<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class logoutcontroller extends Controller
{
    public function index()
    {
       session()->forget('username');
        session()->forget('type');
        session()->forget('email');
        return redirect()->route('login');
    }
}
