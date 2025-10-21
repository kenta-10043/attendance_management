<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthAndVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'ログインが必要です。');
        }

        if (! Auth::user()->hasVerifiedEmail()) {
            return redirect('/email/verify')->with('error', 'メール認証が必要です。');
        }

        return $next($request);
    }
}
