<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if(!Auth::check()){
            return redirect()->route('/api/auth/login');
        }

        $user = Auth::user();

        foreach($roles as $roles){
            if ($user->role == $roles) {
                return $next($request);
            }
        }
    }
}
