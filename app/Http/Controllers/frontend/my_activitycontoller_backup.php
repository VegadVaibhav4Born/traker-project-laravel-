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

        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
                $projectsQuery = Project::where('email', $email);
     
                $dateRange = $request->input('date_range');
                if ($dateRange) {
                            $dates = explode(' to ', $dateRange);
            
                            if (count($dates) === 2) {
                                $startDate = Carbon::parse($dates[0])->startOfDay();
                                $endDate = Carbon::parse($dates[1])->endOfDay();
            
                                $projectsQuery->whereBetween('start_date', [$startDate, $endDate]);
                            }
                        }
                            // Filter by selected project if provided
                            // if ($selectedProjectId) {
                            //     $projectsQuery->where('project_id', $selectedProjectId);
                            // }
              
                            $projects = $projectsQuery->get();
                            // Initialize arrays
                            $memberIds = [];
                            $projectIds = [];
                            $projectNames = [];
            
                            // Gather member IDs and project IDs from the projects
                            foreach ($projects as $project) {
                                if ($project->member_id) {
                                    $ids = explode(',', $project->member_id);
                                    $memberIds = array_merge($memberIds, $ids);
                                    $projectIds[] = $project->project_id;
                                    $projectNames[] = $project->project_name;
                                }
                            }
                            // Remove duplicates
                            $projectIds = array_unique($projectIds);
                            $memberIds = array_unique($memberIds);
            
                            // Get project names
                            $projects_name = Project::whereIn('project_id', $projectIds)->pluck('project_name', 'project_id')->toArray();
            
                            // Get users
                            $users = User::whereIn('id', $memberIds)->get();
                            $memberNames = $users->pluck('name', 'id')->toArray();
            
                            // Get activities
                            $activities = Activity::whereIn('project_id', $projectIds)->get();
    
            
             if ($dateRange) {
                            $dates = explode(' to ', $dateRange);
                
                            if (count($dates) === 2) {
                                $startDate = Carbon::parse($dates[0])->startOfDay();
                                $endDate = Carbon::parse($dates[1])->endOfDay();
                            }
                        }   
                        else{
                            $today = Carbon::now();
                                $startDate = $today->copy()->subDays(6);
                                $endDate = $today;
                        }   
                        $today = Carbon::now();
                        $lastWeek = $today->subDays(6);
                      
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
                        $projectDetails = [];
                 
                        
foreach ($projects as $project) {
    $activities = Activity::where('project_id', $project->project_id)->get();

    foreach ($activities as $activity) {
        $startTime = new Carbon($activity->start_time);
        $endTime = new Carbon($activity->end_time);
        $activityDate = Carbon::parse($activity->start_time)->format('d M');

        $durationSeconds = $startTime->diffInSeconds($endTime);

        // Initialize daily data
        if (!isset($dailyData[$activityDate])) {
            $dailyData[$activityDate] = [
                'total_duration' => 0,
                'mouse_clicks' => 0,
                'keyboard_hits' => 0,
                'activities' => [],
                'first_start_time' => $startTime,
                'last_end_time' => $endTime
            ];
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

        // Add activity details
        $dailyData[$activityDate]['activities'][] = $activity;

        // Activity log start
        $projectDetails = Project::where('project_id', $activity->project_id)->first();
        $project_name = $projectDetails ? $projectDetails->project_name : 'Unknown Project';
        $member = User::where('id', $activity->member_id)->first();
        $mem_name = $member ? $member->name : 'Unknown Member';

        $activity_data[] = [
            'activity_name' => $activity->title,
            'start_time' => $activity->start_time,
            'end_time' => $activity->end_time,
            'project_id' => $project_name,
            'member_name' => $mem_name,
            'screenshot' => $activity->screenshot,
            'mouse_click' => $activity->mouse_click,
            'keyboard_hit' => $activity->keyboard_click,
            'date' => $activityDate
        ];
    }
}
return view('frontend.my-activity', [
                            'projects' => $projects,
                            'members' => $memberNames,
                            // 'projectDetails' => $projectDetails,
                            'dateRange' => $dateRange,
                             'dates' => $dateGroups, 
                            'activity_data' =>$activity_data,
                            'dailyData'=>$dailyData,
                            'user'=>$user,
                        ]);

                       
                    } else {
                return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
            }
        } else {
            return redirect()->route('login')->with('error', 'User not found.');
        }
    }
    public function select_data(Request $request)
    {
        // Reuse the logic from the index method, handle date range here
        return $this->index($request);
    } 
}



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

        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
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

                    // Initialize daily data
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


                }

            
        
            // Return view with the activity data
            return view('frontend.my-activity', [
                'dateRange' => $dateRange,
                'dates' => $dateGroups,
                'activity_data' => $activity_data,
                'dailyData' => $dailyData,
            ]);
                       
                    } else {
                return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
            }
        } else {
            return redirect()->route('login')->with('error', 'User not found.');
        }
    }
    public function select_data(Request $request)
    {
        // Reuse the logic from the index method, handle date range here
        return $this->index($request);
    } 
}