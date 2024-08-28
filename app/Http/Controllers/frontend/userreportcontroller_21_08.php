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


                $projectsQuery = Project::where('email', $email);
    
    if ($dateRange) {
                $dates = explode(' to ', $dateRange);

                if (count($dates) === 2) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate = Carbon::parse($dates[1])->endOfDay();

                    $projectsQuery->whereBetween('start_date', [$startDate, $endDate]);
                }
            }
                // Filter by selected project if provided
                if ($selectedProjectId) {
                    $projectsQuery->where('project_id', $selectedProjectId);
                }
  
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

                // Function to convert HH:MM:SS to total seconds
                function durationToSeconds($duration)
                {
                    list($hours, $minutes, $seconds) = explode(':', $duration);
                    return ($hours * 3600) + ($minutes * 60) + $seconds;
                }

                // Calculate total durations for each member in each project in seconds
                $durations = $activities->groupBy('project_id')->map(function ($activityGroup) {
                    return $activityGroup->groupBy('member_id')->map(function ($memberGroup) {
                        return $memberGroup->sum(function ($activity) {
                            return durationToSeconds($activity->durations);
                        });
                    });
                })->toArray();

                // Filter projects with no durations
                $filteredProjects = array_filter($projects_name, function ($projectId) use ($durations) {
                    return isset($durations[$projectId]) && !empty(array_filter($durations[$projectId]));
                }, ARRAY_FILTER_USE_KEY);

                // Prepare chart data
                $chartData = [
                    'categories' => array_values($filteredProjects), // Project names or categories for x-axis
                    'series' => []
                ];

if($selectedMembers){
     foreach ($selectedMembers as $memberId) {
                if (isset($memberNames[$memberId])) {
                    $memberDurations = [];
                    foreach ($filteredProjects as $projectId => $projectName) {
                        $memberDurations[] = isset($durations[$projectId][$memberId]) ? $durations[$projectId][$memberId] : 0;
                    }
                    $chartData['series'][] = [
                        'name' => $memberNames[$memberId],
                        'data' => $memberDurations
                    ];
                }
            }
}
else{
     // Create a series for each member
                foreach ($memberNames as $memberId => $memberName) {
                    $memberDurations = [];
                    foreach ($filteredProjects as $projectId => $projectName) {
                        $memberDurations[] = isset($durations[$projectId][$memberId]) ? $durations[$projectId][$memberId] : 0;
                    }
                    $chartData['series'][] = [
                        'name' => $memberName,
                        'data' => $memberDurations
                    ];
                }
}
    $projectssql = project::where('email',$email);

    if ($dateRange) {
                $dates = explode(' to ', $dateRange);

                if (count($dates) === 2) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate = Carbon::parse($dates[1])->endOfDay();

                    $projectssql->whereBetween('start_date', [$startDate, $endDate]);
                }
            }
$projects_data = $projectssql->get();

