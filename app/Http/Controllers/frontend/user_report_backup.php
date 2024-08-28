<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;
class user_reportcontroller extends Controller
{
    //
    public function index(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');
    
        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if($user)
            {
                $project = project::where('email',$email)->get();
                // $member = $project->member_id;
                
               $projects = Project::where('email', $email)->get(); // Get all projects
               $memberIds = []; // Initialize an empty array to store member IDs
                $projectIds = [];
                foreach ($projects as $project) {
                    if ($project->member_id) {
                        $ids = explode(',', $project->member_id);
                        $memberIds = array_merge($memberIds, $ids);
                         $projectIds[] = $project->project_id;
                    }
                }
                $projectIds = array_unique($projectIds);
                // Remove duplicate IDs
                $memberIds = array_unique($memberIds);
                $users = User::whereIn('id', $memberIds)->get();

                // Map user IDs to their names
                $memberNames = $users->pluck('name', 'id')->toArray();

                $select_project = $request->input('peoject');
                    if($select_project)
                    {
                         $selectedMembers = $request->input('member', []);
                        if($selectedMembers)
                        {
                           
                            // $pro = Project::where('member_id', $project)->get();
                         $project = $request->input('project'); // Note: Fixed typo 'peoject' to 'project'
                        $selectedMembers = $request->input('member', []); // Array of selected member IDs
                        
                        $memberIds = []; // Initialize an empty array to store member IDs
                        $projectIds = [];
                        $projectIds_selected = []; // Initialize array to store selected project IDs
                        
                        // Find the project based on the project name
                        $pro = Project::where('project_name', $project)->get();
                        
                        // Ensure that we have at least one project
                            if ($pro) {
                                $project_id = $pro->first()->project_id;
                        
                                // Find projects with the same project ID
                                $projects = Project::where('project_id', $project_id)->get();
                        
                                // Iterate over projects to collect member IDs and project IDs
                                foreach ($projects as $project) {
                                    if ($project->member_id) {
                                        $ids = explode(',', $project->member_id);
                                        $memberIds = array_merge($memberIds, $ids);
                                        $projectIds_selected[] = $project->project_id;
                                    }
                                }
                                
                                // Remove duplicate IDs
                                $projectIds_selected = array_unique($projectIds_selected);
                                $memberIds = array_unique($memberIds);
                        
                                // Filter activities by the selected project IDs and member IDs
                                $activities = Activity::whereIn('project_id', $projectIds_selected)
                                    ->whereIn('member_id', $selectedMembers) // Use selected members instead of member IDs
                                    ->get();
                        
                                // Calculate mouse and keyboard clicks per member
                                $mouseClicks = $activities->groupBy('member_id')->map(function ($activityGroup) {
                                    return $activityGroup->sum('mouse_click');
                                })->toArray();
                        
                                $keyboardClicks = $activities->groupBy('member_id')->map(function ($activityGroup) {
                                    return $activityGroup->sum('keyboard_click');
                                })->toArray();
    
                            }
                        }
                        else{
                               
                            $project = $request->input('peoject');
                            $memberIds = []; // Initialize an empty array to store member IDs
                            $projectIds = [];
                            $pro = Project::where('project_name', $project)->get();
                       
                             $project_id = $pro->first()->project_id;
                             $projects = Project::where('project_id', $project_id)->get();
                        
                             $selectedMembers = $request->input('member', []);
                                foreach ($projects as $project) {
                                if ($project->member_id) {
                                    $ids = explode(',', $project->member_id);
                                    $memberIds = array_merge($memberIds, $ids);
                                     $projectIds_selected[] = $project->project_id;
                                }
                            $projectIds = array_unique($projectIds);
                            // Remove duplicate IDs
                            $memberIds = array_unique($memberIds);
                           
                            $activities = Activity::whereIn('project_id', $projectIds_selected)
                            ->whereIn('member_id', $memberIds)
                            ->get();
                            $mouseClicks = $activities->groupBy('member_id')->map(function ($activityGroup) {
                            return $activityGroup->sum('mouse_click'); // Replace 'keyboard_clicks' with the actual column name for keyboard clicks
                             })->toArray();
                        
                             $keyboardClicks = $activities->groupBy('member_id')->map(function ($activityGroup) {
                            return $activityGroup->sum('keyboard_click'); // Replace 'keyboard_clicks' with the actual column name for keyboard clicks
                            })->toArray();
                        }
                    }
                    $projectIds = array_unique($projectIds);
                    }
                else{
                  $activities = Activity::whereIn('project_id', $projectIds)
                ->whereIn('member_id', $memberIds)
                ->get();
               
               $mouseClicks = $activities->groupBy('member_id')->map(function ($activityGroup) {
                    return $activityGroup->sum('mouse_click'); // Replace 'keyboard_clicks' with the actual column name for keyboard clicks
                })->toArray();
                
                 $keyboardClicks = $activities->groupBy('member_id')->map(function ($activityGroup) {
                    return $activityGroup->sum('keyboard_click'); // Replace 'keyboard_clicks' with the actual column name for keyboard clicks
                })->toArray();
                
                }
                
                 $selectedMembers = $request->input('member');
                
      
                return view('frontend.user-report', [
                    'project' => $projects,
                    'members' => $memberNames,
                     'keyboardClicks' => $keyboardClicks,
                     'mouseClicks' => $mouseClicks,
                     'select_project'=>$select_project,
                      'members2' => User::whereIn('id', $memberIds)->pluck('name', 'id')->toArray(),
                     'selectedMembers'=>$selectedMembers,
                     
                ]);
           
            }
            else{
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
}


<script>
        // Pass PHP data to JavaScript
        var keyboardClicks = @json(array_values($keyboardClicks));
        var mouseClicks = @json(array_values($mouseClicks));
        var memberNames = @json(array_keys($members2));

        // Debugging: Check if data is correctly passed
        console.log('Keyboard Clicks:', keyboardClicks);
        console.log('Mouse Clicks:', mouseClicks);
        console.log('Member Names:', memberNames);

        // Check if memberNames is correctly set
        if (!Array.isArray(memberNames) || memberNames.length === 0) {
            console.error('No member names found or memberNames is not an array.');
        }

        // Chart options
        var options = {
            series: [{ 
                name: "Mouse Clicks",
                data: mouseClicks
            }, {
                name: "Keyboard Clicks",
                data: keyboardClicks
            }, {
                name: "Duration",
                data: [35, 41, 36] // Sample data
            }],
            chart: {
                foreColor: "#9ba7b2",
                height: 380,
                type: 'bar',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false,
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    gradientToColors: ['#ffd200', '#00c6fb', '#7928ca'],
                    shadeIntensity: 1,
                    type: 'vertical',
                    stops: [0, 100, 100, 100]
                },
            },
            colors: ['#ff6a00', "#005bea", "#ff0080"],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 4,
                    columnWidth: '45%',
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 4,
                colors: ["transparent"]
            },
            grid: {
                show: true,
                borderColor: 'rgba(0, 0, 0, 0.15)',
                strokeDashArray: 4,
            },
            tooltip: {
                theme: "dark",
            },
            xaxis: {
                categories: memberNames, // Set categories for the x-axis
                title: {
                    text: 'Members'
                }
            }
        };

        // Render the chart
        var chart = new ApexCharts(document.querySelector("#costomize-chart1"), options);
        chart.render();
    </script>
 <div id="costomize-chart1"></div>
 
 
 
  <!--@foreach ($members as $memberId => $memberName)-->
            <!--     <p>{{ $memberName }}: {{ $keyboardClicks[$memberId] ?? 0 }} Mouse clicks</p>-->
            <!--@endforeach-->
            <!--Selected Project:{{$select_project}}<br>-->
            <!--Selected Project:<ul>-->
            <!--    @if($selectedMembers)-->
            <!--        @foreach($selectedMembers as $member)-->
            <!--            <li>{{ $member }}</li>-->
            <!--        @endforeach-->
            <!--    @else-->
            <!--        <li>No members selected</li>-->
            <!--    @endif-->








