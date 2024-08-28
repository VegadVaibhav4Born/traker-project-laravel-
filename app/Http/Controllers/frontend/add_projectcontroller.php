<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\notification;
use Illuminate\Support\Facades\Mail;
class add_projectcontroller extends Controller
{
   public function index(Request $request)
{
    $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            
            $headerController = new headerController();
            $users_data = $headerController->header($request);
     
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
                $user = User::where('email', $email)->where('type', $type)->first();
                
                        $project = null;
                        $teamMembers = collect(); // Initialize as empty collection
                        $url = url('store-project');
                        $title = "Add Project";
                         $request_projects = $users_data['request_projects'] ?? [];
                         $projectCount = $users_data['projectCount'] ?? 0;
                        $data = compact('user', 'url', 'title', 'project', 'teamMembers','user','request_projects','projectCount');
                        
                        return view('frontend.add-project')->with($data);
                    } else {
                        return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
                    }
                } else {
                    return redirect()->route('login')->with('error', 'User not found.');
                }   
}

     public function checkEmail(Request $request)
{
    // $request->validate([
    //     'email' => 'required|email',
    // ]);

    // $email = $request->input('email');
    // $exists = User::where('email', $email)->exists();
    
    // return response()->json(['exists' => $exists]);
    
    $email = $request->input('email');
    $user = User::where('email', $email)->first();

    if ($user) {
        return response()->json(['exists' => true, 'name' => $user->name]);
    } else {
        return response()->json(['exists' => false]);
    }
}

    public function store(Request $request)
{
    $memberIds = [];
    $errors = [];
    
    foreach ($request->team_members as $index => $team_member) {
        $existingUser = User::where('email', $team_member['email'])->first();

        if ($existingUser) {
            $userId = $existingUser->id;
            $memberIds[] = $userId;
        } else {
            // Collect error for invalid emails
            $errors["team_members.$index.email"] = 'Email does not exist in the database';
        }
    }
     $type = $request->session()->get('type');
     $email = $request->session()->get('email');
      $user = User::where('email', $email)->where('type', $type)->first();
$name = $user->name;
    if (!empty($errors)) {
        return redirect()->back()->withErrors($errors)->withInput();
    }
    $p_id=rand(1000, 9999);
    // Save the project
    $project = new Project;
  
    $project->project_id = "$p_id";
    $project->name = $name;
    $project->email = $request->input('user_email');
    $project->project_name = $request->input('project_name');
    $project->project_currency = $request->input('currency');
    $project->project_price = $request->input('price');
    // $project->start_date = $request->input('start_date');
    $project->start_date = now()->format('Y-m-d');
    $project->end_date = $request->input('end_date');
    $project->project_status = "Pendding";
    // Save member IDs as a comma-separated string
    $project->status = json_encode(array_fill_keys($memberIds, 'pending'));
    $project->member_id = implode(',',$memberIds); 
    
    foreach($memberIds as $member)
    {
        $notification = new notification;
        $notification->project_id = "$p_id";
        $notification->date = now()->format('Y-m-d');
       
         $notification->status = "unread" ;
         $notification->member_id = $member ; 
         $notification->save();
    }
    if ($request->hasFile('image')) {
                   
                    // $image = $request->file('image');
                    // $path = $image->store('images', 'public'); // Store image in public/images directory
                    // $user->profile_image = $path; // Save the image path to the user model
                        $image = $request->file('image');
                        $destinationPath = public_path('images/project images');
                        $imageName = time() . '-' . $image->getClientOriginalName();
                        $image->move($destinationPath, $imageName);
                        
                          $project->project_logo = 'project images/' . $imageName;
 
                }
    
    $project->save();

      //send mail   
//  foreach ($request->team_members as $team_member) {
//         $name = $team_member['name'];
//         $email = $team_member['email'];
      
//         $this->sendMemberEmail($name, $email);
//     }
foreach ($request->team_members as $team_member) {
        $name = $team_member['name'];
        $email = $team_member['email'];
        $userId = User::where('email', $email)->first()->id;

        $this->sendMemberEmail($project->id, $userId, $name, $email);
}
    return redirect('projects')->with('success', 'Project created successfully!');
}


  public function acceptInvitation($projectId, $userId)
    {
        $project = Project::findOrFail($projectId);

        // Check if the user is part of the project team
        $memberIds = explode(',', $project->member_id);
        $status = json_decode($project->status, true);

        if (array_key_exists($userId, $status)) {
            $status[$userId] = 'accepted';
            $project->status = json_encode($status);
            $project->project_status = "On Work";

            $project->save();

        return redirect()->route('projectrequest', [
    'project' => $project,
    'memberid' => $memberIds,
    'status' => $status,
       'userId' => $userId
])->with('success', 'You have accepted the project invitation.');

        }

        return redirect('/projects')->with('error', 'Invalid project invitation.');
    }

    public function rejectInvitation($projectId, $userId)
    {
        $project = Project::findOrFail($projectId);

        // Check if the user is part of the project team
        $memberIds = explode(',', $project->member_id);
        $status = json_decode($project->status, true);

        if (array_key_exists($userId, $status)) {
            $status[$userId] = 'rejected';
            $project->status = json_encode($status);
            $project->save();
 $userStatus = $userId && isset($status[$userId]) ? $status[$userId] : 'No status available';

            // return redirect('/projects')->with('success', 'You have rejected the project invitation.');
             return redirect()->route('projectrequest', [
    'project' => $project,
    'memberid' => $memberIds,
    'status' => $status,
    'userId' => $userId
])->with('success', 'You have rejected the project invitation.');

        }

        return redirect('/projects')->with('error', 'Invalid project invitation.');
    }


