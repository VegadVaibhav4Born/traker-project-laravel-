<?php
// namespace App\Http\Controllers\frontend;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\User;
// use App\Models\Activity;
// use Carbon\Carbon;

// class HomeController extends Controller
// {
  

// public function index(Request $request)
    // {
    //     $email = $request->session()->get('email');
    //     $username = $request->session()->get('username');
    //     $select_status = User::where('email', $email)->first();

    //     if (!$select_status || $select_status->status_type !== "complete") {
    //         return redirect("otp-verify")->withErrors(['otp' => 'Please complete OTP verification.']);
    //     }

    //     $type = $request->session()->get('type');
    //     $duration = 0;
    //     $totalMouseClicks = 0;
    //     $previousDuration = 0;
    //     $previousMouseClicks = 0;

    //     if ($email) {
    //         $user = User::where('email', $email)->where('type', $type)->first();
    //         $dateRange = $request->input('date_range');

    //         if ($dateRange) {
    //             $dates = explode(' to ', $dateRange);
    //             $startDate = Carbon::parse($dates[0])->startOfDay();
    //             $endDate = Carbon::parse($dates[1])->endOfDay();
    //         } else {
    //             $startDate = Carbon::now()->startOfDay();
    //             $endDate = Carbon::now()->endOfDay();
    //         }

    //         $filteredData = Activity::whereBetween('start_time', [$startDate, $endDate])->get();
    //         $previousData = Activity::where('start_time', '<', $startDate)->get();

    //         foreach ($filteredData as $activity) {
    //             $startTime = Carbon::parse($activity->start_time);
    //             $endTime = Carbon::parse($activity->end_time);
    //             $duration += $endTime->diffInSeconds($startTime);
    //             $totalMouseClicks += $activity->mouse_click;
    //         }
            

    //         foreach ($previousData as $activity) {
    //             $startTime = Carbon::parse($activity->start_time);
    //             $endTime = Carbon::parse($activity->end_time);
    //             $previousDuration += $endTime->diffInSeconds($startTime);
    //             $previousMouseClicks += $activity->mouse_click;
    //         }


    //         $percentageDifference = $previousMouseClicks > 0
    //             ? (($totalMouseClicks - $previousMouseClicks) / $previousMouseClicks) * 100
    //             : 0;


    //         $durationDifference = $previousDuration - $duration;
    //         $differenceIndicator = $durationDifference >= 0 ? '+' : '-';

    //         $totalDuration = $filteredData->count() * 86400; // Assuming 24 hours per activity; adjust as necessary
    //         $percentageClicks = $totalDuration > 0 ? ($totalMouseClicks / $totalDuration) * 100 : 0;

    //         $hours = floor($duration / 3600);
    //         $minutes = floor(($duration % 3600) / 60);
    //         $seconds = $duration % 60;
    //         $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    //         $chartData = [
    //             'series' => [$percentageDifference],
    //         ];

    //         return view('frontend.index', [
    //             'user' => $user,
    //             'duration' => $formattedDuration,
    //             'mouseClicks' => $totalMouseClicks,
    //             'percentageClicks' => $percentageClicks,
    //             'differenceIndicator' => $differenceIndicator,
    //             'percentageDifference' => $percentageDifference,
    //             'chartData' => $chartData,
    //         ]);
    //     } else {
    //         return redirect()->route('login');
    //     }
    // }

    // public function filter(Request $request)
    // {
    //     // Implement filter logic if needed
    // }
// public function index(Request $request)
// {
//     $email = $request->session()->get('email');
//     $select_status = User::where('email', $email)->first();

//     if (!$select_status || $select_status->status_type !== "complete") {
//         return redirect("otp-verify")->withErrors(['otp' => 'Please complete OTP verification.']);
//     }

//     $type = $request->session()->get('type');
//     $duration = 0;
//     $totalMouseClicks = 0;
//     $previousDuration = 0;
//     $previousMouseClicks = 0;

//     if ($email) {
//         $user = User::where('email', $email)->where('type', $type)->first();
//         $dateRange = $request->input('date_range');

//         if ($dateRange) {
//             $dates = explode(' to ', $dateRange);
//             $startDate = Carbon::parse($dates[0])->startOfDay();
//             $endDate = Carbon::parse($dates[1])->endOfDay();
//         } else {
//             $startDate = Carbon::now()->startOfDay();
//             $endDate = Carbon::now()->endOfDay();
//         }

