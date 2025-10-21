<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $admin = $request->input('admin_logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $admin ? redirect('/admin/login') : redirect('/login');
    }
}
