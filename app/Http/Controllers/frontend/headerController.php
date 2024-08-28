<?php
namespace App\Http\Controllers\frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\project;
use App\Models\notification;

class headerController extends Controller
{
    public function header(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)
                        ->where('email', $email)
                        ->where('type', $type)
                        ->first();
             
            if ($user) {
                // Fetch the user's project requests
                // $requests = project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])
                //                   ->whereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "pending"', [$user->id])
                //                   ->get();
                
                $requests = notification::where('member_id', $user->id)
                        ->get();
                $unread_project = notification::where('member_id', $user->id)->where('status','unread')->get();
                                
                // Initialize an array to hold project details
                $request_projects = [];
               
                foreach ($requests as $request) {
                     
                      $project = project::where('project_id', $request->project_id)->first();

                    $emailP = $project->email;
                    $createdby = User::where('email',$emailP)->first();
                    $request_projects[] = [
                        'project' => $project,
                        'id'=>$request->id,
                        'project_id' => $request->project_id,
                        'project_name' => $project->project_name,
                        'project_logo' => $project->project_logo,
                        'asign_project'=>$createdby->name,
                        'date'=>$request->date,
                    ];
                }
                $unread_request = [];
               foreach ($unread_project as $unreadrequest) 
                   {
                       $unread_request[] = [
                           ];
                       
                   }
                
                // Return data in a structured format
                return [
                    'request_projects' => $request_projects,
                    'projectCount' => count($unread_request)
                ];
            }
            
            return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
        }
        
        return redirect()->route('login')->with('error', 'User not found.');
    }
public function markAllAsRead(Request $request)
{
    $ids = $request->input('project_ids', []);
    
    if (count($ids) > 0) {
        // Update notifications status
        $updated = Notification::whereIn('id', $ids)->update(['status' => 'read']);
        
        if ($updated) {
            // Return JSON response indicating success
            return response()->json(['success' => true]);
        } else {
            // Return JSON response indicating failure
            return response()->json(['success' => false, 'message' => 'Failed to update notifications.']);
        }
    }
    
    // Return JSON response if no IDs are found
    return response()->json(['success' => false, 'message' => 'No notifications found.']);
}





}
