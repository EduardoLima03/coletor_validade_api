<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Não autenticado.'], 403)
                : abort(403, 'Não autenticado.');
        }

        $userRole = strtoupper(auth()->user()->position ?? '');

        $allowed = array_map('strtoupper', $roles);

        if (!in_array($userRole, $allowed)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Acesso não autorizado para este nível de usuário.'], 403)
                : abort(403, 'Acesso não autorizado para este nível de usuário.');
        }

        return $next($request);
    }
}
