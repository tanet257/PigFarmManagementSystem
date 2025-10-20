<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function my_home()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usertype = Auth::user()->usertype;

        if ($usertype == 'staff') {
            return redirect(route('dashboard'));
        } elseif ($usertype == 'admin') {
            return redirect(route('admin.index'));
        }

        abort(403, 'ไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
    public function index()
    {
        if (Auth::id()) {
            $usertype = Auth::user()->usertype;

            if ($usertype == 'staff') {
                return redirect(route('admin.dairy_records.index'));
            } elseif ($usertype == 'admin') {
                return redirect(route('admin.index'));
            } else {
                abort(403, 'ไม่มีสิทธิ์เข้าถึงหน้านี้');
            }
        }
        return redirect(route('login'));
    }
}
