<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente\CustomerUser;
use App\Models\Comercio\CommerceBranding;
use App\Models\Comercio\CommerceIdentityProfile;
use App\Models\Comercio\CommerceUser;
use App\Models\Proveedor\ProviderUser;
use App\Models\Repartidor\DriverUser;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $brandingPending = 0;

        foreach (['header', 'icon', 'listing'] as $type) {
            $brandingPending += CommerceBranding::query()
                ->where("{$type}_image_status", 'pending')
                ->count();
        }

        $metrics = [
            'customers_total' => CustomerUser::query()->count(),

            'commerces_pending' => CommerceUser::query()
                ->where('approval_status', 'pending')
                ->count(),

            'commerces_approved' => CommerceUser::query()
                ->where('approval_status', 'approved')
                ->count(),

            'branding_pending' => $brandingPending,

            'identities_pending' => CommerceIdentityProfile::query()
                ->whereIn('status', ['submitted', 'under_review'])
                ->count(),

            'providers_pending' => ProviderUser::query()
                ->where('approval_status', 'pending')
                ->count(),

            'providers_approved' => ProviderUser::query()
                ->where('approval_status', 'approved')
                ->count(),

            'drivers_pending' => DriverUser::query()
                ->where('approval_status', 'pending')
                ->count(),

            'drivers_approved' => DriverUser::query()
                ->where('approval_status', 'approved')
                ->count(),

            'orders_today' => $this->safeTableCountToday('mysql_orders', 'orders'),

            'payments_today' => $this->safeTableCountToday('mysql_payments', 'payments'),

            'active_deliveries' => $this->safeActiveDeliveriesCount(),
        ];

        return view('portals.admin.home', [
            'metrics' => $metrics,
        ]);
    }

    private function safeTableCountToday(string $connection, string $table): int
    {
        if (! $this->tableExists($connection, $table)) {
            return 0;
        }

        return DB::connection($connection)
            ->table($table)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    private function safeActiveDeliveriesCount(): int
    {
        if (! $this->tableExists('mysql_delivery', 'deliveries')) {
            return 0;
        }

        return DB::connection('mysql_delivery')
            ->table('deliveries')
            ->whereIn('status', [
                'assigned',
                'pickup_route',
                'picked_up',
                'delivery_route',
                'in_progress',
            ])
            ->count();
    }

    private function tableExists(string $connection, string $table): bool
    {
        return DB::connection($connection)
            ->getSchemaBuilder()
            ->hasTable($table);
    }
}
