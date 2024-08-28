<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite; 
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackgoogle()
    {
        try {
            // Use stateless to avoid session issues during development
            $user = Socialite::driver('google')->user();

            // Check if the user already exists
            $finduser = User::where('email', $user->email)->first();
            // dd($user);
            if (!$finduser) {
                // Create a new user if one doesn't exist
                $finduser=new User();
                $finduser->name=$user->name;
                $finduser->email=$user->email;
                $finduser->google_id=$user->id;
                $finduser->profile_image = $user->avatar;
                $finduser->type ="user";
                $finduser->status_type ="complete";

                $finduser->save();

            }
            session()->put('email', $finduser->email); // storing user email
            session()->put('type', $finduser->type); // storing user email
           return redirect('index');

            // Log in the user
            // Auth::login($user);

           
        } catch (\Exception $e) {
            dd($e); // Debugging, consider logging instead
        }
    }
}