//         $filteredData = Activity::whereBetween('start_time', [$startDate, $endDate])->get();
//         $previousData = Activity::where('start_time', '<', $startDate)->get();

//         foreach ($filteredData as $activity) {
//             $startTime = Carbon::parse($activity->start_time);
//             $endTime = Carbon::parse($activity->end_time);
//             $duration += $endTime->diffInSeconds($startTime);
//             $totalMouseClicks += $activity->mouse_click;
//         }

//         foreach ($previousData as $activity) {
//             $startTime = Carbon::parse($activity->start_time);
//             $endTime = Carbon::parse($activity->end_time);
//             $previousDuration += $endTime->diffInSeconds($startTime);
//             $previousMouseClicks += $activity->mouse_click;
//         }

//         $percentageDifference = $previousMouseClicks > 0
//             ? (($totalMouseClicks - $previousMouseClicks) / $previousMouseClicks) * 100
//             : 0;

//         $durationDifference = $previousDuration - $duration;
//         $differenceIndicator = $durationDifference >= 0 ? '+' : '-';

//         $totalDuration = $filteredData->count() * 86400; // Assuming 24 hours per activity
//         $percentageClicks = $totalDuration > 0 ? ($totalMouseClicks / $totalDuration) * 100 : 0;

//         $hours = floor($duration / 3600);
//         $minutes = floor(($duration % 3600) / 60);
//         $seconds = $duration % 60;
//         $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

//         // Data for Chart 1
//         $chartData1 = [
//             'series' => [ $percentageDifference ],
//         ];

//         // Data for Chart 2
//         $chartData2 = [
//             'series' => [
//                 [
//                     'name' => 'Duration',
//                     'data' => $filteredData->map(function($activity) {
//                         $startTime = Carbon::parse($activity->start_time);
//                         $endTime = Carbon::parse($activity->end_time);
//                         return $endTime->diffInSeconds($startTime);
//                     })->toArray()
//                 ]
//             ],
//             'xaxis' => [
//                 'categories' => $filteredData->map(function($activity) {
//                     return Carbon::parse($activity->start_time)->format('Y-m-d'); // Ensure it's a Carbon instance
//                 })->toArray()
//             ]
//         ];

//         return view('frontend.index', [
//             'user' => $user,
//             'duration' => $formattedDuration,
//             'mouseClicks' => $totalMouseClicks,
//             'percentageClicks' => $percentageClicks,
//             'chartData1' => $chartData1,
//             'chartData2' => $chartData2,
//             'differenceIndicator' => $differenceIndicator,
//             'percentageDifference' => $percentageDifference, // Pass the variable to the view
//         ]);
//     } else {
//         return redirect()->route('login');
//     }
// }

// public function index(Request $request)
// {
//     $email = $request->session()->get('email');
//     $select_status = User::where('email', $email)->first();

//     if (!$select_status || $select_status->status_type !== "complete") {
//         return redirect("otp-verify")->withErrors(['otp' => 'Please complete OTP verification.']);
//     }

//     $type = $request->session()->get('type');
//     $duration = 0;
//     $totalMouseClicks = 0;
//     $previousDuration = 0;
//     $previousMouseClicks = 0;

//     if ($email) {
//         $user = User::where('email', $email)->where('type', $type)->first();
        
//         if ($user) {
//             $activities = Activity::where('member_id', $user->id)->get();
//         }

//         $dateRange = $request->input('date_range');

//         // Default to today if no date range provided
//         if ($dateRange) {
//             $dates = explode(' to ', $dateRange);
//             $startDate = Carbon::parse($dates[0])->startOfDay();
//             $endDate = Carbon::parse($dates[1])->endOfDay();
//         } else {
//             $startDate = Carbon::now()->startOfDay();
//             $endDate = Carbon::now()->endOfDay();
//         }

//         // Get data for the last 7 days for Chart 2
//         $startDateForChart2 = Carbon::now()->subDays(7)->startOfDay();
//         $endDateForChart2 = Carbon::now()->endOfDay();
        
//         $filteredData = Activity::whereBetween('start_time', [$startDate, $endDate])->get();
//         $previousData = Activity::where('start_time', '<', $startDate)->get();
//         $filteredDataForChart2 = Activity::whereBetween('start_time', [$startDateForChart2, $endDateForChart2])->get();

