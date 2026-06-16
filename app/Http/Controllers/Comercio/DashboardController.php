<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('comercio.dashboard', [
            'comercio' => Auth::guard('comercio')->user(),
        ]);
    }
}