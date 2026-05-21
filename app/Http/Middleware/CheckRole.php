<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            abort(403, 'Não autenticado.');
        }

        $userRole = strtoupper(auth()->user()->position ?? '');

        $allowed = array_map('strtoupper', $roles);

        if (!in_array($userRole, $allowed)) {
            abort(403, 'Acesso não autorizado para este nível de usuário.');
        }

        return $next($request);
    }
}
