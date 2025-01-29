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
            
                $requests = notification::where('member_id', $user->id)
                        ->orderBy('id', 'DESC')->get();
                $unread_project = notification::where('member_id', $user->id)->where('status','unread')->get();
                // Initialize an array to hold project details
                $request_projects = [];
               
                foreach ($requests as $request) {
                     
                    $project = project::where('project_id', $request->project_id)->orderBy('id', 'DESC')
                     ->first();
   
    
                    //  $project = Project::where('project_id', $request->project_id)
                    // ->where(function ($query) use ($user) {
                    //     $query->whereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "pending"', [$user->id])
                    //           ->orWhereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "Deactivated"', [$user->id]);
                    // })
                    // ->orderBy('id', 'desc')
                    // ->first(); 
            
           $sender_id = $request->sender_id; // Assuming $sender_id is passed in the request
$sender = User::find($sender_id); // Get the sender user

$desiredStatuses = ['accepted', 'rejected']; // Array of statuses

$Client_project = Project::where('project_id', $request->project_id)
    ->where('email', $user->email)
    ->where(function ($query) use ($sender_id, $desiredStatuses) {
        foreach ($desiredStatuses as $status) {
            $query->orWhereJsonContains("status->{$sender_id}", $status);
        }
    })
    ->first();
                    
            // Provide default values if $project is null
                    $statusJson = $project ? $project->status : '{}';
                    $statusArray = json_decode($statusJson, true) ?: [];
                    $userId = $user->id;
                    $userStatus = isset($statusArray[$userId]) ? $statusArray[$userId] : 'unknown';
                    $memberStatus = isset($statusArray[$sender_id]) ? $statusArray[$sender_id] : 'unknown';
                        $emailP = $project->email;
                        $createdby = User::where('email',$emailP)->first();
                        $request_projects[] = [
                        'project' => $project,
                        'client_project'=>$Client_project,
                        'member_name' => $sender ? $sender->name : 'unknown', // Ensure $sender is not null
    'member_status' => $memberStatus,
                        'id'=>$request->id,
                        'project_id' => $request->project_id,
                        'project_name' => $project->project_name,
                        'project_logo' => $project->project_logo,
                        'asign_project'=>$createdby->name,
                        'date'=>$request->date,
                        'user_status' => $userStatus,
                        
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
                    'projectCount' => count($unread_request),
                    'type'=>$type,
                    'user'=>$user
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
