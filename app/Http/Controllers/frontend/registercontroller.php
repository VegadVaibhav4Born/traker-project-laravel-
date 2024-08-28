<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\register;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use App\Models\country;
class registercontroller extends Controller
{
    //
    public function index(Request $request)
    {
        $email = $request->session()->get('email');

        if($email)
        {
            return redirect("index");
         
        }
        else{
          $country = country::all();
          $data = compact('country');
          return view('frontend.register')->with($data);
        }

    }
    public function store(Request $request)
    {
        // echo "<pre>";
        // print_r($request->all()); 
        $type = "user";

        $existingUser = User::where('email', $request->input('email'))
        ->where('type', $type)->first();
        if ($existingUser) {
          
            // echo '<script type="text/javascript">
            //                     alert("Error! Email is already register");
            //               </script>';
        return back()->with('status', 'The email address you entered is already registered. Please use another unique email address and try again.');
        // return redirect("register");

        } 
        else{       
            
            
                // if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = $request['email'];
        $name = $request['name'];
        $otp = rand(100000, 999999); // Generate OTP
        
        // Prepare email content
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
            // $image = 'images/avatars/01.png';
                 $register = new User;
                $register -> type = $type;
                $register -> name = $request['name'];
                $register -> email = $request['email'];
                $register -> password = $request['password'];
                $register -> country = $request['country'];
                $register -> otp = $otp;
                // $register -> profile_image = $image;
                $register->save();
             echo '<script type="text/javascript">
                                alert("A confirmation email has been sent to the email address you entered. Please check your email and confirm your account to continue.");
                          </script>';
                           $request->session()->put('type', $type);
            $request->session()->put('type', $type);
            $request->session()->put('username', $request['name']);
            $request->session()->put('email', $request['email']);
            return redirect('otp-verify');
       
        } else {
            http_response_code(500);
            //  echo '<script type="text/javascript">
            //                     alert("Failed to send email");
            //               </script>';
            echo json_encode(['error' => 'Failed to send email']);
        }
        
    // }
            
            
            
    //         $otp = rand(100000, 999999);
       
    //     $register = new User;
    //     $register -> type = $type;
    //     $register -> name = $request['name'];
    //     $register -> email = $request['email'];
    //     $register -> password = $request['password'];
    //     $register -> country = $request['country'];
    //     $register -> otp = $otp;
    //     $register->save();
    //   // Generate OTP

    //         // Send OTP email using the view
    //         Mail::send('emails.otp', ['name' => $request->input('name'), 'otp' => $otp], function ($message) use ($request) {
    //             $message->to($request->input('email'))
    //                 ->subject('Your OTP Code');
    //         });
    //         $request->session()->put('type', $type);
    //         $request->session()->put('username', $request['name']);
    //         $request->session()->put('email', $request['email']);

    //         // return redirect("otp");
    //         return redirect('otp-verify');
        }

    }
    
  
    
    
}
