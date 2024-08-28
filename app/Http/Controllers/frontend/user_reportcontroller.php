<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;
use Carbon\Carbon;
class user_reportcontroller extends Controller
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
               
                    $selectedProjectId = $request->project; 
                    $selectedMembers = $request->input('member', []); 
                    $dateRange = $request->input('date_range');
                    $select_by = $request->input('select_by');
                    $selected_project = $request->input('project', '');
                    $selected_members = $request->input('member', []);
                //               SELECT * FROM `project` 
                // WHERE email = 'vvegad1525@gmail.com' 
                // AND start_date BETWEEN '2024-07-15' AND '2024-08-05';

$activityQuery = Activity::where('member_id', $user_h->id);
// $dateRange = '2024-08-04 to 2024-10-11';
// Apply date range filter
if ($dateRange) {
    $dates = explode(' to ', $dateRange);
    if (count($dates) === 2) {
        $startDate = Carbon::parse($dates[0])->startOfDay();
        $endDate = Carbon::parse($dates[1])->endOfDay();
        $activityQuery->whereBetween('start_time', [$startDate, $endDate]);
    }
} else {
    $startDate = Carbon::now()->subDays(7)->startOfDay();
    $endDate = Carbon::now()->endOfDay();
    $activityQuery->whereBetween('start_time', [$startDate, $endDate]);
}

// Fetch activities
$activities = $activityQuery->get();

// Handle no activities case
if ($activities->isEmpty()) {
    $chartData = [
        'dates' => [],
        'projects' => [],
        'series' => [],
        'projectDetails' => []
    ];
   
}

// Initialize arrays for project IDs and member IDs
$projectIds = $activities->pluck('project_id')->unique();
$memberIds = $activities->pluck('member_id')->unique();

// Get users
$memberNames = User::whereIn('id', $memberIds)->pluck('name', 'id')->toArray();

