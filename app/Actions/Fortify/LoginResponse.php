<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();
        $usertype = $user->usertype;

        if ($usertype == 'staff') {
            return redirect(route('dashboard'));
        } elseif ($usertype == 'admin') {
            return redirect(route('admin.index'));
        }

        // default fallback
        return redirect(route('dashboard'));
    }
}
