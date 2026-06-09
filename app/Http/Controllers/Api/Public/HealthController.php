<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'project' => 'PETPAY-CARD',
            'service' => 'public-api',
            'version' => '0.1.0',
        ]);
    }
}