// Function to convert HH:MM:SS to total seconds
function durationToSeconds($duration)
{
    list($hours, $minutes, $seconds) = explode(':', $duration);
    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

// Group activities by date and calculate durations
$durationsByDate = $activities->groupBy(function ($activity) {
    return Carbon::parse($activity->start_time)->toDateString(); // Group by date
})->map(function ($dateGroup) use ($projectIds) {
    return $dateGroup->groupBy('project_id')->map(function ($projectGroup) {
        return $projectGroup->sum(function ($activity) {
            return durationToSeconds($activity->durations);
        });
    });
})->toArray();

// Prepare chart data
$chartData = [
    'dates' => array_keys($durationsByDate), // Dates for the x-axis
    'projects' => $projectIds->toArray(), // Project IDs
    'series' => [], // Series to hold data
    'projectDetails' => [] // Project details for tooltip
];

// Create series for each project
foreach ($chartData['projects'] as $projectId) {
     $project = Project::where('project_id', $projectId)->first();
     $pname = $project->project_name;
  
  
    $projectSeries = [];
    foreach ($chartData['dates'] as $date) {
        $projectSeries[] = isset($durationsByDate[$date][$projectId]) ? $durationsByDate[$date][$projectId] : 0;
    }
    $chartData['series'][] = [
        'name' => " $pname", // Series name
        'data' => $projectSeries // Durations for this project
    ];
}

// Prepare project details for tooltips
$chartData['projectDetails'] = $durationsByDate;

$projects_data = Project::whereIn('project_id', $projectIds)->get();

        // Pass chartData to the view
       
$projects = Project::whereIn('project_id', $projectIds)->get();
$select_by = $request->input('select_by');
// in this code if date select kare to te date range ma jetli pan memebr id je user ni id hoy teni sathe compare karo ane activity mathi duration count karo ane chart ma niche date ane te date ma ketlu duration che te display karo ane tooltip ma activity mathi te project id ne display
    switch ($select_by) {
        case 'show_by_project':
                        // $dateRange = '2024-05-10 to 2024-08-03';

                       // Generate the dates array for date groups
                        $dates = [];
                        if ($dateRange) {
                            $dates = $this->getDatesBetween($startDate, $endDate);
                        } else {
                            $today = Carbon::now();
                            $lastWeek = $today->subDays(6);
                            
                            for ($i = 0; $i < 7; $i++) {
                                $dates[] = $lastWeek->copy()->addDays($i)->format('d M');
                            }
                        }
                    
                        // Generate date groups
                        $dateGroups = [];
                        if ($dateRange) {
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
                        } else {
                            $start = Carbon::parse($startDate);
                            $end = Carbon::parse($endDate);
                    
                            while ($start->lessThanOrEqualTo($end)) {
                                $groupEnd = $start->copy()->addDays(1);
                                if ($groupEnd->greaterThan($end)) {
                                    $groupEnd = $end;
                                }
                                $dateGroups[] = $start->format('d M') . ' - ' . $groupEnd->format('d M');
                                $start = $groupEnd->copy()->addDay();
                            }
                        }
                    
                        // Initialize $activity_data as an array
                       $activity_data = [];
                        $dailyDurations = [];
                    
                    // Process activities for detailed data
                    foreach ($activities as $activity) {
                        $startTime = new Carbon($activity->start_time);
                        $endTime = new Carbon($activity->end_time);
                        $totalSeconds = $startTime->diffInSeconds($endTime);
                    
                        $activityDate = Carbon::parse($activity->start_time)->format('d M');
                        $dailyDurations[$activityDate] = ($dailyDurations[$activityDate] ?? 0) + $totalSeconds;
                    
                        // Group activities by date
                        $project = Project::where('project_id', $activity->project_id)->first();
                        $project_name = $project ? $project->project_name : 'Unknown Project';
                        $member = User::where('id', $activity->member_id)->first();
                        $mem_name = $member ? $member->name : 'Unknown Member';
                    
                        $activity_data[] = [
                            'activity_name' => $activity->title,
                            'start_time' => $activity->start_time,
                            'end_time' => $activity->end_time,
                            'project_id' => $project_name,
                            'member_name' => $mem_name,
                            'screenshot' => $activity->screenshot, // Assuming you have this field
                            'formatted_duration' => formatDurationInSeconds($totalSeconds),
                        ];
                    }
                        // Format total duration
                        function formatDurationInSeconds($totalSeconds) {
                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        }
                    
                        // Calculate total durations for each 7-day group
                        $groupDurations = [];
                        foreach ($dateGroups as $dateGroup) {
                            list($start, $end) = explode(' - ', $dateGroup);
                            $startDateGroup = Carbon::createFromFormat('d M', $start);
                            $endDateGroup = Carbon::createFromFormat('d M', $end);
                    
                            $groupTotalDuration = 0;
                            foreach ($dailyDurations as $date => $duration) {
                                $dateCarbon = Carbon::createFromFormat('d M', $date);
                                if ($dateCarbon->between($startDateGroup, $endDateGroup)) {
                                    $groupTotalDuration += $duration;
                                }
                            }
                    
                            $groupDurations[$dateGroup] = $groupTotalDuration;
                        }
                    
                        // Sum of all group durations
                        $sumOfGroupDurations = array_sum($groupDurations);
                    
                        // Process user data for each member
                        $user_data = [];
                        
                    
                        // Collect project details
                        $projectDetails = [];
                        foreach ($projectIds as $projectId) {
                            $project = Project::where('project_id', $projectId)->first();
                            $pname = $project ? $project->project_name : 'Unknown Project';
                            
                            $totalSeconds = 0;
                            $dailyDurations = [];
                            
                            foreach ($memberIds as $memberId) {
                           
                                $durationmember = 0; // Reset the member duration for each member
                                
                                        $activities_m = Activity::where('member_id', $memberId)
                                            ->where('project_id', $project->project_id)
                                            ->get();
                                
                                        foreach ($activities_m as $activity) {
                                            list($hours, $minutes, $seconds) = explode(':', $activity->durations);
                                        $durationmember += ($hours * 3600) + ($minutes * 60) + $seconds;
                                    }
                            $user = User::find($memberId);
                            if ($user) {
                                $user_data[$memberId] = [
                                    'user' => $user,
                                    'image' => $user->profile_image,
                                    'totalDuration' => formatDurationInSeconds($durationmember)
                                ];
                            }
                        }
                            
                            
                            
                            foreach ($activities as $activity) {
                                if ($activity->project_id == $projectId) {
                                    $startTime = new Carbon($activity->start_time);
                                    $endTime = new Carbon($activity->end_time);
                                    $totalSeconds += $startTime->diffInSeconds($endTime);
                            
                                    $activityDate = Carbon::parse($activity->start_time)->format('d M');
                                    if (!isset($dailyDurations[$activityDate])) {
                                        $dailyDurations[$activityDate] = 0;
                                    }
                                    $dailyDurations[$activityDate] += $startTime->diffInSeconds($endTime);
                                }
                            }
                            
                            $formattedDuration = formatDurationInSeconds($totalSeconds);
                            
                            // Calculate total durations for each 7-day group
                            $groupDurations = [];
                            foreach ($dateGroups as $dateGroup) {
                                list($start, $end) = explode(' - ', $dateGroup);
                                $startDateGroup = Carbon::createFromFormat('d M', $start);
                                $endDateGroup = Carbon::createFromFormat('d M', $end);
                            
                                $groupTotalDuration = 0;
                                foreach ($dailyDurations as $date => $duration) {
                                    $dateCarbon = Carbon::createFromFormat('d M', $date);
                                    if ($dateCarbon->between($startDateGroup, $endDateGroup)) {
                                        $groupTotalDuration += $duration;
                                    }
                                }
                            
                                $groupDurations[$dateGroup] = $groupTotalDuration;
                            }
                            
                            $projectDetails[] = [
                                'id' => $project->id,
                                'project_id' => $project->project_id,
                                'project_logo' => $project->project_logo,
                                'project_name' => $pname,
                                'total_duration' => formatDurationInSeconds($totalSeconds),
                                'daily_durations' => $dailyDurations,
                                'user_data' => $user_data,
                                'group_durations' => $groupDurations
                            ];
                        }
                    
                    
                    // Return the full view for normal requests
                    return view('frontend.user-report', [
                        'projects' => $projects,
                        'chartData' => $chartData,
                        'projectDetails' => $projectDetails,
                        'dateRange' => $dateRange,
                        'selected_project' => $selected_project,
                        'selected_members' => $selected_members,
                        'select_by' => $select_by,
                        'dates' => $dateGroups,
                        'activity_data' => $activity_data,
                        'user' => $user_h,
                        'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0,
                    ]); 
        break;

        case 'show_by_member':
                
            // Initialize today
        $today = Carbon::now();
        // Generate the dates array
        $dates = [];
    $start = Carbon::parse($startDate);
    $end = Carbon::parse($endDate);

    while ($start->lessThanOrEqualTo($end)) {
        $dates[] = $start->format('d M');
        $start->addDay();
    }

    $formattedDates = $dates;

        // Generate date groups
        $dateGroups = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($dateRange) {
            while ($start->lessThanOrEqualTo($end)) {
                $groupEnd = $start->copy()->addDays(6);
                if ($groupEnd->greaterThan($end)) {
                    $groupEnd = $end;
                }
                $dateGroups[] = $start->format('d M') . ' - ' . $groupEnd->format('d M');
                $start = $groupEnd->copy()->addDay();
            }
        } else {
            while ($start->lessThanOrEqualTo($end)) {
                $groupEnd = $start->copy()->addDays(1);
                if ($groupEnd->greaterThan($end)) {
                    $groupEnd = $end;
                }
                $dateGroups[] = $start->format('d M') . ' - ' . $groupEnd->format('d M');
                $start = $groupEnd->copy()->addDay();
            }
        }

        // Initialize arrays
      

    // Initialize arrays
        $memberDurations = [];
        $groupDurations = [];
        $projectDetails = [];
        $activity_data = [];
        $dailyDurations = [];
          
 
          
                    // Process activities for detailed data
                    foreach ($activities as $activity) {
                        $startTime = new Carbon($activity->start_time);
                        $endTime = new Carbon($activity->end_time);
                        $totalSeconds = $startTime->diffInSeconds($endTime);
                    
                        $activityDate = Carbon::parse($activity->start_time)->format('d M');
                        $dailyDurations[$activityDate] = ($dailyDurations[$activityDate] ?? 0) + $totalSeconds;
                    
                        // Group activities by date
                        $project = Project::where('project_id', $activity->project_id)->first();
                        $project_name = $project ? $project->project_name : 'Unknown Project';
                        $member = User::where('id', $activity->member_id)->first();
                        $mem_name = $member ? $member->name : 'Unknown Member';
                    
                        $activity_data[] = [
                            'activity_name' => $activity->title,
                            'start_time' => $activity->start_time,
                            'end_time' => $activity->end_time,
                            'project_id' => $project_name,
                            'member_name' => $mem_name,
                            'screenshot' => $activity->screenshot, // Assuming you have this field
                            'formatted_duration' => formatDurationInSeconds($totalSeconds),
                        ];
                    }
    // Calculate project durations by member
    foreach ($projectIds as $projectId) {
        $project = Project::where('project_id', $projectId)->first();
        $memberIds = is_array($user_h->id) ? $user_h->id : [$user_h->id];

        foreach ($memberIds as $memberId) {
            if (!isset($memberDurations[$memberId])) {
                $memberDurations[$memberId] = array_fill_keys($formattedDates, 0);
                $groupDurations[$memberId] = array_fill_keys($dateGroups, 0);
                $projectDetails[$memberId] = [];
            }

            $memberActivities = Activity::where('member_id', $memberId)
                ->where('project_id', $projectId)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();

            $projectTotalSeconds = 0;

            foreach ($memberActivities as $activity) {
                $startTime = new Carbon($activity->start_time);
                $endTime = new Carbon($activity->end_time);
                $durationInSeconds = $startTime->diffInSeconds($endTime);

                $activityDate = $startTime->format('d M');
                $memberDurations[$memberId][$activityDate] += $durationInSeconds;

                foreach ($dateGroups as $group) {
                    list($groupStart, $groupEnd) = explode(' - ', $group);
                    $groupStartDate = Carbon::createFromFormat('d M', $groupStart)->startOfDay();
                    $groupEndDate = Carbon::createFromFormat('d M', $groupEnd)->endOfDay(); // Ensure end of day

                    if ($startTime->between($groupStartDate, $groupEndDate)) {
                        $groupDurations[$memberId][$group] += $durationInSeconds;
                    }
                }

                $projectTotalSeconds += $durationInSeconds;
            }

            $projectHours = floor($projectTotalSeconds / 3600);
            $projectMinutes = floor(($projectTotalSeconds % 3600) / 60);
            $projectSeconds = $projectTotalSeconds % 60;
            $formattedProjectDuration = sprintf('%02d:%02d:%02d', $projectHours, $projectMinutes, $projectSeconds);

            $projectDetails[$memberId][$projectId] = [
                'project_id' => $projectId,
                'project_name' => $project->project_name,
                'project_logo' => $project->project_logo,
                'total_duration' => $formattedProjectDuration,
                'Activity' => $activity_data,
            ];
        }
    }

    // Prepare data for each member
    $membersData = [];
    foreach ($memberDurations as $memberId => $dailyDurations) {
        $user = User::find($memberId);
        if ($user) {
            $totalSeconds = array_sum($dailyDurations);
            $formattedDuration = formatDurationInSeconds($totalSeconds);

            $membersData[] = [
                'user' => $user,
                'image' => $user->profile_image,
                'user_id' => $user->id,
                'totalDuration' => $formattedDuration,
                'daily_durations' => $dailyDurations,
                'group_durations' => $groupDurations[$memberId] ?? [],
                'projects' => $projectDetails[$memberId] ?? [],
            ];
        }
    }

    // Return the view with the processed data
    return view('frontend.user-report', [
        'projects' => $projects,
        'members' => $memberNames,
        'chartData' => $chartData,
        'membersData' => $membersData,
        'dateRange' => $dateRange,
        'selected_project' => $selected_project,
        'selected_members' => $selected_members,
        'select_by' => $select_by,
        'dates' => $formattedDates,
        'dateGroups' => $dateGroups,
        'activity_data' => $activity_data,
        'user' => $user_h,
        'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0,
    ]);
    break;





        case 'show_by_activity':
 
            // $dateRange = '2024-05-01 to 2024-08-03';
            $dates = [];
          if($dateRange)
         {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);


            while ($start->lessThanOrEqualTo($end)) {
                $groupEnd = $start->copy()->addDays(6);
                if ($groupEnd->greaterThan($end)) {
                    $groupEnd = $end;
                }
                $dateRanges[] = $start->format('M d') . ' - ' . $groupEnd->format('M d');
                $start = $groupEnd->copy()->addDay();
            }
        }
        else{

            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);


            while ($start->lessThanOrEqualTo($end)) {
                $groupEnd = $start->copy()->addDays(1);
                if ($groupEnd->greaterThan($end)) {
                    $groupEnd = $end;
                }
                $dateRanges[] = $start->format('M d') . ' - ' . $groupEnd->format('M d');
                $start = $groupEnd->copy()->addDay();
            }
        }
