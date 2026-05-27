<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
        return $next($request);
    }
}
