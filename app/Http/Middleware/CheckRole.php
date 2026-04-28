<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: CheckRole
 * 
 * Restricts access to routes based on the user's role.
 * 
 * Usage in routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:medecin')
 *   ->middleware('role:patient')
 *   ->middleware('role:admin,medecin')   ← multiple roles allowed
 * 
 * This middleware runs AFTER auth:sanctum, so $request->user() is always set.
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * @param string $role  Comma-separated list of allowed roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // This should never happen if auth:sanctum is applied first,
        // but we guard anyway
        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Check if the user's role is in the list of allowed roles
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Accès refusé. Rôle requis : ' . implode(' ou ', $roles),
            ], 403);
        }

        // User has the right role → continue to the controller
        return $next($request);
    }
}