// Prepare formatted dates for table columns
$formattedDates = array_map(function($range) {
    return $range;
}, $dateRanges);

$memberDurations = [];
$projectDetails = [];
// Process activities for detailed data
foreach ($activities as $activity) {
    $startTime = new Carbon($activity->start_time);
    $endTime = new Carbon($activity->end_time);
    $totalSeconds = $startTime->diffInSeconds($endTime);

    $activityDate = Carbon::parse($activity->start_time)->format('d M');
    $dailyDurations[$activityDate] = ($dailyDurations[$activityDate] ?? 0) + $totalSeconds;

    // Group activities by date
    $project = Project::where('project_id', $activity->project_id)->first();
    $project_name = $project ? $project->project_name : 'Unknown Project';
    $member = User::where('id', $activity->member_id)->first();
    $mem_name = $member ? $member->name : 'Unknown Member';

    $activity_data[] = [
        'activity_name' => $activity->title,
        'start_time' => $activity->start_time,
        'end_time' => $activity->end_time,
        'project_id' => $project_name,
        'member_name' => $mem_name,
        'screenshot' => $activity->screenshot, // Assuming you have this field
        'formatted_duration' => formatDurationInSeconds($totalSeconds),
    ];
}
foreach ($projects_data as $project) { 
    // $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);
 $memberIds = is_array($user_h->id) ? $user_h->id : [$user_h->id];

    //activity log start
   
                //activity log end
    foreach ($memberIds as $memberId) {
        if (!isset($memberDurations[$memberId])) {
            $memberDurations[$memberId] = array_fill_keys($formattedDates, 0);
            $projectDetails[$memberId] = [];
        }

        $activities = Activity::where('member_id', $memberId)
            ->where('project_id', $project->project_id)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get();

        foreach ($activities as $activity) {
            $activityStartDate = Carbon::parse($activity->start_time);
            $activityEndDate = Carbon::parse($activity->end_time);
            $durationInSeconds = $activityStartDate->diffInSeconds($activityEndDate);

            // Determine which date range the activity falls into
            foreach ($dateRanges as $range) {
                list($rangeStart, $rangeEnd) = explode(' - ', $range);
                $rangeStart = Carbon::createFromFormat('M d', $rangeStart);
                $rangeEnd = Carbon::createFromFormat('M d', $rangeEnd);

                if ($activityStartDate->between($rangeStart, $rangeEnd)) {
                    $memberDurations[$memberId][$range] += $durationInSeconds;

                    if (!isset($projectDetails[$memberId][$activity->title])) {
                        $projectDetails[$memberId][$activity->title] = [
                            'activity_title' => $activity->title,
                            'daily_durations' => array_fill_keys($formattedDates, 0),
                            'total_duration' => 0,
                        ];
                    }

                    $projectDetails[$memberId][$activity->title]['daily_durations'][$range] += $durationInSeconds;
                    $projectDetails[$memberId][$activity->title]['total_duration'] += $durationInSeconds;
                    break;
                }
            }
        }
    }
}

