<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;

class CheckLicense
{
    public function handle(Request $request, Closure $next)
    {
        $licenseKey = $request->bearerToken()
            ?? $request->header('X-License-Key')
            ?? $request->input('license_key');

        if (!$licenseKey) {
            return response()->json(['error' => 'Licença não informada.'], 401);
        }

        $license = License::where('license_key', $licenseKey)->first();

        if (!$license || !$license->isValid()) {
            return response()->json([
                'error' => 'Licença inválida ou expirada.',
                'code' => 'LICENSE_INVALID',
            ], 403);
        }

        $request->merge(['_license' => $license]);

        return $next($request);
    }
}
