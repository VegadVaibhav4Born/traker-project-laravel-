<?php
namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\TrakingUser;

class ResetPasswordController extends Controller
{
    public function showResetForm($token)
    {
        // return view('auth.passwords.reset', ['token' => $token]);
        return view('frontend.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
            'token' => 'required',
        ]);

        $user = TrakingUser::where('remember_token', $request->token)->first();

        if ($user) {
            // $user->password = Hash::make($request->password);
            $user->password = $request->password;

            $user->remember_token = null; // Invalidate the token
            $user->save();

            return redirect()->route('login')->with('success', 'Your password has been successfully updated. You may now continue to login.');
        }
     
        return back()->withErrors(['token' => 'The provided token is invalid.']);
    }
}