protected function sendMemberEmail($projectId, $userId, $name, $email)
{
    $subject = 'Invitation to Join Project';
    
    $acceptUrl = route('projects.accept', ['projectId' => $projectId, 'userId' => $userId]);
    $rejectUrl = route('projects.reject', ['projectId' => $projectId, 'userId' => $userId]);

    $message = '<html><body>';
       $message .= '<style>';
    $message .= '.btn {';
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
    $message .= '    margin: 5px;';
    $message .= '}';
    $message .= '.btn-accept { background-color: #28a745; }';
    $message .= '.btn-reject { background-color: #dc3545; }';
    $message .= '</style>';

    $message .= '<div style="border: 1px solid #666; padding: 10px; color: #ffffff; text-align: center;">';
    $message .= '<h2 style="color:#0a58ca;">Dear ' . $name . ',</h2>';
    $message .= '<div style="border: 1px solid #ddd; background-color: #fff; padding: 20px; color: #222; font-family: Arial, sans-serif; line-height: 1.5; text-align: center;">';
    $message .= '<h2 style="color:#222; margin-bottom: 10px;">You have been invited to join a project.</h2>';
    $message .= '<p style="color:#777; margin-bottom: 30px;">Please choose one of the options below:</p>';

  $message .= '<a href="' . $acceptUrl . '" style="background: linear-gradient(310deg, #7928ca, #ff0080); color: #ffffff; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; box-shadow: 2px 2px 5px #ddd; text-decoration: none; display: inline-block; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;">Accept</a>';
    $message .= '   <a href="' . $rejectUrl . '" style="background: linear-gradient(310deg, #7928ca, #ff0080); color: #ffffff; border: none; padding: 10px 20px; font-size: 18px; border-radius: 5px; box-shadow: 2px 2px 5px #ddd; text-decoration: none; display: inline-block; transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;">Reject</a>';

    $message .= '</div>';
    $message .= '</div>';
    $message .= '</body></html>';

    $headers = "From: Tracker\r\n";
    $headers .= "Reply-To: Tracker\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Sending email
    mail($email, $subject, $message, $headers);
}


}
 // foreach ($request->team_members as $team_member) {
        //     $existingUser = User::where('email', $team_member['email'])->first();

        //     if ($existingUser) {
        //         $userId = $existingUser->id;
        //     } else {
        //         $newUser = new User();
        //         $newUser->type = 'user';
        //         $newUser->name = $team_member['name'];
        //         $newUser->email = $team_member['email'];
        //         $newUser->save();
        //         $userId = $newUser->id;
                
        //     }

        //     // $project->User()->attach($userId);
        // } 
        // $project = new Project;
        // $project->name = $request->input('user_name');
        // $project->email = $request->input('user_email');
        // $project->project_name = $request->input('project_name');
        // $project->project_currency = $request->input('currency');
        // $project->start_date = $request->input('start_date');
        // $project->member_id = $userId;
        
        // $project->save();
        // return redirect('projects');
        
 // Create the project
        // $project = new Project;
        // $project->name = $request->input('user_name');
        // $project->email = $request->input('user_email');
        // $project->project_name = $request->input('project_name');
        // $project->project_currency = $request->input('currency');
        // $project->start_date = $request->input('start_date');
        // $project->save();

        // // Handle team members
        // $memberIds = [];

        // foreach ($request->team_members as $team_member) {
        //     $existingUser = User::where('email', $team_member['email'])->first();

        //     if ($existingUser) {
        //         $userId = $existingUser->id;
        //     } else {
        //         $newUser = new User();
        //         $newUser->type = 'user';
        //         $newUser->name = $team_member['name'];
        //         $newUser->email = $team_member['email'];
        //         $newUser->save();
        //         $userId = $newUser->id;
        //     }

        //     $memberIds[] = $userId;
        // }

        // // Store member IDs as a serialized array in the project record
        // $project->member_id = serialize($memberIds);
        // $project->save();
     
        // return redirect('projects');
        
       
        // Handle team members