$select_by = $request->input('select_by');

    switch ($select_by) {
        case 'show_by_project':
        // $dateRange = '2024-05-10 to 2024-08-03';

                        if ($dateRange) {
                            $dates = explode(' to ', $dateRange);
                
                            if (count($dates) === 2) {
                                $startDate = Carbon::parse($dates[0])->startOfDay();
                                $endDate = Carbon::parse($dates[1])->endOfDay();
                            }
                        }   
                        else{
                            $today = Carbon::now();
                                $startDate = $today->copy()->subDays(7);
                                $endDate = $today;
                        }   
                        // $startDate = '2024-05-01';
                        // $endDate = '2024-08-01';

                        // Generate the dates array
                        $dates = $this->getDatesBetween($startDate, $endDate);
                        
                        // Existing logic for handling date ranges for the past week
                        
                        $today = Carbon::now();
                        $lastWeek = $today->subDays(6);
                        
                        $dates = [];
                        for ($i = 0; $i < 7; $i++) {
                            $dates[] = $lastWeek->copy()->addDays($i)->format('d M');
                        }
                        
                        function formatDurationInSeconds($totalSeconds) {
                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        }
                        
                        // Generate date groups
                        $dateGroups = [];
                    if($dateRange){
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
                    }
                    else{
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

                        $projectDetails = [];
                        $activity_data = [];
                        foreach ($projects_data as $project) {
                            $activities = Activity::where('project_id', $project->project_id)->get();
                            $totalSeconds = 0;
                            $dailyDurations = [];
                        
                        

                            foreach ($activities as $activity) {
                                $startTime = new Carbon($activity->start_time);
                                $endTime = new Carbon($activity->end_time);
                                $totalSeconds += $startTime->diffInSeconds($endTime);
                        
                                $activityDate = Carbon::parse($activity->start_time)->format('d M');
                                if (!isset($dailyDurations[$activityDate])) {
                                    $dailyDurations[$activityDate] = 0;
                                }
                                $dailyDurations[$activityDate] += $startTime->diffInSeconds($endTime);

                                    //activity log start
                                    $project = Project::where('project_id', $activity->project_id)->first(); // Use first() to get a single project

                                    // Handle case when project is not found
                                    $project_name = $project ? $project->project_name : 'Unknown Project';
                                    $member = User::where('id', $activity->member_id)->first();
                                    $mem_name = $member->name;
                                    $activity_data[] = [
                                        'activity_name' => $activity->title,
                                        'start_time' => $activity->start_time,
                                        'end_time' => $activity->end_time,
                                        'project_id' => $project_name,
                                        'member_name'=>$mem_name,
                                        'screenshot' => $activity->screenshot, // Assuming you have this field
                                        'mouse_click'=>$activity->mouse_click,
                                    ];
                            
                                    //activity log end
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
                        
                            // Sum of all group durations
                            $sumOfGroupDurations = array_sum($groupDurations);
                        
                            $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);
                            $user_data = [];
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
                                        'totalDuration' => formatDurationInSeconds($durationmember), // Use durationmember here
                                        'activity_data' =>$activity_data,
                                        // 'mouse_click'=>$activity->mouse_click,
                                    ];
                                }
                            }
                        
                            $projectDetails[] = [
                                'id' => $project->id,
                                'project_id' => $project->project_id,
                                'project_logo' => $project->project_logo,
                                'project_name' => $project->project_name,
                                'total_duration' => formatDurationInSeconds($sumOfGroupDurations), // Use sum of group durations here
                                'daily_durations' => $dailyDurations,
                                'user_data' => $user_data,
                                'group_durations' => $groupDurations // Add group durations here
                            ];
                        }
                    
                        return view('frontend.user-report', [
                            'projects' => $projects,
                            'members' => $memberNames,
                            'chartData' => $chartData,
                            'projectDetails' => $projectDetails,
                            'dateRange' => $dateRange,
                            'selected_project' => $selected_project,
                            'selected_members' => $selected_members,
                            'select_by' => $select_by,
                            'dates' => $dateGroups, // Use dateGroups here
                            'activity_data' =>$activity_data,
                            'user'=>$user_h,
                        ]);
                        
        break;

        case 'show_by_member':
        // $dateRange = '2024-05-10 to 2024-08-03';
            
            // Initialize today
        $today = Carbon::now();

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);

            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
            }
        } else {
            $startDate = $today->copy()->subDays(7)->startOfDay();
            $endDate = $today->endOfDay();
        }

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
        $memberDurations = [];
        $groupDurations = [];
        $projectDetails = [];