//new file code 
<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;
class user_reportcontroller extends Controller
{
    //
    public function index(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');
    
        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
             if($user)
            {
                $projects = Project::where('email', $email)->get();
                $memberIds = [];
                $projectNames = [];
                $projectIds = [];

                foreach ($projects as $project) {
                    if ($project->member_id) {
                        $ids = explode(',', $project->member_id);
                        $memberIds = array_merge($memberIds, $ids);
                        $projectIds[] = $project->project_id;
                        $projectNames[] = $project->project_name;
                    }
                }

                $projectIds = array_unique($projectIds);
                $memberIds = array_unique($memberIds);
              
                $projectIds = Project::where('member_id', $memberIds)->pluck('project_id')->toArray();
                 $projects_name = $projects->pluck('project_name');
                $users = User::whereIn('id', $memberIds)->get();
                $memberNames = $users->pluck('name', 'id')->toArray();
                
                  $activities = Activity::whereIn('project_id', $projectIds)->get();


// Function to convert HH:MM:SS to total seconds
function durationToSeconds($duration) {
    list($hours, $minutes, $seconds) = explode(':', $duration);
    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

// Assuming $activities and $projects_name are defined and populated

// Calculate total durations for each project in seconds
$durations = $activities->groupBy('project_id')->map(function ($activityGroup) {
    return $activityGroup->sum(function ($activity) {
        return durationToSeconds($activity->durations);
    });
})->toArray();

// Get project names or categories
$categories = $projects_name->toArray();

// Prepare chart data
$chartData = [
    'categories' => array_values($categories), // Project names or categories for x-axis
    'series' => [
        [
            'name' => 'Total Duration',
            'data' => array_values($durations) // Duration data for each project in seconds
        ],
    ],
];

                return view('frontend.user-report', [
                    'projects' => $projects,
                    'members' => $memberNames,
                    'projectIds' => $projectIds,
                    'categories' => array_values($categories),
                    // Project names for chart categories
    'chartData' => $chartData,
                ]);
               

            
                
                
                
            }
            
            else{
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
}

/// final display project and durations 
<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;
class user_reportcontroller extends Controller
{
    public function index(Request $request )
    {
       $email = $request->session()->get('email');
        $type = $request->session()->get('type');
    
        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
             if($user)
            {
                $projects = Project::where('email', $email)->get();
        
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
        function durationToSeconds($duration) {
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

        // Prepare chart data
        $chartData = [
            'categories' => array_values($projects_name), // Project names or categories for x-axis
            'series' => []
        ];

        // Create a series for each member
        foreach ($memberNames as $memberId => $memberName) {
            $memberDurations = [];
            foreach ($projects_name as $projectId => $projectName) {
                $memberDurations[] = isset($durations[$projectId][$memberId]) ? $durations[$projectId][$memberId] : 0;
            }
            $chartData['series'][] = [
                'name' => $memberName,
                'data' => $memberDurations
            ];
        }

        // Return view with data
        return view('frontend.user-report', [
            'projects' => $projects,
            'members' => $memberNames,
            'chartData' => $chartData,
        ]);
        
            }
            
            else{
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
}
// in this code implement when not found duration of any member in any project then not display project name

// this code is with selectiob box
<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;

class user_reportcontroller extends Controller
{
    public function index(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
                $projects = Project::where('email', $email)->get();

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

                // Return view with data
                return view('frontend.user-report', [
                    'projects' => $projects,
                    'members' => $memberNames,
                    'chartData' => $chartData,
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

// backup of slected projects 
<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
use App\Models\Activity;

class user_reportcontroller extends Controller
{
    public function index(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
                // $projects = Project::where('email', $email)->get();
                $selectedProjectId = $request->project_id; 
                $projectsQuery = Project::where('email', $email);

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

                // Return view with data
                return view('frontend.user-report', [
                    'projects' => $projects,
                    'members' => $memberNames,
                    'chartData' => $chartData,
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
//end backup of selected member
@foreach ($project_detail  as $row)
                              <tr>
                                <td>
                                   
                                  <a href="{{url('project-details')}}/{{$row['id']}}">#{{$row['project_id']}}</a>
                                    <button class="btn btn-sm btn-primary expand-btn" data-target="#memberDetails{{$row['id']}}">Expand</button>
                                <button class="abstract_link base_table_cell-toggle is-button is-icon_only is-border_radius_soft is-button_size_icon is-style_link" title="Collapse row" type="button" value="projects-1123485"><svg class="icon abstract_link-icon base_table_cell-toggle-icon icon-minus" width="24px" height="24px"><use xlink:href="#icon-minus"></use></svg></button>
                                </td>
                                <td>
                                  <a class="d-flex align-items-center gap-3" href="javascript:;">
                                    <div class="customer-pic">
                                      @if($row['project_logo'] =="")
                                      <img src="{{url('frontend/assets/images/avatars/01.png')}}" class="rounded-circle" width="40"
                                        height="40" alt="" />
                                        @else
                                         <img src="images/{{$row['project_logo']}}" class="rounded-circle" width="40"
                                        height="40" alt="" />
                                        @endif
                                    </div>
                                    <p class="mb-0 customer-name fw-bold">{{$row['project_name']}}
                                    </p>
                                  </a>
                                </td>
                                <td>{{ $row['total_duration'] }}</td>
                              </tr>
                                        <tr id="memberDetails{{$row['id']}}" class="collapse">
                                          
                                            <td>
                                                  <a class="d-flex align-items-center gap-3" href="javascript:;">
                                                    <div class="customer-pic">
                                                      @if($row['project_logo'] =="")
                                                      <img src="{{url('frontend/assets/images/avatars/01.png')}}" class="rounded-circle" width="40"
                                                        height="40" alt="" />
                                                        @else
                                                         <img src="images/{{$row['project_logo']}}" class="rounded-circle" width="40"
                                                        height="40" alt="" />
                                                        @endif
                                                    </div>
                                                    <p class="mb-0 customer-name fw-bold">{{$row['project_name']}}
                                                    </p>
                                                  </a>
                                            </td>
                                             <td>{{ $row['total_duration'] }}</td>
                                               
                                           
                                        </tr>
                              @endforeach
                              
                              
                              
                              
                              <!--                            <tbody>-->
<!--                                @foreach ($project_detail  as $row)-->
<!--                              <tr>-->
<!--                                <td>-->
                                   
<!--                                  <a href="{{url('project-details')}}/{{$row['id']}}">#{{$row['project_id']}}</a>-->
<!--                                     <button class="btn btn-sm btn-primary expand-btn" data-target="#memberDetails{{$row['project_id']}}">Expand</button>  </td>-->
<!--                                <td>-->
<!--                                  <a class="d-flex align-items-center gap-3" href="javascript:;">-->
<!--                                    <div class="customer-pic">-->
<!--                                      @if($row['project_logo'] =="")-->
<!--                                      <img src="{{url('frontend/assets/images/avatars/01.png')}}" class="rounded-circle" width="40"-->
<!--                                        height="40" alt="" />-->
<!--                                        @else-->
<!--                                         <img src="images/{{$row['project_logo']}}" class="rounded-circle" width="40"-->
<!--                                        height="40" alt="" />-->
<!--                                        @endif-->
<!--                                    </div>-->
<!--                                    <p class="mb-0 customer-name fw-bold">{{$row['project_name']}}-->
<!--                                    </p>-->
<!--                                  </a>-->
<!--                                </td>-->
<!--                                <td>{{ $row['total_duration'] }}</td>-->
<!--                              </tr>-->
                              
         
<!--         @foreach ($project_detail as $row)-->
<!--    @forelse ($row['members'] as $memberId => $memberName)-->
<!--        <tr id="memberDetails{{ $row['project_id'] }}" class="collapse">-->
<!--            <td>-->
<!--                <a class="d-flex align-items-center gap-3" href="javascript:;">-->
<!--                    <div class="customer-pic">-->
<!--                        @if(empty($row['project_logo']))-->
<!--                            <img src="{{ url('frontend/assets/images/avatars/01.png') }}" class="rounded-circle" width="40" height="40" alt="" />-->
<!--                        @else-->
<!--                            <img src="{{ url('images/' . $row['project_logo']) }}" class="rounded-circle" width="40" height="40" alt="" />-->
<!--                        @endif-->
<!--                    </div>-->
<!--                    <p class="mb-0 customer-name fw-bold">{{ $memberName }}</p>-->
<!--                </a>-->
<!--            </td>-->
<!--            <td>-->
                <!-- Display member's duration or other details here -->
<!--                {{ $durations[$row['project_id']][$memberId] ?? '0' }}-->
<!--            </td>-->
<!--        </tr>-->
<!--    @empty-->
<!--        <tr>-->
<!--            <td colspan="2">No members found.</td>-->
<!--        </tr>-->
<!--    @endforelse-->
<!--@endforeach-->



<!--                              @endforeach-->
<!--                            </tbody>-->





<!--                            <tbody>-->
<!--                                @foreach ($project_detail  as $row)-->
<!--                              <tr>-->
<!--                                <td>-->
                                   
<!--                                  <a href="{{url('project-details')}}/{{$row['id']}}">#{{$row['project_id']}}</a>-->
<!--                                     <button class="btn btn-sm btn-primary expand-btn" data-target="#memberDetails{{$row['project_id']}}">Expand</button>  </td>-->
<!--                                <td>-->
<!--                                  <a class="d-flex align-items-center gap-3" href="javascript:;">-->
<!--                                    <div class="customer-pic">-->
<!--                                      @if($row['project_logo'] =="")-->
<!--                                      <img src="{{url('frontend/assets/images/avatars/01.png')}}" class="rounded-circle" width="40"-->
<!--                                        height="40" alt="" />-->
<!--                                        @else-->
<!--                                         <img src="images/{{$row['project_logo']}}" class="rounded-circle" width="40"-->
<!--                                        height="40" alt="" />-->
<!--                                        @endif-->
<!--                                    </div>-->
<!--                                    <p class="mb-0 customer-name fw-bold">{{$row['project_name']}}-->
<!--                                    </p>-->
<!--                                  </a>-->
<!--                                </td>-->
<!--                                <td>{{ $row['total_duration'] }}</td>-->
<!--                              </tr>-->
                              
         
<!--         @foreach ($project_detail as $row)-->
<!--    @forelse ($row['members'] as $memberId => $memberName)-->
<!--        <tr id="memberDetails{{ $row['project_id'] }}" class="collapse">-->
<!--            <td>-->
<!--                <a class="d-flex align-items-center gap-3" href="javascript:;">-->
<!--                    <div class="customer-pic">-->
<!--                        @if(empty($row['project_logo']))-->
<!--                            <img src="{{ url('frontend/assets/images/avatars/01.png') }}" class="rounded-circle" width="40" height="40" alt="" />-->
<!--                        @else-->
<!--                            <img src="{{ url('images/' . $row['project_logo']) }}" class="rounded-circle" width="40" height="40" alt="" />-->
<!--                        @endif-->
<!--                    </div>-->
<!--                    <p class="mb-0 customer-name fw-bold">{{ $memberName }}</p>-->
<!--                </a>-->
<!--            </td>-->
<!--            <td>-->
                <!-- Display member's duration or other details here -->
<!--                {{ $durations[$row['project_id']][$memberId] ?? '0' }}-->
<!--            </td>-->
<!--        </tr>-->
<!--    @empty-->
<!--        <tr>-->
<!--            <td colspan="2">No members found.</td>-->
<!--        </tr>-->
<!--    @endforelse-->
<!--@endforeach-->



<!--                              @endforeach-->
<!--                            </tbody>-->

//final code 
// <tbody>
//     @foreach ($projectDetails as $row)
//         <tr>
//             <td>
//                 <a href="{{ url('project-details') }}/{{ $row['id'] }}">#{{ $row['project_id'] }}</a>
//                 <!-- Button to expand/collapse member details -->
//                 <button class="expand-btn" data-target="#memberDetails{{ $row['project_id'] }}">
//                     <i class='fas fa-plus' style='font-size:24px;color:red'></i>
//                 </button>
//             </td>
//             <td>
//                 <a class="d-flex align-items-center gap-3" href="javascript:;">
//                     <div class="customer-pic">
//                         @if(empty($row['project_logo']))
//                             <img src="{{ url('frontend/assets/images/avatars/01.png') }}" class="rounded-circle" width="40" height="40" alt="" />
//                         @else
//                             <img src="{{ url('images/' . $row['project_logo']) }}" class="rounded-circle" width="40" height="40" alt="" />
//                         @endif
//                     </div>
//                     <p class="mb-0 customer-name fw-bold">{{ $row['project_name'] }}</p>
//                 </a>
//             </td>
//             <td>{{ $row['total_duration'] }}</td>
//         </tr>

//         <!-- Expandable row for member details -->
//         <tr id="memberDetails{{ $row['project_id'] }}" class="collapse">
//             <td colspan="3">
//                 <table class="table">
//                 @foreach($row['user_data'] as $memberId => $data)
//                         <tr>
//                             <td>
//                                 <a class="d-flex align-items-center gap-3" href="javascript:;">
//                                     <div class="customer-pic">
//                                         @if(empty($data['image']))
//                                             <img src="{{ url('frontend/assets/images/avatars/01.png') }}" class="rounded-circle" width="40" height="40" alt="" />
//                                         @else
//                                             <img src="{{ $data['image']}}" class="rounded-circle" width="40" height="40" alt="" />
//                                         @endif
//                                     </div>
//                                     <p class="mb-0 customer-name fw-bold">{{ $data['user']->name }}</p>
//                                 </a>
//                             </td>
//                             <td>
//                                 {{ $data['totalDuration'] }}
//                             </td>
//                         </tr>
//                     @endforeach
//                 </table>
//             </td>
//         </tr>
//     @endforeach
// </tbody>

