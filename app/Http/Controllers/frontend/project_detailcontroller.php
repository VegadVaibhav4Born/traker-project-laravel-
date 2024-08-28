<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;
use Carbon\Carbon;
class project_detailcontroller extends Controller
{
    //
    public function index($id, Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');
       
        if ($email) {
            
             $headerController = new headerController();
        $users_data = $headerController->header($request);
        
            $status_type = 'complete';
            $user_h = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user_h) {
                $user = User::where('email', $email)->where('type', $type)->first();
                
                $project = Project::find($id);
                    $project_id = $project->project_id;
                //  $memberId = $project->member_id;
                $totalMouseClicks = 0;
                
                $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);
               
                // Initialize totals
                $totalMouseClicks = 0;
                $totalKeyboardHits = 0;
                $totalDuration = 0;
                // Loop through each member ID

                foreach ($memberIds as $memberId) {
                    
                    // Fetch mouse clicks for the current member and project
                    $mouseClicks = Activity::where('member_id', $memberId)
                                            ->where('project_id', $project_id) // Make sure to use $id or $project_id as needed
                                            ->sum('mouse_click');
                    $totalMouseClicks += $mouseClicks;
                
                    // Fetch keyboard clicks for the current member and project
                    $keyboardClicks = Activity::where('member_id', $memberId)
                                               ->where('project_id', $project_id) // Make sure to use $id or $project_id as needed
                                               ->sum('keyboard_click');
                    $totalKeyboardHits += $keyboardClicks;
                    //  $startTime = Activity::where('member_id', $memberId)
                    //     ->where('project_id', $id)
                    //     ->sum('start_time'); // Assuming 'duration' is a field in Activity
                    
                    //  $endTime = Activity::where('member_id', $memberId)
                    //     ->where('project_id', $id)
                    //     ->sum('end_time');
                    $activities = Activity::where('member_id', $memberId)
                          ->where('project_id', $project_id)
                          ->get();
                                               
                          $duration = 0;
                    foreach ($activities as $activity) {
                    $startTime = new \Carbon\Carbon($activity->start_time); // Convert to Carbon instance
                    $endTime = new \Carbon\Carbon($activity->end_time); // Convert to Carbon instance
                  
                   $duration += $startTime->diffInSeconds($endTime); // Calculate duration in seconds
                
                        
                    }
               
                    $totalDuration += $duration;
                    $member_status= project::select('status')->where('project_id', $project_id)->get() ;// Make sure to use $id or $project_id as needed
                      
                 // Assuming $memberId and $project are defined and available in this context

    // Fetch the user based on the memberId
    $member = User::where('id', $memberId)->first();
    // $usersData = [];
        if ($member) {
            // Decode the project's status field, defaulting to an empty array if decoding fails
            $statusMap = json_decode($project->status, true) ?? [];
           
            
        // Prepare the data for the user

       
        } 
        $usersData[] = [
            'users' => $member , // Store the user object
            'mouseClicks' => $mouseClicks ?? '', // Number of mouse clicks
            'keyboardClicks' => $keyboardClicks ?? '', // Number of keyboard clicks
            'image' => $member->profile_image ?? '', // User's profile image
            'id' => $member->id ?? '', // User's ID
            // 'user' => $user_h , // Storing the user object again seems redundant, consider removing this line if not needed
            'memberStatuses' => $statusMap[$memberId] ?? 'unknown', // Retrieve the status for this member or 'unknown' if not found
            'totalDuration' => formatDurationInSeconds($duration) // Format the duration in seconds
        ];
       

                
                
        
                }
             
                if (is_null($project)) {
                    session()->flash('error', 'Project Not Founde.');
                        return redirect("projects");
                } else {
                    // Fetch team members based on member IDs
                    $teamMemberIds = explode(',', $project->member_id);
                    $teamMembers = User::whereIn('id', $teamMemberIds)->get();
                
                }
      if ($users_data) {
                 if ($users_data instanceof \Illuminate\Http\RedirectResponse) {
                return $users_data; // Return the redirect response
                }
      }
            return view('frontend.project-details',['project'=>$project,'totalMouseClicks'=>$totalMouseClicks,
            'totalKeyboardHits'=>$totalKeyboardHits,'user'=>$user_h,'usersData'=>$usersData,
            'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0,
            ]);
        } else {
            return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
        }
    } else {
        return redirect()->route('login')->with('error', 'User not found.');
    }   
                 
    }
     
   
public function DeleteMember($memberid, $projectid)
{
   
    $projects = Project::where('project_id', $projectid)
    ->where('member_id', 'LIKE', "%$memberid%")
    ->get();
    if (!is_null($projects)) {
        // Fetch projects that have the specified project ID and contain the member ID
       

        foreach ($projects as $project) {
            $memberIds = explode(',', $project->member_id);
            $memberIds = array_map('trim', $memberIds); // Remove any extra spaces

            // If the member is the only member in the list
            // if (count($memberIds) == 1) {
            //     session()->flash('warning', 'Cannot delete the last member. Please add a new member before deleting this one.');
            //     return redirect()->back();
            // }
        }

        // Remove member ID from all projects
        // foreach ($projects as $project) {
        //     $memberIds = explode(',', $project->member_id);
        //     $updatedMemberIds = array_filter($memberIds, function ($id) use ($memberid) {
        //         return trim($id) != $memberid;
        //     });

        //     // $project->member_id = implode(',', $updatedMemberIds);
             $status = json_decode($project->status, true);
             if (isset($status[$memberid])) {
                     $status[$memberid] = 'Deactivated';
                }

    // Encode the status back to JSON
            $project->status = json_encode($status);
            $project->save();
        // }

        session()->flash('success', 'Member Deactivated Success.');
    } else {
        session()->flash('error', 'Member not found.');
    }

    return redirect()->back();
}



    public function ChangeStatus(Request $request, $id)
    {
        // Access the status value from the form submission
        $status = $request->input('status');
        // Fetch the project using the project ID
        $project = Project::find($id);
        if ($project) {
            // Update the project's status
            $project->project_status = $status;
            $project->save(); // Save changes to the database 
            session()->flash('success', "Project status changed to $status.");
        } else {
            session()->flash('error', 'Project not found.');
        }
    
        // Redirect back to the previous page or another page
        return redirect()->back();
    }
}
