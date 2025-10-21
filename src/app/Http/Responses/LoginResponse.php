<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return redirect('/email/verify')
                ->withErrors(['email' => 'メール認証が完了していません。']);
        }

        if ($user->is_admin) {
            return redirect()->intended('/admin/attendance/list');
        }

        return redirect('/attendance');
    }
}
