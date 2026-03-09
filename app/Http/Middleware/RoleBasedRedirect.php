<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleBasedRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->role; // Assurez-vous que votre modèle User a un champ 'role'
            $currentPath = $request->path(); // Obtenir le chemin actuel

            switch ($role) {
                // case 'admin':
                //     if ($currentPath !== 'adminn') { // Vérifiez si l'utilisateur n'est pas déjà sur /admin
                //         return redirect('/admin');
                //     }
                //     break;
                case 'user':
                    if ($currentPath !== 'dashboard') { // Vérifiez si l'utilisateur n'est pas déjà sur /dashboard
                        return redirect('/dashboard');
                    }
                    break;
                default:
                    // Gérer les rôles non reconnus si nécessaire
                    break;
            }
        }

        return $next($request);
    }
}
