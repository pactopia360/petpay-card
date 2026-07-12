<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use App\Models\Comercio\CommerceBranding;
use App\Models\Comercio\CommerceCatalogBrand;
use App\Models\Comercio\CommerceCatalogCategory;
use App\Models\Comercio\CommerceCatalogProduct;
use App\Models\Comercio\CommerceContact;
use App\Models\Comercio\CommerceContract;
use App\Models\Comercio\CommerceIdentityProfile;
use App\Models\Comercio\CommerceMonetizationCampaign;
use App\Models\Comercio\CommerceUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $comercio = Auth::guard('comercio')->user();

        abort_unless($comercio instanceof CommerceUser, 401);

        $this->ensureDefaultContracts($comercio);

        $contacts = CommerceContact::query()
            ->where('commerce_user_id', $comercio->id)
            ->orderByDesc('is_primary')
            ->latest('id')
            ->get();

        $branches = CommerceBranch::query()
            ->where('commerce_user_id', $comercio->id)
            ->orderByRaw("FIELD(status_flag, 'incomplete', 'complete')")
            ->latest('id')
            ->get();

        $branding = CommerceBranding::query()
            ->where('commerce_user_id', $comercio->id)
            ->first();

        $catalogCategories = CommerceCatalogCategory::query()
            ->where('commerce_user_id', $comercio->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $catalogBrands = CommerceCatalogBrand::query()
            ->where('commerce_user_id', $comercio->id)
            ->orderBy('name')
            ->get();

        $catalogProducts = CommerceCatalogProduct::query()
            ->with(['category', 'brand', 'variants', 'stocks.branch'])
            ->where('commerce_user_id', $comercio->id)
            ->latest('id')
            ->get();

        $monetizationCampaigns = CommerceMonetizationCampaign::query()
            ->with(['branch', 'events'])
            ->where('commerce_user_id', $comercio->id)
            ->latest('id')
            ->get();

        $contracts = CommerceContract::query()
            ->with(['branch', 'documents', 'events'])
            ->where('commerce_user_id', $comercio->id)
            ->orderBy('group_key')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $identityProfile = CommerceIdentityProfile::query()
            ->with(['documents', 'events'])
            ->firstOrCreate(
                ['commerce_user_id' => $comercio->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'person_type' => 'individual',
                    'business_legal_name' => $comercio->business_name,
                    'representative_name' => $comercio->name,
                    'representative_email' => $comercio->email,
                    'representative_phone' => $comercio->phone,
                    'address_line' => $comercio->business_address,
                    'status' => 'draft',
                    'liveness_challenge' => 'Muestra tu rostro y una hoja con la fecha actual y la palabra PETPAY.',
                ]
            );

        $allowedTabs = [
            'usuarios',
            'sucursales',
            'branding',
            'catalogos',
            'finanzas',
            'monetizar',
            'contratos',
        ];

        $requestedTab = (string) $request->query('tab', 'usuarios');

        $activeTab = in_array($requestedTab, $allowedTabs, true)
            ? $requestedTab
            : 'usuarios';

        return view('comercio.dashboard', [
            'comercio' => $comercio,
            'contacts' => $contacts,
            'branches' => $branches,
            'branding' => $branding,
            'catalogCategories' => $catalogCategories,
            'catalogBrands' => $catalogBrands,
            'catalogProducts' => $catalogProducts,
            'monetizationCampaigns' => $monetizationCampaigns,
            'contracts' => $contracts,
            'identityProfile' => $identityProfile,
            'states' => $this->states(),
            'activeTab' => $activeTab,
            'serviceDays' => $this->serviceDays(),
        ]);
    }

    private function ensureDefaultContracts(CommerceUser $commerce): void
    {
        $year = (int) now()->format('Y');

        $defaults = [
            ['corporate', 'commercial_terms', 'Contrato marco de servicios Petpay', 'commercial', true, 10],
            ['corporate', 'platform_acceptance', 'Aceptación de términos de plataforma', 'terms', true, 20],
            ['corporate', 'data_processing', 'Anexo de tratamiento de datos', 'privacy', false, 30],
            ['compliance', 'privacy_notice', 'Aviso de privacidad', 'privacy', true, 10],
            ['compliance', 'consumer_compliance', 'Cumplimiento comercial y protección al consumidor', 'compliance', true, 20],
            ['compliance', 'brand_use', 'Autorización de uso de marca e imagen', 'annex', true, 30],
            ['financial', 'settlement_agreement', 'Convenio de liquidaciones y comisiones', 'financial', true, 10],
            ['financial', 'bank_validation', 'Validación de cuenta bancaria', 'financial', true, 20],
            ['financial', 'tax_information', 'Declaración de información fiscal', 'financial', true, 30],
        ];

        foreach ($defaults as [$group, $key, $title, $type, $required, $sort]) {
            CommerceContract::query()->firstOrCreate(
                [
                    'commerce_user_id' => $commerce->id,
                    'template_key' => $key,
                    'document_year' => $year,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'branch_id' => null,
                    'group_key' => $group,
                    'title' => $title,
                    'contract_type' => $type,
                    'version' => '1.0',
                    'is_required' => $required,
                    'sort_order' => $sort,
                    'status' => 'pending_signature',
                    'effective_from' => now()->startOfYear()->toDateString(),
                    'effective_to' => now()->endOfYear()->toDateString(),
                ]
            );
        }
    }

    private function states(): array
    {
        return [
            'aguascalientes' => 'Aguascalientes',
            'baja_california' => 'Baja California',
            'baja_california_sur' => 'Baja California Sur',
            'campeche' => 'Campeche',
            'chiapas' => 'Chiapas',
            'chihuahua' => 'Chihuahua',
            'ciudad_de_mexico' => 'Ciudad de México',
            'coahuila' => 'Coahuila',
            'colima' => 'Colima',
            'durango' => 'Durango',
            'estado_de_mexico' => 'Estado de México',
            'guanajuato' => 'Guanajuato',
            'guerrero' => 'Guerrero',
            'hidalgo' => 'Hidalgo',
            'jalisco' => 'Jalisco',
            'michoacan' => 'Michoacán',
            'morelos' => 'Morelos',
            'nayarit' => 'Nayarit',
            'nuevo_leon' => 'Nuevo León',
            'oaxaca' => 'Oaxaca',
            'puebla' => 'Puebla',
            'queretaro' => 'Querétaro',
            'quintana_roo' => 'Quintana Roo',
            'san_luis_potosi' => 'San Luis Potosí',
            'sinaloa' => 'Sinaloa',
            'sonora' => 'Sonora',
            'tabasco' => 'Tabasco',
            'tamaulipas' => 'Tamaulipas',
            'tlaxcala' => 'Tlaxcala',
            'veracruz' => 'Veracruz',
            'yucatan' => 'Yucatán',
            'zacatecas' => 'Zacatecas',
        ];
    }

    private function serviceDays(): array
    {
        return [
            'L' => 'Lunes',
            'M' => 'Martes',
            'X' => 'Miércoles',
            'J' => 'Jueves',
            'V' => 'Viernes',
            'S' => 'Sábado',
            'D' => 'Domingo',
        ];
    }
}