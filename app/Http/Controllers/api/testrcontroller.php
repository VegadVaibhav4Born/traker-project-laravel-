<?php

// app/Http/Controllers/api/testrcontroller.php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class testrcontroller extends Controller
{
    public function index()
    {
        return 'laravel API Run';
    }
}