$activity_data = [];
        foreach ($projects_data as $project) {
            $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);

            $activities = Activity::where('project_id', $project->project_id)->get();
            foreach ($activities as $activity) {
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
                    'screenshot' => $activity->screenshot,
                    'mouse_click' => $activity->mouse_click,
                ];
            }

            foreach ($memberIds as $memberId) {
                if (!isset($memberDurations[$memberId])) {
                    $memberDurations[$memberId] = array_fill_keys($formattedDates, 0);
                    $groupDurations[$memberId] = array_fill_keys($dateGroups, 0);
                    $projectDetails[$memberId] = [];
                }

                $activities = Activity::where('member_id', $memberId)
                    ->where('project_id', $project->project_id)
                    ->whereBetween('start_time', [$startDate, $endDate])
                    ->get();

                foreach ($activities as $activity) {
                    $startTime = new Carbon($activity->start_time);
                    $endTime = new Carbon($activity->end_time);
                    $durationInSeconds = $startTime->diffInSeconds($endTime);

                    $activityDate = $startTime->format('d M');
                    if (isset($memberDurations[$memberId][$activityDate])) {
                        $memberDurations[$memberId][$activityDate] += $durationInSeconds;
                    }

                    foreach ($dateGroups as $group) {
                        list($groupStart, $groupEnd) = explode(' - ', $group);
                        $groupStartDate = Carbon::createFromFormat('d M', $groupStart);
                        $groupEndDate = Carbon::createFromFormat('d M', $groupEnd);

                        if ($startTime->between($groupStartDate, $groupEndDate)) {
                            $groupDurations[$memberId][$group] += $durationInSeconds;
                        }
                    }

                    $projectTotalSeconds = isset($projectDetails[$memberId][$project->project_id]['total_duration'])
                        ? $projectDetails[$memberId][$project->project_id]['total_duration']
                        : 0;

                    // Ensure numeric value for addition
                    $projectTotalSeconds = is_numeric($projectTotalSeconds) ? $projectTotalSeconds : 0;
            $projectTotalSeconds += $durationInSeconds;

            $projectHours = floor($projectTotalSeconds / 3600);
            $projectMinutes = floor(($projectTotalSeconds % 3600) / 60);
            $projectSeconds = $projectTotalSeconds % 60;
            $formattedProjectDuration = sprintf('%02d:%02d:%02d', $projectHours, $projectMinutes, $projectSeconds);

            $projectDetails[$memberId][$project->project_id] = [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'project_logo' => $project->project_logo,
                'total_duration' => $formattedProjectDuration,
                'Activity' => $activity_data,
            ];
        }
        }
        }

        $membersData = [];
        foreach ($memberDurations as $memberId => $dailyDurations) {
        $user = User::find($memberId);
        if ($user) {
            $totalSeconds = array_sum($dailyDurations);
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        $membersData[] = [
            'user' => $user,
            'image' => $user->profile_image,
            'user_id' => $user->id,
            'totalDuration' => $formattedDuration,
            'daily_durations' => $dailyDurations,
            'group_durations' => isset($groupDurations[$memberId]) ? $groupDurations[$memberId] : [],
            'projects' => isset($projectDetails[$memberId]) ? $projectDetails[$memberId] : [],
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
        'dateGroups' => $dateGroups,
        'activity_data' => $activity_data,
        'user'=>$user_h,
     ]);


        break;

        case 'show_by_activity':
        $dateRange = '2024-05-01 to 2024-08-03';

            if ($dateRange) {
                $dates = explode(' to ', $dateRange);

                if (count($dates) === 2) {
                    $startDate = Carbon::parse($dates[0])->startOfDay();
                    $endDate = Carbon::parse($dates[1])->endOfDay();
                }
            } else {
                $today = Carbon::now();
                $startDate = $today->copy()->subDays(7)->startOfDay();
                $endDate = $today->endOfDay();
            }
            // $startDate = '2024-05-02';
            //                 $endDate = '2024-08-02';

            // Generate the dates array
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
$activity_data = [];
foreach ($projects_data as $project) { 
    $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);

    //activity log start
    $activities = Activity::where('project_id', $project->project_id)->get();
    foreach ($activities as $activity) {
            
            $project = Project::where('project_id', $activity->project_id)->first(); // Use first() to get a single project

            // Handle case when project is not found
            $project_name = $project ? $project->project_name : 'Unknown Project';
            $member = User::where('id', $activity->member_id)->first();
            $mem_name = $member->name;
            $activity_data[] = [
                'activity_name' => $activity->title,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'project_id' => $project_name,
                'member_name'=>$mem_name,
                'screenshot' => $activity->screenshot, // Assuming you have this field
            ];
    
         }
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
]);

        break;

        default:
        // $dateRange = '2024-05-10 to 2024-08-03';

        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
        
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
            }
        } else {
            $today = Carbon::now();
            $startDate = $today->copy()->subDays(6);
            $endDate = $today;
        }
        
        // Generate the dates array
        $dates = $this->getDatesBetween($startDate, $endDate);
        
        // Existing logic for handling date ranges for the past week
        $today = Carbon::now();
        $lastWeek = $today->subDays(6);
        
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $lastWeek->copy()->addDays($i)->format('d M');
        }
        
        function formatDurationInSeconds($totalSeconds) {
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
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
        
        $projectDetails = [];
        
        // Initialize $activity_data as an array
        $activity_data = [];
        
        foreach ($projects_data as $project) {
            $activities = Activity::where('project_id', $project->project_id)->get();
            $totalSeconds = 0;
            $dailyDurations = [];
        
            foreach ($activities as $activity) {
                $startTime = new Carbon($activity->start_time);
                $endTime = new Carbon($activity->end_time);
                $totalSeconds += $startTime->diffInSeconds($endTime);
        
                $activityDate = Carbon::parse($activity->start_time)->format('d M');
                if (!isset($dailyDurations[$activityDate])) {
                    $dailyDurations[$activityDate] = 0;
                }
                $dailyDurations[$activityDate] += $startTime->diffInSeconds($endTime);
        
                // Group activities by date
                $project = Project::where('project_id', $activity->project_id)->first(); // Use first() to get a single project
        
                // Handle case when project is not found
                $project_name = $project ? $project->project_name : 'Unknown Project';
                $member = User::where('id', $activity->member_id)->first();
                $mem_name = $member->name;
        
                $activity_data[] = [
                    'activity_name' => $activity->title,
                    'start_time' => $activity->start_time,
                    'end_time' => $activity->end_time,
                    'project_id' => $project_name,
                    'member_name' => $mem_name,
                    'screenshot' => $activity->screenshot, // Assuming you have this field
                ];
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
        
            // Sum of all group durations
            $sumOfGroupDurations = array_sum($groupDurations);
        
            $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);
            $user_data = [];
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
                        'totalDuration' => formatDurationInSeconds($durationmember) // Use durationmember here
                    ];
                }
            }
        
            $projectDetails[] = [
                'id' => $project->id,
                'project_id' => $project->project_id,
                'project_logo' => $project->project_logo,
                'project_name' => $project->project_name,
                'total_duration' => formatDurationInSeconds($sumOfGroupDurations), // Use sum of group durations here
                'daily_durations' => $dailyDurations,
                'user_data' => $user_data,
                'group_durations' => $groupDurations // Add group durations here
            ];
        }
        
        // Return the full view for normal requests
        return view('frontend.user-report', [
            'projects' => $projects,
            'members' => $memberNames,
            'chartData' => $chartData,
            'projectDetails' => $projectDetails,
            'dateRange' => $dateRange,
            'selected_project' => $selected_project,
            'selected_members' => $selected_members,
            'select_by' => $select_by,
            'dates' => $dateGroups,
            'activity_data' => $activity_data,
            'user'=>$user_h,
        ]);
        
            break;
    }
    


                // Return view with data
                // return view('frontend.user-report', [
                //     'projects' => $projects,
                //     'members' => $memberNames,
                //     'chartData' => $chartData,
                //     'projectDetails'=>$projectDetails,
                //     'dateRange'=>$dateRange,
                //     'selected_project'=>$selected_project,
                //   'selected_members'=> $selected_members,
                //   'select_by' =>$select_by
                // ]);
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
