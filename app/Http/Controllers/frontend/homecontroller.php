<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Activity;
use Carbon\Carbon;
use App\Models\project;
class HomeController extends Controller
{
   public function index(Request $request)
{
    $headerController = new headerController();
    $users_data = $headerController->header($request);
        

    $type = $request->session()->get('type');
    $duration = 0;
    $totalMouseClicks = 0;
    $previousDuration = 0;
    $previousMouseClicks = 0;
    $totalKeyboardHit = 0;
    $previousKeyboardHit = 0;

    $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if ($email) {
            $status_type = 'complete';
            $user = User::where('status_type', $status_type)->where('email', $email)->where('type', $type)->first();
            if ($user) {
            $userId = $user->id;
            // Determine the date range for the charts
            $dateRange = $request->input('date_range');
            
             
            $startDate = $dateRange ? Carbon::parse(explode(' to ', $dateRange)[0])->startOfDay() : Carbon::now()->subDays(7)->startOfDay();
            $endDate = $dateRange ? Carbon::parse(explode(' to ', $dateRange)[1])->endOfDay() : Carbon::now()->endOfDay();

            // Get data for the last 7 days for Chart 2 and Chart 3
            $startDateForChart2 = Carbon::now()->subDays(7)->startOfDay();
            $endDateForChart2 = Carbon::now()->endOfDay();

            // Fetch data based on user ID and selected or default date range
            $filteredData = Activity::where('member_id', $userId)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();
              
            $previousData = Activity::where('member_id', $userId)
                ->where('start_time', '<', $startDate)
                ->get();

                
            // Fetch data for the last 7 days for Chart 2
            $filteredDataForChart2 = Activity::where('member_id', $userId)
                ->whereBetween('start_time', [$startDateForChart2, $endDateForChart2])
                ->get();

            // Fetch data for Chart 3 using selected or default date range
            $dataForChart3 = Activity::where('member_id', $userId)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();
               
                $duration = 0;
                $totalMouseClicks = 0;
                $totalKeyboardHit = 0;
                $softwareUsageData = [];
                $softwareNames = []; // Initialize the variable before using it
                
                foreach ($filteredData as $activity) {
                    $startTime = Carbon::parse($activity->start_time);
                    $endTime = Carbon::parse($activity->end_time);
                    $duration += $endTime->diffInSeconds($startTime);
                    $totalMouseClicks += $activity->mouse_click;
                    $totalKeyboardHit += $activity->keyboard_click; 
                
                    // Aggregate software usage
                    $softwareUsage = json_decode($activity->software_use_name, true);
                
                    if (is_array($softwareUsage)) {
                        foreach ($softwareUsage as $minutes => $name) {
                            $minutes = (int) $minutes; // Ensure minutes is an integer
                
                            // Aggregate total minutes for each software
                            if (isset($softwareUsageData[$name])) {
                                $softwareUsageData[$name] += $minutes;
                            } else {
                                $softwareUsageData[$name] = $minutes;
                                $softwareNames[] = $name; // Store the software name
                            }
                        }
                    }
                }
                
                $uniqueSoftwareNames = array_unique($softwareNames);
                $softwareData = [];
                
                foreach ($uniqueSoftwareNames as $name) {
                    $softwareData[] = isset($softwareUsageData[$name]) ? $softwareUsageData[$name] : 0;
                }
                
        
            $chartDataSoftwareUsage = [
                'labels' => $uniqueSoftwareNames, // Unique software names
                'series' => [[
                    'name' => 'Time Spent (minutes)',
                    'data' => $softwareData // Total minutes spent on each software
                ]]
            ];

            $totalSoftwareTime = array_sum($softwareData);
             // Initialize array to store percentage data
            $softwarePercentageData = [];
            
            foreach ($uniqueSoftwareNames as $index => $name) {
                $softwareTime = isset($softwareUsageData[$name]) ? $softwareUsageData[$name] : 0;
                $percentage = $totalSoftwareTime > 0 ? ($softwareTime / $totalSoftwareTime) * 100 : 0;
                $softwarePercentageData[$name] = round($percentage, 2); // Round to 2 decimal places
            }
           

            foreach ($previousData as $activity) {
                $startTime = Carbon::parse($activity->start_time);
                $endTime = Carbon::parse($activity->end_time);
                $previousDuration += $endTime->diffInSeconds($startTime);
                $previousMouseClicks += $activity->mouse_click;
                $previousKeyboardHit += $activity->keyboard_click;

               
            }

            $percentageDifference = $previousMouseClicks > 0
                ? (($totalMouseClicks - $previousMouseClicks) / $previousMouseClicks) * 100
                : 0;
            $percentageDifferenceDuraion = $previousDuration > 0
                ? (($duration - $previousDuration) / $previousDuration) * 100
                : 0;
            // Cap percentageDifference at ±100%
            if ($percentageDifference > 100) {
                $percentageDifference = 100;
            } elseif ($percentageDifference < -100) {
                $percentageDifference = -100;
            }
            if ($percentageDifferenceDuraion > 100) {
                $percentageDifferenceDuraion = 100;
            } elseif ($percentageDifferenceDuraion < -100) {
                $percentageDifferenceDuraion = -100;
            }
            $durationDifference = $previousDuration - $duration;
            $differenceIndicator = $durationDifference >= 0 ? '-' : '+';

            $totalDuration = $filteredData->count() * 86400; // Assuming 24 hours per activity
            $percentageClicks = $totalDuration > 0 ? ($totalMouseClicks / $totalDuration) * 100 : 0;

            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $seconds = $duration % 60;
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            // Calculate today's progress
            $todayStart = Carbon::now()->startOfDay();
            $todayEnd = Carbon::now()->endOfDay();
            $todayActivities = Activity::where('member_id', $userId)
                ->whereBetween('start_time', [$todayStart, $todayEnd])
                ->get();

            
            $todayDuration = 0;
            $KeyboardHitToday =0;
            $softwareUsageData = [];
            $mouseClicksToday = 0; // Added variable to hold today's mouse clicks
            foreach ($todayActivities as $activity) {
                $startTime = Carbon::parse($activity->start_time);
                $endTime = Carbon::parse($activity->end_time);
                $todayDuration += $endTime->diffInSeconds($startTime);
                $mouseClicksToday += $activity->mouse_click; // Calculate today's mouse clicks
                $KeyboardHitToday += $activity->keyboard_click;


            }
            // $softwareUsageData = [];
            // $softwareNames = []; // To store the mapping from values to names
            
            // // Fetch activities
            // $activities = Activity::where('member_id', $userId)->get();
            
            // foreach ($activities as $activity) {
            //     $softwareUsage = json_decode($activity->software_use_name, true);
                
            //     if (is_array($softwareUsage)) {
            //         foreach ($softwareUsage as $value => $name) {
            //             $minutes = (int) $value; // Use value as minutes
            //             $softwareNames[$minutes] = $name; // Store mapping
            
            //             if (isset($softwareUsageData[$minutes])) {
            //                 $softwareUsageData[$minutes] += $minutes;
            //             } else {
            //                 $softwareUsageData[$minutes] = $minutes;
            //             }
            //         }
            //     } else {
            //         error_log("Invalid JSON data for activity ID: {$activity->id}");
            //     }
            // }
            
            // // Prepare data for the chart
            // $chartDataSoftwareUsage = [
            //     'labels' => array_values($softwareNames),
            //     'series' => [[
            //         'name' => 'Time Spent (minutes)',
            //         'data' => array_values($softwareUsageData)
            //     ]]
            // ];
            // Prepare data for the software usage chart
           
            // dd($chartDataSoftwareUsage);

            
            $hoursToday = floor($todayDuration / 3600);
            $minutesToday = floor(($todayDuration % 3600) / 60);
            $secondsToday = $todayDuration % 60;
            $formattedTodayDuration = sprintf('%02d:%02d:%02d', $hoursToday,$minutesToday, $secondsToday);

            // Data for Chart 1 (Today's progress)
            $chartData1 = [
                'series' => [ $percentageDifference ],
                'formattedTodayDuration' => $formattedTodayDuration,
            ];

            $startDateForChart2 = Carbon::now()->subDays(7)->startOfDay();
    $endDateForChart2 = Carbon::now()->endOfDay();
    
    $dateRange = $request->input('date_range');
    if ($dateRange) {
        $startDateForChart2 = Carbon::parse(explode(' to ', $dateRange)[0])->startOfDay();
        $endDateForChart2 = Carbon::parse(explode(' to ', $dateRange)[1])->endOfDay();
    }

    // Fetch data for Chart 2
    $filteredDataForChart2 = Activity::where('member_id', $userId)
        ->whereBetween('start_time', [$startDateForChart2, $endDateForChart2])
        ->get();

    // Prepare data for Chart 2
    $chartData2 = [
        'series' => [
            [
                'name' => 'Duration (seconds)',
                'data' => $filteredDataForChart2->map(function($activity) {
                    $startTime = Carbon::parse($activity->start_time);
                    $endTime = Carbon::parse($activity->end_time);
                    return $endTime->diffInSeconds($startTime);
                })->toArray()
            ]
        ],
        'xaxis' => [
            'categories' => $filteredDataForChart2->map(function($activity) {
                return Carbon::parse($activity->start_time)->format('Y-m-d');
            })->toArray()
        ]
    ];

            $chartData3 = [
                'series' => [
                    [
                        'name' => 'Mouse Clicks',
                        'type' => 'bar',
                        'data' => $dataForChart3->map(function($activity) {
                            return $activity->mouse_click;
                        })->toArray()
                    ],
                    [
                        'name' => 'Duration',
                        'type' => 'bar',
                        'data' => $dataForChart3->map(function($activity) {
                            return $activity->keyboard_click;
                        })->toArray()
                    ]
                ],
                'xaxis' => [
                    'categories' => $dataForChart3->map(function($activity) {
                        return Carbon::parse($activity->start_time)->format('Y-m-d');
                    })->toArray()
                ]
            ];



            // $softwareUsageData = [];
            // $softwareUsage = $user->software_use_name; // Assuming you have this data in the user model
        
            // if ($softwareUsage) {
            //     $softwareUsageArray = json_decode($softwareUsage, true);
            //     foreach ($softwareUsageArray as $minutes => $software) {
            //         $softwareUsageData[$software] = $minutes;
            //     }
            // }
        
           
            
            $dateRangeSelected = $request->input('date_range') ? true : false;
             $projects = Project::where('email', $email)
                    ->latest()
                    ->take(10)
                    ->get();

// if ($users_data) {
//                  if ($users_data instanceof \Illuminate\Http\RedirectResponse) {
//                 return $users_data; // Return the redirect response
//                 }
//       }
            return view('frontend.index', [
                'user' => $user,
                 'mouseClicksToday' => $mouseClicksToday, // Pass today's mouse clicks to view
                'KeyboardHitToday' => $KeyboardHitToday,
                'todayDuration' => $formattedTodayDuration,
                'formattedDuration' => $formattedDuration,
                'mouseClicks' => $totalMouseClicks,
                'percentageClicks' => $percentageClicks,
                'chartData1' => $chartData1,
                'chartData2' => $chartData2,
                'chartData3' => $chartData3,
                'differenceIndicator' => $differenceIndicator,
                'percentageDifference' => $percentageDifference,
                'percentageDifferenceDuraion'=>$percentageDifferenceDuraion,
                'dateRangeSelected' => $dateRangeSelected,
                'KeyboardHit' => $totalKeyboardHit,
                'projects' => $projects,
                'dateRange'=>$dateRange,
                 'chartDataSoftwareUsage' => $chartDataSoftwareUsage,
                 'softwareUsageData' =>$softwareUsageData,
                 'softwarePercentageData' => $softwarePercentageData,
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

public function DateRange(Request $request)
{
    // Reuse the logic from the index method, handle date range here
    return $this->index($request);
}
}