//         foreach ($filteredData as $activity) {
//             $startTime = Carbon::parse($activity->start_time);
//             $endTime = Carbon::parse($activity->end_time);
//             $duration += $endTime->diffInSeconds($startTime);
//             $totalMouseClicks += $activity->mouse_click;
//         }

//         foreach ($previousData as $activity) {
//             $startTime = Carbon::parse($activity->start_time);
//             $endTime = Carbon::parse($activity->end_time);
//             $previousDuration += $endTime->diffInSeconds($startTime);
//             $previousMouseClicks += $activity->mouse_click;
//         }

//         $percentageDifference = $previousMouseClicks > 0
//             ? (($totalMouseClicks - $previousMouseClicks) / $previousMouseClicks) * 100
//             : 0;

//         // Cap percentageDifference at 100%
//         if ($percentageDifference > 100) {
//             $percentageDifference = 100;
//         } elseif ($percentageDifference < -100) {
//             $percentageDifference = -100;
//         }

//         $durationDifference = $previousDuration - $duration;
//         $differenceIndicator = $durationDifference >= 0 ? '-' : 'y';

//         $totalDuration = $filteredData->count() * 86400; // Assuming 24 hours per activity
//         $percentageClicks = $totalDuration > 0 ? ($totalMouseClicks / $totalDuration) * 100 : 0;

//         $hours = floor($duration / 3600);
//         $minutes = floor(($duration % 3600) / 60);
//         $seconds = $duration % 60;
//         $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

//         // Calculate today's progress
//         $todayStart = Carbon::now()->startOfDay();
//         $todayEnd = Carbon::now()->endOfDay();
//         $todayActivities = Activity::whereBetween('start_time', [$todayStart, $todayEnd])->get();
        
//         $todayDuration = 0;
//         foreach ($todayActivities as $activity) {
//             $startTime = Carbon::parse($activity->start_time);
//             $endTime = Carbon::parse($activity->end_time);
//             $todayDuration += $endTime->diffInSeconds($startTime);
//         }

//         $hoursToday = floor($todayDuration / 3600);
//         $minutesToday = floor(($todayDuration % 3600) / 60);
//         $secondsToday = $todayDuration % 60;
//         $formattedTodayDuration = sprintf('%02d:%02d:%02d', $hoursToday, $minutesToday, $secondsToday);

//         // Data for Chart 1 (Today's progress)
//         $chartData1 = [
//             'series' => [ $percentageDifference ],
//             'formattedTodayDuration' => $formattedTodayDuration,
//         ];

//         // Data for Chart 2 (Last 7 Days)
//         $chartData2 = [
//             'series' => [
//                 [
//                     'name' => 'Duration',
//                     'data' => $filteredDataForChart2->map(function($activity) {
//                         $startTime = Carbon::parse($activity->start_time);
//                         $endTime = Carbon::parse($activity->end_time);
//                         return $endTime->diffInSeconds($startTime);
//                     })->toArray()
//                 ]
//             ],
//             'xaxis' => [
//                 'categories' => $filteredDataForChart2->map(function($activity) {
//                     return Carbon::parse($activity->start_time)->format('Y-m-d');
//                 })->toArray()
//             ]
//         ];

//         return view('frontend.index', [
//             'user' => $user,
//             'formattedDuration' => $formattedDuration,
//             'mouseClicks' => $totalMouseClicks,
//             'percentageClicks' => $percentageClicks,
//             'chartData1' => $chartData1,
//             'chartData2' => $chartData2,
//             'differenceIndicator' => $differenceIndicator,
//             'percentageDifference' => $percentageDifference,
//         ]);
        
//     } else {
//         return redirect()->route('login');
//     }
// }
//}

// new code homecontroller
<?php
namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Activity;
use Carbon\Carbon;

