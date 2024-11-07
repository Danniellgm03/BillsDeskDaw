<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permisions): Response
    {
        $user = Auth::user();

        if($user->role->isAdmin){
            return $next($request);
        }

        foreach ($permisions as $permission) {
            if (in_array($permission, $user->role->permissions)) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'No tienes permisos para realizar esta acción'], 403);
    }
}
