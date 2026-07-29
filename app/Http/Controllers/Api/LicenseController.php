<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate(['license_key' => 'required|string']);

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license || !$license->isValid()) {
            return response()->json([
                'valid' => false,
                'error' => 'Licença inválida ou expirada.',
            ], 403);
        }

        return response()->json([
            'valid' => true,
            'plan' => $license->plan,
            'client_name' => $license->client_name,
            'valid_until' => $license->valid_until->format('Y-m-d'),
            'max_stores' => $license->max_stores,
            'max_users' => $license->max_users,
        ]);
    }

    public function status(Request $request)
    {
        $license = $request->get('_license');

        return response()->json([
            'valid' => $license->isValid(),
            'plan' => $license->plan,
            'client_name' => $license->client_name,
            'valid_until' => $license->valid_until->format('Y-m-d'),
            'max_stores' => $license->max_stores,
            'max_users' => $license->max_users,
        ]);
    }

    public function heartbeat(Request $request)
    {
        $request->validate(['license_key' => 'required|string']);

        $license = License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'error' => 'Licença não encontrada.',
                'server_time' => now()->toIso8601String(),
            ], 404);
        }

        return response()->json([
            'valid' => $license->isValid(),
            'plan' => $license->plan,
            'client_name' => $license->client_name,
            'valid_until' => $license->valid_until->format('Y-m-d'),
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