class HomeController extends Controller
{
    
public function index(Request $request)
{
    $email = $request->session()->get('email');
    $select_status = User::where('email', $email)->first();

    if (!$select_status || $select_status->status_type !== "complete") {
        return redirect("otp-verify")->withErrors(['otp' => 'Please complete OTP verification.']);
    }

    $type = $request->session()->get('type');
    $duration = 0;
    $totalMouseClicks = 0;
    $previousDuration = 0;
    $previousMouseClicks = 0;

    if ($email) {
        $user = User::where('email', $email)->where('type', $type)->first();
        
        if ($user) {
            $activities = Activity::where('member_id', $user->id)->get();
        }

        $dateRange = $request->input('date_range');

        // Default to today if no date range provided
        if ($dateRange) {
            $dates = explode(' to ', $dateRange);
            $startDate = Carbon::parse($dates[0])->startOfDay();
            $endDate = Carbon::parse($dates[1])->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // Get data for the last 7 days for Chart 2 and Chart 3
        $startDateForChart2 = Carbon::now()->subDays(7)->startOfDay();
        $endDateForChart2 = Carbon::now()->endOfDay();
        
        $filteredData = Activity::whereBetween('start_time', [$startDate, $endDate])->get();
        $previousData = Activity::where('start_time', '<', $startDate)->get();
        $filteredDataForChart2 = Activity::whereBetween('start_time', [$startDateForChart2, $endDateForChart2])->get();

        foreach ($filteredData as $activity) {
            $startTime = Carbon::parse($activity->start_time);
            $endTime = Carbon::parse($activity->end_time);
            $duration += $endTime->diffInSeconds($startTime);
            $totalMouseClicks += $activity->mouse_click;
        }

        foreach ($previousData as $activity) {
            $startTime = Carbon::parse($activity->start_time);
            $endTime = Carbon::parse($activity->end_time);
            $previousDuration += $endTime->diffInSeconds($startTime);
            $previousMouseClicks += $activity->mouse_click;
        }

        $percentageDifference = $previousMouseClicks > 0
            ? (($totalMouseClicks - $previousMouseClicks) / $previousMouseClicks) * 100
            : 0;

        // Cap percentageDifference at ±100%
        $percentageDifference = max(-100, min(100, $percentageDifference));

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
        $todayActivities = Activity::whereBetween('start_time', [$todayStart, $todayEnd])->get();
        
        $todayDuration = 0;
        foreach ($todayActivities as $activity) {
            $startTime = Carbon::parse($activity->start_time);
            $endTime = Carbon::parse($activity->end_time);
            $todayDuration += $endTime->diffInSeconds($startTime);
        }

        $hoursToday = floor($todayDuration / 3600);
        $minutesToday = floor(($todayDuration % 3600) / 60);
        $secondsToday = $todayDuration % 60;
        $formattedTodayDuration = sprintf('%02d:%02d:%02d', $hoursToday, $minutesToday, $secondsToday);

        // Data for Chart 1 (Today's progress)
        $chartData1 = [
            'series' => [ $percentageDifference ],
            'formattedTodayDuration' => $formattedTodayDuration,
        ];

        // Data for Chart 2 (Last 7 Days)
        $chartData2 = [
            'series' => [
                [
                    'name' => 'Duration',
                    'data' => $filteredDataForChart2->map(function($activity) {
                        $startTime = Carbon::parse($activity->start_time);
                        $endTime = Carbon::parse($activity->end_time);
                        return $endTime->diffInSeconds($startTime);
                    })->toArray()
                ],
                [
                    'name' => 'Mouse Clicks',
                    'data' => $filteredDataForChart2->map(function($activity) {
                        return $activity->mouse_click;
                    })->toArray()
                ]
            ],
            'xaxis' => [
                'categories' => $filteredDataForChart2->map(function($activity) {
                    return Carbon::parse($activity->start_time)->format('Y-m-d');
                })->toArray()
            ]
        ];

        // Data for Chart 3
        if ($dateRange) {
            // Use selected date range for Chart 3
            $dataForChart3 = $filteredData;
        } else {
            // Use last 7 days for Chart 3
            $dataForChart3 = Activity::whereBetween('start_time', [$startDateForChart2, $endDateForChart2])->get();
        }

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
                        $startTime = Carbon::parse($activity->start_time);
                        $endTime = Carbon::parse($activity->end_time);
                        return $endTime->diffInSeconds($startTime);
                    })->toArray()
                ]
            ],
            'xaxis' => [
                'categories' => $dataForChart3->map(function($activity) {
                    return Carbon::parse($activity->start_time)->format('Y-m-d');
                })->toArray()
            ]
        ];

        return view('frontend.index', [
            'user' => $user,
            'formattedDuration' => $formattedDuration,
            'mouseClicks' => $totalMouseClicks,
            'percentageClicks' => $percentageClicks,
            'chartData1' => $chartData1,
            'chartData2' => $chartData2,
            'chartData3' => $chartData3, // Pass the data for Chart 3
            'differenceIndicator' => $differenceIndicator,
            'percentageDifference' => $percentageDifference,
        ]);
        
    } else {
        return redirect()->route('login');
    }
}



}
