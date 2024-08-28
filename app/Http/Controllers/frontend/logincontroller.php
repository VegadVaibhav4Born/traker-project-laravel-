<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->session()->get('email');

        if ($email) {
            return redirect("index");
        } else {
            return view('frontend.login');
        }
    }

    public function store(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // Validate the email and password
        $selectuser = User::where('email', $email)->first();
        
        if ($selectuser && $selectuser->password === $password) {
            // Storing user details in session
            $request->session()->put('username', $selectuser->name);
            $request->session()->put('email', $selectuser->email);
            $request->session()->put('type', $selectuser->type);
            
            if ($selectuser->status_type === 'complete') {
                // Redirect to index if status_type is complete
                return redirect("index");
            } else {
                // Send OTP email if status_type is not complete
                $this->sendOtp($selectuser);
                // Redirect to OTP verification page
                return redirect("otp-verify");
            }
        } else {
            // Invalid email or password
            return back()->with('status', 'The email address or password you entered is incorrect. Please try again.');
        }
    }

    private function sendOtp($user)
    {
        $name = $user->name;
        $otp = rand(100000, 999999);
        $to = $user->email;
        $subject = 'Your OTP for Account Verification';

        $message = '<html><body>';
        $message .= '<style>';
        $message .= '.btn-grd-primary {';
        $message .= '    background: linear-gradient(310deg, #7928ca, #ff0080) !important;';
        $message .= '    color: #ffffff;';
        $message .= '    border: none;';
        $message .= '    padding: 10px 20px;';
        $message .= '    font-size: 18px;';
        $message .= '    border-radius: 5px;';
        $message .= '    box-shadow: 2px 2px 5px #ddd;';
        $message .= '    text-decoration: none;';
        $message .= '    display: inline-block;';
        $message .= '    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;';
        $message .= '}';
        $message .= '</style>';

        $message .= '<div style="border: 1px solid #666; padding: 10px; color: #ffffff; text-align: center;">';
        $message .= '<h2 style="color:#0a58ca;">Dear ' . $name . ',</h2>';
        $message .= '<div style="border: 1px solid #ddd; background-color: #fff; padding: 20px; color: #222; font-family: Arial, sans-serif; line-height: 1.5; text-align: center;">';
        $message .= '<h2 style="color:#222; margin-bottom: 10px;">Notice: Please use this OTP to verify your account.</h2>';
        $message .= '<p style="color:#777; margin-bottom: 30px;">If you did not request this OTP, please ignore this email.</p>';
        $message .= '<p style="color:#777; margin-bottom: 30px;">Your OTP for account verification is: <h2 style="color:#0a58ca;">' . $otp . '</h2></p>';
        $message .= '<p style="color:#777; margin-bottom: 30px;">If you have any questions or concerns, please do not hesitate to contact our customer support team.</p>';
        $message .= '<a href="https://4born.in/" style="background: linear-gradient(310deg, #7928ca, #ff0080); color: #ffffff; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; box-shadow: 2px 2px 5px #ddd; text-decoration: none; display: inline-block; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;">Contact</a>';
        $message .= '</div>';
        $message .= '</div>';
        $message .= '</body></html>';

        $headers = "From: Traker\r\n";
        $headers .= "Reply-To: Traker\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($to, $subject, $message, $headers)) {
            $user->otp = $otp;
            $user->save();
        } else {
            throw new \Exception('Failed to send OTP email.');
        }
    }
}
