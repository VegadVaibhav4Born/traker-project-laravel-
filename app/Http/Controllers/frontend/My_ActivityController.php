<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;
use Carbon\Carbon;
class My_ActivityController extends Controller
{
    public function index(Request $request)
    {
        $headerController = new headerController();
    $users_data = $headerController->header($request);
        

        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user_h = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user_h) {
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();

                $activity_data = [];
                $dailyData = [];
                $user_id = $user->id;
                $activityQuery = Activity::where('member_id', $user_id);
        
                // Handle date range input
                $dateRange = $request->input('date_range');
                if ($dateRange) {
                    $dates = explode(' to ', $dateRange);
        
                    if (count($dates) === 2) {
                        $startDate = Carbon::parse($dates[0])->startOfDay();
                        $endDate = Carbon::parse($dates[1])->endOfDay();
                        $activityQuery->whereBetween('start_time', [$startDate, $endDate]);
                    }
                } else {
                    $today = Carbon::now();
                    $startDate = $today->copy()->subDays(6)->startOfDay();
                    $endDate = $today->endOfDay();
                }
        
                // Fetch activities based on query
                $activities = $activityQuery->orderBy('start_time', 'desc')->get();
                // Generate date groups
                $dateGroups = [];
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
        
                while ($start->lessThanOrEqualTo($end)) {
                    $groupEnd = $start->copy()->addDays(6);
                    if ($groupEnd->greaterThan($end)) {
                        $groupEnd = $end;
                    }
                    $dateGroups[] = $start->format('d M') . ' - ' . $groupEnd->format('d M');
                    $start = $groupEnd->copy()->addDay();
                }
        
                // Process each activity
               // Process each activity
               foreach ($activities as $activity) {
                $startTime = new Carbon($activity->start_time);
                $endTime = new Carbon($activity->end_time);
                $activityDate = $startTime->format('d M');
            
                $durationSeconds = $startTime->diffInSeconds($endTime);
            
                // Fetch project details and member information
                $project = Project::where('project_id', $activity->project_id)->first();
                $project_name = $project ? $project->project_name : 'Unknown Project'; // Handle null project
            
                $member = User::where('id', $activity->member_id)->first();
                $mem_name = $member ? $member->name : 'Unknown Member';
            
                // Extract activity ID
                $id = $activity->id;
            
                // Initialize daily data if not set
                if (!isset($dailyData[$activityDate])) {
                    $dailyData[$activityDate] = [
                        'total_duration' => 0,
                        'mouse_clicks' => 0,
                        'keyboard_hits' => 0,
                        'activities' => [],
                        'first_start_time' => $startTime,
                        'last_end_time' => $endTime,
                        'latest_activity' => $activity,
                        'project_name' => $project_name, // Store project name
                        'software_usage' => [],
                        'id' => $id, // Initialize activity_ids array
                       
                    ];
                }
            
                // Parse the software usage JSON
                $softwareData = json_decode($activity->software_use_name, true) ?? [];
            
                foreach ($softwareData as $minutes => $software) {
                    if (!isset($dailyData[$activityDate]['software_usage'][$software])) {
                        $dailyData[$activityDate]['software_usage'][$software] = 0;
                    }
                    $dailyData[$activityDate]['software_usage'][$software] += $minutes; // Aggregate minutes
                }
            
                // Aggregate daily data
                $dailyData[$activityDate]['total_duration'] += $durationSeconds;
                $dailyData[$activityDate]['mouse_clicks'] += $activity->mouse_click;
                $dailyData[$activityDate]['keyboard_hits'] += $activity->keyboard_click;
            
                // Update first start time and last end time
                if ($startTime < $dailyData[$activityDate]['first_start_time']) {
                    $dailyData[$activityDate]['first_start_time'] = $startTime;
                }
                if ($endTime > $dailyData[$activityDate]['last_end_time']) {
                    $dailyData[$activityDate]['last_end_time'] = $endTime;
                }
            
                // Add activity details including project name
                $activityDetails = $activity->toArray();
                $activityDetails['project_name'] = $project_name; // Add project name here
                $dailyData[$activityDate]['activities'][] = $activityDetails;
            
                // Store activity ID
                $dailyData[$activityDate]['activity_ids'][] = $id; // Add activity ID to the array
            }
            

            
        
            // Return view with the activity data
            return view('frontend.my-activity', [
                'dateRange' => $dateRange,
                'dates' => $dateGroups,
                'activity_data' => $activity_data,
                'dailyData' => $dailyData,
                'user'=>$user_h,
                 'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0
            ]);
                       
                    } else {
                return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
            }
        } else {
            return redirect()->route('login')->with('error', 'User not found.');
        }
    }
   public function SelectData(Request $request)
{
    // Reuse the logic from the index method, handle date range here
    return $this->index($request);
} 

public function SelectDataRedirect(Request $request)
{
    return redirect()->route('my-activity'); // Uses the corrected route name
}

    public function delete($id)
    { 
        $activity = Activity::find($id);
        if(!is_null($activity))
        {
            $activity->delete();
            session()->flash('error', 'Activity Deleted.');
             return redirect("my-activity");
             
        }
        else{
             session()->flash('error', 'Activity Not Founde.');
            return redirect("my-activity");
           
        }
        
    }
}