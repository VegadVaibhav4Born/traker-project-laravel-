<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::all();
        return response()->json($activities);
    }
    public function store(Request $request)
    {
        
        $end_time = $request->end_time;
        $start = Carbon::now();
        $end = Carbon::parse($end_time);
        // $duration = $end->diffInSeconds($start);
       $durationInSeconds = $end->diffInSeconds($start);

// Convert duration to hours, minutes, and seconds
$hours = floor($durationInSeconds / 3600);
$minutes = floor(($durationInSeconds % 3600) / 60);
$seconds = $durationInSeconds % 60;

// Format duration as HH:MM:SS
$formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        $data = [
            'title' => $request-> title ,
            'project_id' => $request-> project_id ,
            'member_id' => $request-> member_id ,
            'mouse_click' => $request-> mouse_click ,
            'keyboard_click' => $request-> keyboard_click,
            'screenshot' => $request->  screenshot,
            'software_use_name' => $request->  software_use_name,
            'software_use_time' => $request-> software_use_time ,
            'start_time' => $request->  start_time,
            'end_time' => $request->  end_time,
            'durations' => $formattedDuration,
            ];
            
            DB::BeginTransaction();
            try{
                $Activity = Activity::create($data);
                DB::Commit();
            }
            catch(\Exception $err)
            {
                DB::rollBack();
                print_r($err->getMessage());
                $Activity = null;
            }
            if($Activity != null)
            {
                return response()->json([
                    'status'=>'success',
                    'message'=>'Data Insert Success.']);
            }
            else{
                return response()->json([
                    'status'=>'Failed',
                    'message' => 'Data Insert Failed: ' . $err->getMessage() ]);
            }

    }
}




