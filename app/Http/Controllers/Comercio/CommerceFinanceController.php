<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use App\Models\Comercio\CommerceUser;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommerceFinanceController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $commerce = $this->commerce();
        $filters = $this->filters($request);

        $movementsQuery = DB::connection('mysql_payments')
            ->table('finance_movements')
            ->where('commerce_user_id', $commerce->id);

        $this->applyMovementFilters($movementsQuery, $filters);

        $movements = (clone $movementsQuery)
            ->orderByDesc('occurred_at')
            ->limit(250)
            ->get();

        $settlements = DB::connection('mysql_payments')
            ->table('settlements')
            ->where('commerce_user_id', $commerce->id)
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('period_end', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('period_start', '<=', $date))
            ->orderByDesc('period_end')
            ->limit(100)
            ->get();

        $disputes = DB::connection('mysql_payments')
            ->table('finance_disputes')
            ->where('commerce_user_id', $commerce->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $invoices = DB::connection('mysql_payments')
            ->table('commerce_invoices')
            ->where('commerce_user_id', $commerce->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $bankAccounts = DB::connection('mysql_payments')
            ->table('commerce_bank_accounts')
            ->where('commerce_user_id', $commerce->id)
            ->orderByDesc('is_primary')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get()
            ->map(function ($account): object {
                $account->masked_clabe = '•••• •••• •••• ••'.($account->account_last4 ?: '••••');
                $account->masked_account = $account->account_number_last4
                    ? '•••• •••• '.$account->account_number_last4
                    : 'No registrada';

                unset($account->clabe_encrypted, $account->account_number_encrypted);

                return $account;
            });

        $taxProfile = DB::connection('mysql_payments')
            ->table('commerce_tax_profiles')
            ->where('commerce_user_id', $commerce->id)
            ->first();

        $invoiceSeries = DB::connection('mysql_payments')
            ->table('commerce_invoice_series')
            ->where('commerce_user_id', $commerce->id)
            ->orderByDesc('is_default')
            ->orderBy('series')
            ->get();

        $summary = [
            'gross_sales' => round((float) $movements->sum('gross_amount'), 2),
            'commission' => round((float) $movements->sum('commission_amount'), 2),
            'delivery' => round((float) $movements->sum('delivery_amount'), 2),
            'refunds' => round((float) $movements->where('type', 'refund')->sum(fn ($item) => abs((float) $item->net_amount)), 2),
            'available' => round((float) $movements->where('status', 'available')->sum('net_amount'), 2),
            'pending' => round((float) $movements->whereIn('status', ['pending', 'processing'])->sum('net_amount'), 2),
            'held' => round((float) $movements->where('status', 'held')->sum('net_amount'), 2),
            'paid' => round((float) $settlements->where('status', 'paid')->sum('net_amount'), 2),
            'disputes' => (int) $disputes->whereIn('status', ['open', 'review', 'information_required'])->count(),
        ];

        return response()->json([
            'ok' => true,
            'summary' => $summary,
            'movements' => $movements,
            'settlements' => $settlements,
            'disputes' => $disputes,
            'invoices' => $invoices,
            'bank_accounts' => $bankAccounts,
            'tax_profile' => $taxProfile,
            'invoice_series' => $invoiceSeries,
            'branches' => CommerceBranch::query()
                ->where('commerce_user_id', $commerce->id)
                ->orderBy('branch_name')
                ->get(['id', 'branch_name', 'branch_code']),
        ]);
    }

    public function saveTaxProfile(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'person_type' => ['required', Rule::in(['fisica', 'moral'])],
            'rfc' => ['required', 'string', 'max:13'],
            'legal_name' => ['required', 'string', 'max:240'],
            'tax_regime' => ['required', 'string', 'max:10'],
            'postal_code' => ['required', 'string', 'max:10'],
            'cfdi_use' => ['nullable', 'string', 'max:10'],
            'tax_email' => ['required', 'email', 'max:180'],
            'fiscal_street' => ['nullable', 'string', 'max:180'],
            'fiscal_number' => ['nullable', 'string', 'max:30'],
            'fiscal_colony' => ['nullable', 'string', 'max:120'],
            'fiscal_city' => ['nullable', 'string', 'max:120'],
            'fiscal_state' => ['nullable', 'string', 'max:120'],
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
            'csf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'compliance_opinion' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'efirma_cer' => ['nullable', 'file', 'mimes:cer', 'max:5120'],
            'efirma_key' => ['nullable', 'file', 'mimes:key', 'max:5120'],
            'efirma_password' => ['nullable', 'string', 'max:255'],
            'csd_cer' => ['nullable', 'file', 'mimes:cer', 'max:5120'],
            'csd_key' => ['nullable', 'file', 'mimes:key', 'max:5120'],
            'csd_password' => ['nullable', 'string', 'max:255'],
        ]);

        $connection = DB::connection('mysql_payments');
        $current = $connection
            ->table('commerce_tax_profiles')
            ->where('commerce_user_id', $commerce->id)
            ->first();

        $paths = [
            'csf_path' => $current?->csf_path,
            'compliance_opinion_path' => $current?->compliance_opinion_path,
            'efirma_cer_path' => $current?->efirma_cer_path,
            'efirma_key_path' => $current?->efirma_key_path,
            'csd_cer_path' => $current?->csd_cer_path,
            'csd_key_path' => $current?->csd_key_path,
        ];

        $uploadMap = [
            'csf' => 'csf_path',
            'compliance_opinion' => 'compliance_opinion_path',
            'efirma_cer' => 'efirma_cer_path',
            'efirma_key' => 'efirma_key_path',
            'csd_cer' => 'csd_cer_path',
            'csd_key' => 'csd_key_path',
        ];

        foreach ($uploadMap as $input => $column) {
            if (! $request->hasFile($input)) {
                continue;
            }

            if ($paths[$column]) {
                Storage::disk('private')->delete($paths[$column]);
            }

            $paths[$column] = $request->file($input)->store(
                'commerce-finance/'.$commerce->id.'/tax',
                'private'
            );
        }

        $payload = [
            'person_type' => $validated['person_type'],
            'rfc' => strtoupper(trim($validated['rfc'])),
            'legal_name' => trim($validated['legal_name']),
            'tax_regime' => trim($validated['tax_regime']),
            'postal_code' => trim($validated['postal_code']),
            'cfdi_use' => $validated['cfdi_use'] ?? null,
            'tax_email' => strtolower(trim($validated['tax_email'])),
            'fiscal_street' => $validated['fiscal_street'] ?? null,
            'fiscal_number' => $validated['fiscal_number'] ?? null,
            'fiscal_colony' => $validated['fiscal_colony'] ?? null,
            'fiscal_city' => $validated['fiscal_city'] ?? null,
            'fiscal_state' => $validated['fiscal_state'] ?? null,
            'environment' => $validated['environment'],
            'status' => 'pending_review',
            'verified_at' => null,
            'updated_at' => now(),
            'created_at' => $current?->created_at ?? now(),
            ...$paths,
        ];

        if (! empty($validated['efirma_password'])) {
            $payload['efirma_password_encrypted'] = Crypt::encryptString($validated['efirma_password']);
        }

        if (! empty($validated['csd_password'])) {
            $payload['csd_password_encrypted'] = Crypt::encryptString($validated['csd_password']);
        }

        $connection
            ->table('commerce_tax_profiles')
            ->updateOrInsert(
                ['commerce_user_id' => $commerce->id],
                $payload
            );

        return back()
            ->with('status', 'Datos fiscales y certificados guardados para revisión.')
            ->with('finance_tab', 'tax');
    }

    public function saveBankAccount(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:10'],
            'bank_name' => ['required', 'string', 'max:120'],
            'account_holder' => ['required', 'string', 'max:180'],
            'holder_rfc' => ['nullable', 'string', 'max:13'],
            'clabe' => ['required', 'digits:18'],
            'account_number' => ['nullable', 'string', 'max:30'],
            'card_last4' => ['nullable', 'digits:4'],
            'bank_branch' => ['nullable', 'string', 'max:120'],
            'agreement_reference' => ['nullable', 'string', 'max:120'],
            'currency' => ['required', Rule::in(['MXN', 'USD'])],
            'is_primary' => ['nullable', 'boolean'],
            'proof' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,webp', 'max:10240'],
            'statement' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store('commerce-finance/'.$commerce->id.'/bank', 'private')
            : null;

        $statementPath = $request->hasFile('statement')
            ? $request->file('statement')->store('commerce-finance/'.$commerce->id.'/bank', 'private')
            : null;

        $isPrimary = $request->boolean('is_primary');

        DB::connection('mysql_payments')->transaction(function () use (
            $validated,
            $commerce,
            $proofPath,
            $statementPath,
            $isPrimary
        ): void {
            if ($isPrimary) {
                DB::connection('mysql_payments')
                    ->table('commerce_bank_accounts')
                    ->where('commerce_user_id', $commerce->id)
                    ->update([
                        'is_primary' => false,
                        'updated_at' => now(),
                    ]);
            }

            DB::connection('mysql_payments')
                ->table('commerce_bank_accounts')
                ->insert([
                    'commerce_user_id' => $commerce->id,
                    'bank_code' => $validated['bank_code'],
                    'bank_name' => trim($validated['bank_name']),
                    'account_holder' => trim($validated['account_holder']),
                    'holder_rfc' => isset($validated['holder_rfc']) ? strtoupper(trim($validated['holder_rfc'])) : null,
                    'clabe_encrypted' => Crypt::encryptString($validated['clabe']),
                    'account_last4' => substr($validated['clabe'], -4),
                    'account_number_encrypted' => ! empty($validated['account_number'])
                        ? Crypt::encryptString($validated['account_number'])
                        : null,
                    'account_number_last4' => ! empty($validated['account_number'])
                        ? substr($validated['account_number'], -4)
                        : null,
                    'card_last4' => $validated['card_last4'] ?? null,
                    'bank_branch' => $validated['bank_branch'] ?? null,
                    'agreement_reference' => $validated['agreement_reference'] ?? null,
                    'currency' => $validated['currency'],
                    'status' => 'pending_review',
                    'is_primary' => $isPrimary,
                    'is_active' => true,
                    'proof_path' => $proofPath,
                    'statement_path' => $statementPath,
                    'verified_at' => null,
                    'metadata' => json_encode(['change_requested_at' => now()->toIso8601String()]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return back()
            ->with('status', 'Cuenta bancaria registrada y enviada a revisión.')
            ->with('finance_tab', 'bank');
    }

    public function setPrimaryBankAccount(int $account): RedirectResponse
    {
        $commerce = $this->commerce();

        $bank = DB::connection('mysql_payments')
            ->table('commerce_bank_accounts')
            ->where('commerce_user_id', $commerce->id)
            ->where('id', $account)
            ->first();

        abort_unless($bank, 404);

        DB::connection('mysql_payments')->transaction(function () use ($commerce, $account): void {
            DB::connection('mysql_payments')
                ->table('commerce_bank_accounts')
                ->where('commerce_user_id', $commerce->id)
                ->update(['is_primary' => false, 'updated_at' => now()]);

            DB::connection('mysql_payments')
                ->table('commerce_bank_accounts')
                ->where('commerce_user_id', $commerce->id)
                ->where('id', $account)
                ->update(['is_primary' => true, 'is_active' => true, 'updated_at' => now()]);
        });

        return back()
            ->with('status', 'Cuenta bancaria principal actualizada.')
            ->with('finance_tab', 'bank');
    }

    public function toggleBankAccount(int $account): RedirectResponse
    {
        $commerce = $this->commerce();

        $bank = DB::connection('mysql_payments')
            ->table('commerce_bank_accounts')
            ->where('commerce_user_id', $commerce->id)
            ->where('id', $account)
            ->first();

        abort_unless($bank, 404);

        if ($bank->is_primary && $bank->is_active) {
            return back()
                ->withErrors(['bank' => 'La cuenta principal no puede desactivarse. Asigna otra como principal primero.'])
                ->with('finance_tab', 'bank');
        }

        DB::connection('mysql_payments')
            ->table('commerce_bank_accounts')
            ->where('commerce_user_id', $commerce->id)
            ->where('id', $account)
            ->update([
                'is_active' => ! (bool) $bank->is_active,
                'updated_at' => now(),
            ]);

        return back()
            ->with('status', 'Estado de la cuenta bancaria actualizado.')
            ->with('finance_tab', 'bank');
    }

    public function storeInvoiceSeries(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'series' => ['required', 'string', 'max:20'],
            'cfdi_type' => ['required', Rule::in(['I', 'E', 'P', 'T', 'N'])],
            'initial_folio' => ['required', 'integer', 'min:1'],
            'current_folio' => ['nullable', 'integer', 'min:1'],
            'branch_id' => ['nullable', 'integer'],
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $series = strtoupper(trim($validated['series']));
        $isDefault = $request->boolean('is_default');

        $exists = DB::connection('mysql_payments')
            ->table('commerce_invoice_series')
            ->where('commerce_user_id', $commerce->id)
            ->where('series', $series)
            ->where('cfdi_type', $validated['cfdi_type'])
            ->where('environment', $validated['environment'])
            ->where(function ($query) use ($validated): void {
                if (empty($validated['branch_id'])) {
                    $query->whereNull('branch_id');
                } else {
                    $query->where('branch_id', $validated['branch_id']);
                }
            })
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['series' => 'Ya existe esa serie para el tipo de CFDI, sucursal y ambiente seleccionados.'])
                ->with('finance_tab', 'invoices');
        }

        DB::connection('mysql_payments')->transaction(function () use (
            $commerce,
            $validated,
            $series,
            $isDefault,
            $request
        ): void {
            if ($isDefault) {
                DB::connection('mysql_payments')
                    ->table('commerce_invoice_series')
                    ->where('commerce_user_id', $commerce->id)
                    ->where('cfdi_type', $validated['cfdi_type'])
                    ->where('environment', $validated['environment'])
                    ->update(['is_default' => false, 'updated_at' => now()]);
            }

            DB::connection('mysql_payments')
                ->table('commerce_invoice_series')
                ->insert([
                    'commerce_user_id' => $commerce->id,
                    'branch_id' => $validated['branch_id'] ?? null,
                    'series' => $series,
                    'cfdi_type' => $validated['cfdi_type'],
                    'initial_folio' => $validated['initial_folio'],
                    'current_folio' => $validated['current_folio'] ?? $validated['initial_folio'],
                    'environment' => $validated['environment'],
                    'is_default' => $isDefault,
                    'is_active' => $request->boolean('is_active', true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return back()
            ->with('status', 'Serie y folio registrados correctamente.')
            ->with('finance_tab', 'invoices');
    }

    public function storeDispute(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'type' => ['required', Rule::in(['payment', 'settlement', 'commission', 'refund', 'chargeback', 'invoice', 'other'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'subject' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:5000'],
            'claimed_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'order_id' => ['nullable', 'integer'],
            'payment_transaction_id' => ['nullable', 'integer'],
            'settlement_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'attachments.*' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg,webp,xlsx,csv', 'max:10240'],
        ]);

        $attachments = [];

        foreach ($request->file('attachments', []) as $file) {
            $attachments[] = $file->store(
                'commerce-finance/'.$commerce->id.'/disputes',
                'private'
            );
        }

        DB::connection('mysql_payments')
            ->table('finance_disputes')
            ->insert([
                'uuid' => (string) Str::uuid(),
                'folio' => 'ACL-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'commerce_user_id' => $commerce->id,
                'branch_id' => $validated['branch_id'] ?? null,
                'order_id' => $validated['order_id'] ?? null,
                'payment_transaction_id' => $validated['payment_transaction_id'] ?? null,
                'settlement_id' => $validated['settlement_id'] ?? null,
                'type' => $validated['type'],
                'priority' => $validated['priority'],
                'subject' => trim($validated['subject']),
                'description' => trim($validated['description']),
                'claimed_amount' => $validated['claimed_amount'] ?? 0,
                'status' => 'open',
                'due_at' => $this->addBusinessDays(now(), 5),
                'resolved_at' => null,
                'resolution' => null,
                'attachments' => json_encode($attachments),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return back()
            ->with('status', 'Aclaración registrada correctamente.')
            ->with('finance_tab', 'disputes');
    }

    public function exportMovements(Request $request): StreamedResponse
    {
        $commerce = $this->commerce();
        $filters = $this->filters($request);

        $query = DB::connection('mysql_payments')
            ->table('finance_movements')
            ->where('commerce_user_id', $commerce->id);

        $this->applyMovementFilters($query, $filters);

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Fecha', 'Tipo', 'Concepto', 'Pedido', 'Sucursal', 'Bruto',
                'Comisión', 'Envío', 'Descuento', 'Impuestos', 'Ajuste', 'Neto', 'Estatus',
            ]);

            $query->orderByDesc('occurred_at')->chunkById(500, function ($rows) use ($handle): void {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->occurred_at,
                        $row->type,
                        $row->concept,
                        $row->order_id,
                        $row->branch_id,
                        $row->gross_amount,
                        $row->commission_amount,
                        $row->delivery_amount,
                        $row->discount_amount,
                        $row->tax_amount,
                        $row->adjustment_amount,
                        $row->net_amount,
                        $row->status,
                    ]);
                }
            });

            fclose($handle);
        }, 'movimientos-financieros-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'branch_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:40'],
            'type' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:160'],
        ]);
    }

    private function applyMovementFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn ($builder, $value) => $builder->whereDate('occurred_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($builder, $value) => $builder->whereDate('occurred_at', '<=', $value))
            ->when($filters['branch_id'] ?? null, fn ($builder, $value) => $builder->where('branch_id', $value))
            ->when($filters['status'] ?? null, fn ($builder, $value) => $builder->where('status', $value))
            ->when($filters['type'] ?? null, fn ($builder, $value) => $builder->where('type', $value))
            ->when($filters['search'] ?? null, function ($builder, $value): void {
                $builder->where(function ($nested) use ($value): void {
                    $nested
                        ->where('concept', 'like', '%'.$value.'%')
                        ->orWhere('uuid', 'like', '%'.$value.'%');

                    if (ctype_digit((string) $value)) {
                        $nested->orWhere('order_id', (int) $value);
                    }
                });
            });
    }

    private function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();

        while ($days > 0) {
            $result->addDay();

            if (! $result->isWeekend()) {
                $days--;
            }
        }

        return $result;
    }

    private function commerce(): CommerceUser
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce instanceof CommerceUser, 401);

        return $commerce;
    }
}

