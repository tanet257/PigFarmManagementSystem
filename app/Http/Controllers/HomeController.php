<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function my_home()
    {
        return view('home.index');
    }
    public function index()
    {
        if(Auth::id())
        {
            $usertype = Auth()->user()->usertype;

            if($usertype=='staff')
            {
                return view('home.index');
            }

            elseif($usertype== 'admin')
            {
                return view('admin.admin_index');
            }

            else
            {
                abort(403, 'ไม่มีสิทธิ์เข้าถึงหน้านี้');
            }
        }
    }
}
