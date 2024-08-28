<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
class Plan_Show extends Controller
{
    public function index()
    {

        $response = [
            'status' => 'success',
            'message' => 'Plans are shown!',
        ];

        return response()->json($response);
    }
}
