<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleApplicationListMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        // 管理者の場合 → 管理者ルートへリダイレクト
        if ($user->is_admin) {
            return redirect()->route('admin.admin_application_list');
        }

        // 一般ユーザーはそのまま続行（既存ルート：attendance.applicationList で処理）
        return $next($request);
    }
}
