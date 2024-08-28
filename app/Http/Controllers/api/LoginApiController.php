<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class LoginApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   
         // Controller method for handling login
        public function login(Request $request)
        {
            // Validate incoming request
            // $request->validate([
            //     'email' => 'required|email',
            //     'password' => 'required',
            // ]);
        
            // Find user by email
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Check if the provided password matches the stored hashed password
                if ($request->password === $user->password) {
                    $response = [
                        'message' => 'Login Success',
                        'status' => 'Success',
                        'data' =>[
                            'name'=> $user->name ,
                            'email'=> $user->email ,
                            'country'=>$user->country,
                            'status_type'=>$user->status_type,
                            'address'=>$user->address,
                            'mobile'=>$user->mobile
                            ],
                    ];
                } 
                else {
                    $response = [
                        'message' => 'The password you entered is incorrect. Please enter the correct email and password.',
                        'status' => 'Failed',
                    ];
                }
                } 
                else {
                $response = [
                    'message' => 'The email address you entered is not registered. Please use a registered email address and try again.',
                    'status' => 'Failed',
                ];
            }

             return response()->json($response);
     }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
