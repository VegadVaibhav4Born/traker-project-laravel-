<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
class projectcontroller extends Controller
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
                
                $email = $user->email;
                
             // Retrieve projects by email
            $project = Project::where('email', $email)->orwhereRaw('FIND_IN_SET(?, member_id)', [$user->id])
            ->whereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "accepted"', [$user->id])->get();

            // Retrieve projects by member_id
            // $requests = Project::where('member_id', 'LIKE', "%,$user->id,%")->get();
            $requests = Project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])
            ->whereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "pending"', [$user->id])
            ->get();
// Initialize an empty array for request projects
$projectsrequest = [];

// Populate the request_projects array with project details

foreach ($requests as $request) {
    $projectsrequest[] = [
        'project' => $request,
        'project_id' => $request->project_id,         
        'project_name' => $request->project_name,     
        'project_logo' => $request->project_logo,     
        'member_id' => $request->member_id,
        'price' => $request->project_price, 
        'acceptUrl' => route('projects.accept', ['projectId' => $request->id, 'userId' => $user->id]),
        'rejectUrl' => route('projects.reject', ['projectId' => $request->id, 'userId' => $user->id]),
    ];
}
$request_projects = $users_data['request_projects'] ?? [];
$projectCount = $users_data['projectCount'] ?? 0;
$project_request = $projectsrequest;
// Prepare data for view
$data = compact('project', 'user', 'request_projects','request_projects','projectCount','project_request');


                    //  return view('frontend.projects', ['email' => $email]);
                    return view('frontend.projects')->with($data);


        } else {
            return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
        }
    } else {
        return redirect()->route('login')->with('error', 'User not found.');
    }    
        
                
    }
    public function delete($id)
    { 
        $project = project::find($id);
        if(!is_null($project))
        {
            $project->delete();
            session()->flash('error', 'Project Deleted.');
             return redirect("projects");
             
        }
        else{
             session()->flash('error', 'Project Not Founde.');
            return redirect("projects");
           
        }
        
    }
 public function edit($id, Request $request)
{
     $headerController = new headerController();
    $users_data = $headerController->header($request);
     
    $email = $request->session()->get('email');
    $type = $request->session()->get('type');
    $user = User::where('email', $email)->where('type', $type)->first();
    $project = Project::find($id);

    if (is_null($project)) {
         session()->flash('error', 'Project Not Founde.');
        return redirect("projects");
    } else {
        // Fetch team members based on member IDs
        $teamMemberIds = explode(',', $project->member_id);
        $teamMembers = User::whereIn('id', $teamMemberIds)->get();

        $title = "Update Project";
        $url = url('project/update/') . "/" . $id;
        
             $request_projects = $users_data['request_projects'] ?? [];
            $projectCount = $users_data['projectCount'] ?? 0;
            $data = compact('project', 'url', 'title', 'user', 'teamMembers','request_projects','projectCount');
    }
     

    // Return the view with data
    return view('frontend.add-project')->with($data);
}


// public function update($id, Request $request)
// {
//     // Retrieve the existing project
//     $project = Project::find($id);
//     if (!$project) {
//          session()->flash('error', 'Project Not Found!');
//         return redirect()->back();
//     }

//     $memberIds = [];
//     $errors = [];

//     // Validate team members
//     foreach ($request->team_members as $index => $team_member) {
//         $existingUser = User::where('email', $team_member['email'])->first();

//         if ($existingUser) {
//             $userId = $existingUser->id;
//             $memberIds[] = $userId;
//         } else {
//             // Collect error for invalid emails
//             $errors["team_members.$index.email"] = 'Email does not exist in the database';
//         }
//     }

//     // If there are validation errors, redirect back with errors
//     if (!empty($errors)) {
//         return redirect()->back()->withErrors($errors)->withInput();
//     }

//     // Update project details
//     $project->email = $request->input('user_email');
//     $project->project_name = $request->input('project_name');
//     $project->project_currency = $request->input('currency');
//     $project->project_price = $request->input('price');
//     $project->start_date = $request->input('start_date');

//     // Save member IDs as a comma-separated string
//     $project->status = json_encode(array_fill_keys($memberIds, 'pending'));
//     $project->member_id = implode(',', $memberIds);

//     // Handle image upload if present
//     if ($request->hasFile('image')) {
//         $image = $request->file('image');
//         $destinationPath = public_path('images/project images');
//         $imageName = time() . '-' . $image->getClientOriginalName();
//         $image->move($destinationPath, $imageName);
        
//         $project->project_logo = 'project images/' . $imageName;
//     }

//     // Save the updated project
//     $project->save();

//     // Send emails to team members
//     foreach ($request->team_members as $team_member) {
//         $name = $team_member['name'];
//         $email = $team_member['email'];
//         $userId = User::where('email', $email)->first()->id;

//         $this->sendMemberEmail($project->id, $userId, $name, $email);
//     }
//     session()->flash('success', 'Project updated successfully!');
//     return redirect('projects');
// }
public function update($id, Request $request)
{
    // Retrieve the existing project
    $project = Project::find($id);
    if (!$project) {
         session()->flash('error', 'Project Not Found!');
        return redirect()->back();
    }

    $memberIds = [];
    $errors = [];

    // Get the current status of members
    $currentStatus = json_decode($project->status, true) ?? [];

    // Validate team members and update status
    foreach ($request->team_members as $index => $team_member) {
        $existingUser = User::where('email', $team_member['email'])->first();

        if ($existingUser) {
            $userId = $existingUser->id;
            $memberIds[] = $userId;

            // Check if the member is already in the project
            if (!isset($currentStatus[$userId])) {
                // If not, add them with 'pending' status
                $currentStatus[$userId] = 'pending';
            }
        } else {
            // Collect error for invalid emails
            $errors["team_members.$index.email"] = 'Email does not exist in the database';
        }
    }

    // If there are validation errors, redirect back with errors
    if (!empty($errors)) {
        return redirect()->back()->withErrors($errors)->withInput();
    }

    // Update project details
    $project->email = $request->input('user_email');
    $project->project_name = $request->input('project_name');
    $project->project_currency = $request->input('currency');
    $project->project_price = $request->input('price');
    $project->start_date = $request->input('start_date');

    // Save member IDs as a comma-separated string
    $project->status = json_encode($currentStatus);
    $project->member_id = implode(',', $memberIds);

    // Handle image upload if present
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $destinationPath = public_path('images/project images');
        $imageName = time() . '-' . $image->getClientOriginalName();
        $image->move($destinationPath, $imageName);
        
        $project->project_logo = 'project images/' . $imageName;
    }

    // Save the updated project
    $project->save();

    // Send emails to team members
    foreach ($request->team_members as $team_member) {
        $name = $team_member['name'];
        $email = $team_member['email'];
        $userId = User::where('email', $email)->first()->id;

        $this->sendMemberEmail($project->id, $userId, $name, $email);
    }
    session()->flash('success', 'Project updated successfully!');
    return redirect('projects');
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
