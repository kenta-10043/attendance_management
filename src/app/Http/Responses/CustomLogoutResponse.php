<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
