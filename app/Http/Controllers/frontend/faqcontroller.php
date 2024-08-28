<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class faqcontroller extends Controller
{
    //
    public function index(Request $request)
    { 
         $headerController = new headerController();
    $users_data = $headerController->header($request);
     
         $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
                
        return view('frontend.faq'
        ,['user'=>$user,
         'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0]);
            }
            return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
        }
     else {
        return redirect()->route('login')->with('error', 'User not found.');
    }   
    }
}