// Format total durations
foreach ($projectDetails as $memberId => $activities) {
    foreach ($activities as $title => $activity) {
        $totalSeconds = $activity['total_duration'];
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $formattedTotalDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        $projectDetails[$memberId][$title]['total_duration'] = $formattedTotalDuration;
    }
}

$membersData = [];
foreach ($memberDurations as $memberId => $totalDurations) {
    $user = User::find($memberId);
    if ($user) {
        $totalSeconds = array_sum($totalDurations);
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        $projectData = [];
        foreach ($projectDetails[$memberId] as $activityTitle => $activity) {
            $formattedProjectDuration = $activity['total_duration'];
            $projectData[] = [
                'activity_title' => $activityTitle,
                'total_duration' => $formattedProjectDuration,
                'daily_durations' => $activity['daily_durations'],
            ];
        }

        $membersData[] = [
            'user' => $user,
            'image' => $user->profile_image,
            'user_id' => $user->id,
            'totalDuration' => $formattedDuration,
            'projects' => $projectData,
        ];
    }
}


return view('frontend.user-report', [
    'projects' => $projects,
    'members' => $memberNames,
    'chartData' => $chartData,
    'membersData' => $membersData,
    'dateRange' => $dateRange,
    'selected_project' => $selected_project,
    'selected_members' => $selected_members,
    'select_by' => $select_by,
    'dates' => $formattedDates,
    'activity_data'=>$activity_data,
    'user'=>$user_h,
    'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0,
]);


        break;

       default:
        // $dateRange = '2024-05-10 to 2024-08-03';

    
    // Generate the dates array for date groups
    $dates = [];
    if ($dateRange) {
        $dates = $this->getDatesBetween($startDate, $endDate);
    } else {
        $today = Carbon::now();
        $lastWeek = $today->subDays(6);
        
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $lastWeek->copy()->addDays($i)->format('d M');
        }
    }

    // Generate date groups
    $dateGroups = [];
    if ($dateRange) {
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
    } else {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lessThanOrEqualTo($end)) {
            $groupEnd = $start->copy()->addDays(1);
            if ($groupEnd->greaterThan($end)) {
                $groupEnd = $end;
            }
            $dateGroups[] = $start->format('d M') . ' - ' . $groupEnd->format('d M');
            $start = $groupEnd->copy()->addDay();
        }
    }
