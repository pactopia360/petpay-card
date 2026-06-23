<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use App\Models\Comercio\CommerceContact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $comercio = Auth::guard('comercio')->user();

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

        return view('comercio.dashboard', [
            'comercio' => $comercio,
            'contacts' => $contacts,
            'branches' => $branches,
            'states' => $this->states(),
            'activeTab' => $request->query('tab', 'usuarios'),
            'serviceDays' => $this->serviceDays(),
        ]);
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