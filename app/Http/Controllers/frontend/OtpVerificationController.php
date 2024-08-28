<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Mail\PasswordResetMail;

class OtpVerificationController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
          
            // $user = User::where('email', $email)->where('type', $type)->first();
            return view('frontend.otpverify');
        // return view('frontend.index',['user' => $user]);
        }
        else{
                return redirect("login");
        } 
    }

    public function verify(Request $request)
    {
        
        $type = $request->session()->get('type');
        $email = $request->session()->get('email');

        // Verification logic
        $user = User::where('email', $email)
                    ->where('otp', $request->input('otp'))
                    ->where('type', $type)->first();
        if($user)
        {
            $status_type = "complete";
            $user->status_type = $status_type;
            $user->save();
            return redirect("index");
            // echo '<script type="text/javascript">
                    //         toastr.success("Your account has been successfully verified. You may now continue.");
                    // </script>';
            
        }
        else{
            // return redirect('otp-verify')->withErrors(['otp' => 'Invalid OTP. Please try again.']);
              
         return back()->withErrors(['email' => 'The OTP you entered is incorrect. Please enter the correct OTP and verify your account.']);
        }
    }
      public function resendotp(Request $request)
    {
        
         $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        $user = User::where('email',$email)->where('type',$type)->first();
        if($user)
        {
          
        $name = $user->name;
        $otp = rand(100000, 999999); 
        $to = $email;
        $subject = ' Your OTP for Account Verification';
    
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
        
        // Sending email and checking for success
        if (mail($to, $subject, $message, $headers)) {
                $user = User::where('email', $email)
                    ->where('type', $type)->first();
                $user-> otp = $otp;
                 $user->save();
               return back()->with('status', 'A confirmation email has been sent to the provided email address. Please check your email to confirm your account and reset your password if necessary.');
                
             // return back()->with('status', 'OTP has been resent successfully. Please check your email and verify your account.');
       
        } else {
            http_response_code(500);
            //  echo '<script type="text/javascript">
            //                     alert("Failed to send email");
            //               </script>';
          
             return back()->withErrors(['email' => 'Failed to send email.']);
        }
        
        
    }
    else{
         return back()->withErrors(['email' => 'The email address you entered is not registered. Please enter a registered email address.']);
    }
    }
}