$activity_data = [];
    // Initialize $activity_data as an array
   // Process activities for detailed data
foreach ($activities as $activity) {
    $startTime = new Carbon($activity->start_time);
    $endTime = new Carbon($activity->end_time);
    $totalSeconds = $startTime->diffInSeconds($endTime);

    $activityDate = Carbon::parse($activity->start_time)->format('d M');
    $dailyDurations[$activityDate] = ($dailyDurations[$activityDate] ?? 0) + $totalSeconds;

    // Group activities by date
    $project = Project::where('project_id', $activity->project_id)->first();
    $project_name = $project ? $project->project_name : 'Unknown Project';
    $member = User::where('id', $activity->member_id)->first();
    $mem_name = $member ? $member->name : 'Unknown Member';

    $activity_data[] = [
        'activity_name' => $activity->title,
        'start_time' => $activity->start_time,
        'end_time' => $activity->end_time,
        'project_id' => $project_name,
        'member_name' => $mem_name,
        'screenshot' => $activity->screenshot, // Assuming you have this field
        'formatted_duration' => formatDurationInSeconds($totalSeconds),
    ];
}
    // Format total duration
    function formatDurationInSeconds($totalSeconds) {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
$dailyDurations =[];
    // Calculate total durations for each 7-day group
    $groupDurations = [];
    foreach ($dateGroups as $dateGroup) {
        list($start, $end) = explode(' - ', $dateGroup);
        $startDateGroup = Carbon::createFromFormat('d M', $start);
        $endDateGroup = Carbon::createFromFormat('d M', $end);

        $groupTotalDuration = 0;
        foreach ($dailyDurations as $date => $duration) {
            $dateCarbon = Carbon::createFromFormat('d M', $date);
            if ($dateCarbon->between($startDateGroup, $endDateGroup)) {
                $groupTotalDuration += $duration;
            }
        }

        $groupDurations[$dateGroup] = $groupTotalDuration;
    }

    // Sum of all group durations
    $sumOfGroupDurations = array_sum($groupDurations);

    // Process user data for each member
    $user_data = [];
    

    // Collect project details
    $projectDetails = [];
    foreach ($projectIds as $projectId) {
        $project = Project::where('project_id', $projectId)->first();
        $pname = $project ? $project->project_name : 'Unknown Project';
        
        $totalSeconds = 0;
        $dailyDurations = [];
        
        foreach ($memberIds as $memberId) {
       
            $durationmember = 0; // Reset the member duration for each member
            
                    $activities_m = Activity::where('member_id', $memberId)
                        ->where('project_id', $project->project_id)
                        ->get();
            
                    foreach ($activities_m as $activity) {
                        list($hours, $minutes, $seconds) = explode(':', $activity->durations);
                    $durationmember += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
        $user = User::find($memberId);
        if ($user) {
            $user_data[$memberId] = [
                'user' => $user,
                'image' => $user->profile_image,
                'totalDuration' => formatDurationInSeconds($durationmember)
            ];
        }
    }
        
        
        
        foreach ($activities as $activity) {
            if ($activity->project_id == $projectId) {
                $startTime = new Carbon($activity->start_time);
                $endTime = new Carbon($activity->end_time);
                $totalSeconds += $startTime->diffInSeconds($endTime);
        
                $activityDate = Carbon::parse($activity->start_time)->format('d M');
                if (!isset($dailyDurations[$activityDate])) {
                    $dailyDurations[$activityDate] = 0;
                }
                $dailyDurations[$activityDate] += $startTime->diffInSeconds($endTime);
            }
        }
        
        $formattedDuration = formatDurationInSeconds($totalSeconds);
        
        // Calculate total durations for each 7-day group
        $groupDurations = [];
        foreach ($dateGroups as $dateGroup) {
            list($start, $end) = explode(' - ', $dateGroup);
            $startDateGroup = Carbon::createFromFormat('d M', $start);
            $endDateGroup = Carbon::createFromFormat('d M', $end);
        
            $groupTotalDuration = 0;
            foreach ($dailyDurations as $date => $duration) {
                $dateCarbon = Carbon::createFromFormat('d M', $date);
                if ($dateCarbon->between($startDateGroup, $endDateGroup)) {
                    $groupTotalDuration += $duration;
                }
            }
        
            $groupDurations[$dateGroup] = $groupTotalDuration;
        }
        
        $projectDetails[] = [
            'id' => $project->id,
            'project_id' => $project->project_id,
            'project_logo' => $project->project_logo,
            'project_name' => $pname,
            'total_duration' => formatDurationInSeconds($totalSeconds),
            'daily_durations' => $dailyDurations,
            'user_data' => $user_data,
            'group_durations' => $groupDurations
        ];
    }


// Return the full view for normal requests
return view('frontend.user-report', [
    'projects' => $projects,
    'chartData' => $chartData,
    'projectDetails' => $projectDetails,
    'dateRange' => $dateRange,
    'selected_project' => $selected_project,
    'selected_members' => $selected_members,
    'select_by' => $select_by,
    'dates' => $dateGroups,
    'activity_data' => $activity_data,
    'user' => $user_h,
    'request_projects' => $users_data['request_projects'] ?? [],
                   'projectCount' => $users_data['projectCount'] ?? 0,
]);
            break;
    }
    


               
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
        private function getDatesBetween($startDate, $endDate) {
            // $dates = [];
            // $start = new Carbon($startDate);
            // $end = new Carbon($endDate);
            // $end = $end->addDay(); // Add one day to include end date in the range
        
            // while ($start < $end) {
            //     $dates[] = $start->format('d M');
            //     $start->addDay();
            // }
        
            // return $dates;

            $dates = [];
    $start = new Carbon($startDate);
    $end = new Carbon($endDate);
    $end = $end->addDay(); // Add one day to include end date in the range

    while ($start < $end) {
        $groupStart = $start->copy();
        $groupEnd = $start->copy()->addDays(6);
        
        if ($groupEnd > $end) {
            $groupEnd = $end->copy()->subDay(); // Adjust end date if it exceeds the actual end date
        }

        $dates[] = $groupStart->format('M d') . ' - ' . $groupEnd->format('M d');
        $start->addDays(7); // Move to the next 7-day group
    }

    return $dates;

        }
    public function SelectDataRedirect(Request $request)
{
    
     return redirect()->route('user-report');
}

     public function getMembers(Request $request)
    {
        $projectId = $request->input('project_id');
        $project = Project::where('project_id', $projectId)->first();

        if ($project && $project->member_id) {
            $memberIds = explode(',', $project->member_id);
            $members = User::whereIn('id', $memberIds)->pluck('name', 'id')->toArray();
            return response()->json($members);
        }

        return response()->json([]);
    }
      
}
