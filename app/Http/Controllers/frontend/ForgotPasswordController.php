<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\TrakingUser;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email address
        $request->validate([
            'email' => 'required|email|exists:traking_user,email',
        ]);

        // Generate a new token
        $token = Str::random(60);

        // Find the user by email
        $user = TrakingUser::where('email', $request->email)->first();
        if($user){
        // Save the token to the user's remember_token field
        $user->remember_token = $token;
        $user->save();

        // Generate the reset link
        $resetLink = route('password.reset', ['token' => $token, 'email' => urlencode($user->email)]);

       
        $email = $request['email'];
        // $otp = rand(100000, 999999); // Generate OTP
        
        // Prepare email content
        $to = $email;
        $subject = 'Reset Your Password';
        
        
        
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
$message .= '<h2 style="color:#0a58ca;">Dear ' . $email . ',</h2>';
$message .= '<div style="border: 1px solid #ddd; background-color: #fff; padding: 20px; color: #222; font-family: Arial, sans-serif; line-height: 1.5; text-align: center;">';
// $message .= '<h2 style="color:#222; margin-bottom: 10px;">We received a request to reset your password. Please click the link below to reset your password:</h2>';
$message .= '<p style="color:#777; margin-bottom: 30px;">We received a request to reset your password. Please click the link below to reset your password:
</p>';
$message .= '<a href="' . $resetLink . '" style="background: linear-gradient(310deg, #7928ca, #ff0080); color: #ffffff; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; box-shadow: 2px 2px 5px #ddd; text-decoration: none; display: inline-block; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;">Reset Password</a>';
$message .= '<p style="color:#777; margin-bottom: 30px;">If you did not request a password reset, please ignore this email.</p>';
// $message .= '<a href="https://4born.in/" style="background: linear-gradient(310deg, #7928ca, #ff0080); color: #ffffff; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; box-shadow: 2px 2px 5px #ddd; text-decoration: none; display: inline-block; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;">Contact</a>';
$message .= '</div>';
$message .= '</div>';
$message .= '</body></html>';
        
    //     $message = '<html><body>';
    //     $message .= '<div style="border: 1px solid #666; padding: 10px; color: #ffffff; text-align: center;">';
    //     $message .= '<h2 style="color:#fd961a;">Dear ' . $email . ',</h2>';
    //     $message .= '<div style="border: 1px solid #ddd; background-color: #fff; padding: 20px; color: #222; font-family: Arial, sans-serif; line-height: 1.5; text-align: center;">';
    //     $message .= '<img src="https://4born.in/assets/images/BGCO.png" alt="4BornSolutions" style="max-width: 350px; margin-bottom: 10px;">';
    //     // $message .= '<h2 style="color:#222; margin-bottom: 10px;">Notice: Please do not share this OTP with anyone</h2>';
    //   $message .= '<p>Hello,</p>';
    //   $message .= ' <p>Click the link below to reset your password:</p>';
    //     $message .= '<a href="' . $resetLink . '" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; text-decoration: none; border-radius: 5px;">Reset Password</a>';
    //     $message .= '<p>If you did not request a password reset, no further action is required.</p>';
    //     $message .= '<p style="color:#777; margin-bottom: 30px;">If you have any questions or concerns, please do not hesitate to contact our customer support team.</p>';
    //     $message .= '<a href="https://e-otp.4born.in/" style="text-decoration:none;"><button style="background-color: #fd961a; color: #fff; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; box-shadow: 2px 2px 5px #ddd; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;">Contact</button></a>';
    //     $message .= '</div>';
    //     $message .= '</body></html>';
        
        
         $headers = "From: Traker\r\n";
        $headers .= "Reply-To: Traker\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Sending email and checking for success
        if (mail($to, $subject, $message, $headers)) {
           return back()->with('status', 'A confirmation email has been sent to the provided email address. Please check your email to confirm your account and reset your password if necessary.');
        
            
        } else {
            http_response_code(500);
            //  echo '<script type="text/javascript">
            //                     alert("Failed to send email");
            //               </script>';
            // echo json_encode(['error' => 'Failed to send email']);
           
        }
        
        
        // Send the email
        // Mail::send('emails.password_reset', ['resetLink' => $resetLink], function ($message) use ($request) {
        //     $message->to($request->email);
        //     $message->subject('Password Reset Request');
        // });

        // Redirect back with success message
        // return back()->with('status', 'A confirmation email has been sent to the provided email address. Please check your email to confirm your account and reset your password if necessary.');
    }
    else{
        return back()->withError('status', 'The email address you entered is not registered. Please enter a registered email address.');
    }
    }
}
