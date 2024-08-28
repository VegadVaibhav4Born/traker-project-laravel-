<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class coming_sooncontroller extends Controller
{
    //
    public function index()
    {
        return view('frontend.coming-soon');
    }
}
