<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Models\country; 
use Illuminate\Support\Str;
class UserProfileController extends Controller
{
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
                // Retrieve all countries
                $countries = country::all();

                // Pass the user data and countries to the view
                return view('frontend.user-profile', ['user' => $user, 'countries' => $countries,'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0,]);
                // return redirect()->route('user-profile')->with(['user' => $user, 'countries' => $countries]);
            } else {
                // Display an alert and redirect to the login page
                return redirect()->route('login')->with('error', 'User not found.');
            }
        } else {
            // If no email is found in session, redirect to login page
            return redirect()->route('login')->with('error', 'Please login first.');
        }
    }


    public function update(Request $request)
    {
        $email = $request->input('email');
        $type = $request->session()->get('type');

        if ($email) {
            // Find the user by email
            $user = User::where('email', $email)->where('type', $type)->first();

            if ($user) {
                // Update user attributes
                $user->name = $request->input('name');
                $user->email = $request->input('email');
              
                $user->country = $request->input('country');
                $user->address = $request->input('address');
                $user->mobile = $request->input('mobile');

                // Save the updated user
            //   4BornWork/test.4born.in/storage/app/public/profile_images
                if ($request->hasFile('image')) {
                   
                    
                    // $image = $request->file('image');
                    // $path = $image->store('images', 'public'); // Store image in public/images directory
                    // $user->profile_image = $path; // Save the image path to the user model
                        $image = $request->file('image');
                        $destinationPath = public_path('images');
                        $imageName = time() . '-' . $image->getClientOriginalName();
                        $image->move($destinationPath, $imageName);
                        
                          $user->profile_image = 'images/' . $imageName;
 
                }
               
                $user->save();

                // Redirect to profile page with success message
                return redirect()->to('user-profile')->with('success', 'Profile updated successfully.');
            } else {
                // return back()->withError('status', 'Please Enter Valid Registerd Email.');
            
                //   return response()->json(['exists' => $user]);
                   return redirect()->back()->withErrors(['email' => 'Email does not exist in the database'])->withInput();
            }
        } else {
            return redirect()->to('login')->with('error', 'Please login first.');
        }
    }
}
