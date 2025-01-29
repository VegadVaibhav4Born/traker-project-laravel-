<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class ActivityOneAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
    {
        $start_time = $request->start_time;
        $data = [
            'title' => $request->title ,
            'project_id' => $request->project_id ,
            'member_id' => $request->member_id ,
            'mouse_click' => 0 ,
            'keyboard_click' => 0,
            'start_time' => $request->start_time,
            'end_time' => '0000-00-00 00:00:00',
            'durations' => '00:00:00',
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
                    $data['id'] = $Activity->id;
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data Insert Success.',
                        'data' => $data
                    ]);
                
            }
            else{
                return response()->json([
                    'status'=>'Failed',
                    'message' => 'Data Insert Failed: ' . $err->getMessage() ]);
            }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editRecord(Request $request,$id)
    { // update mouseclick,kayboard click ,duration , end time .....
           
            $activity = Activity::find($id);
        
            if (is_null($activity)) {
                return response()->json([
                    'status' => 'Failed',
                    'message' => 'Activity Not Found.'
                ]);
            }
        else{
             
            //  $end_time = $request-> end_time;
             $end_time = Carbon::parse($request->end_time);
             $start_time = $activity->start_time;
                 $start = Carbon::parse($start_time);
                $durationInSeconds = $end_time->diffInSeconds($start);
        
                // Convert duration to hours, minutes, and seconds
                $hours = floor($durationInSeconds / 3600);
                $minutes = floor(($durationInSeconds % 3600) / 60);
                $seconds = $durationInSeconds % 60;
                
                // Format duration as HH:MM:SS
                $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            DB::beginTransaction();
            try {
                $activity->mouse_click = $request-> mouse_click;
                $activity->keyboard_click = $request-> keyboard_click;
                $activity->software_use_name = $request-> software_use_name;
                $activity->end_time = $end_time;
                $activity->durations = $formattedDuration;
                $activity->save();
                DB::commit();
        
                return response()->json([
                    'status' => 'success',
                    'message' => 'Screenshot Inserted Successfully.',
                ]);
        
            } catch (\Exception $err) {
                DB::rollback();
        
                return response()->json([
                    'status' => 'Failed',
                    'message' => 'Data Insert Failed: ' . $err->getMessage(),
                ]);
            }
        }     
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

// store screenshort 
public function updateScrennshort(Request $request, $id)
{ 
    $activity = Activity::find($id);

    if (is_null($activity)) {
        return response()->json([
            'status' => 'Failed',
            'message' => 'Activity Not Found.'
        ]);
    }

    DB::beginTransaction();

   try {
    $existingScreenshots = $activity->screenshot ? explode(',', $activity->screenshot) : [];
    $duration = new \DateTime('00:00:00'); // Initialize end time to start from 00:00:00

    // Calculate the end time based on existing screenshots
    foreach ($existingScreenshots as $screenshot) {
        $duration->add(new \DateInterval('PT5M')); // Add 5 minutes for each existing screenshot
    }

    if ($request->file('screenshot')) {
        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $filePath = 'images/screenshots/';
            $fullPath = public_path($filePath);

            // Ensure the directory exists
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }
            $dateTime = new \DateTime();
            $formattedDateTime = $dateTime->format('dmY_His'); // Format: YYYYMMDD_HHMMSS

            // Generate a unique file name with extension
            $fileName = $formattedDateTime . '_' . $file->getClientOriginalName();

            // Move the new file to the specified path
            $file->move($fullPath, $fileName);

            // Insert new screenshot path at the beginning of the array
            array_unshift($existingScreenshots, $fileName);
            $duration->add(new \DateInterval('PT5M')); // Add duration for the new screenshot

            // Convert the array of paths to a comma-separated string
            $activity->screenshot = implode(',', $existingScreenshots);
            $activity->durations = $duration->format('H:i:s');
            $activity->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Screenshot Inserted Successfully.',
            ]);
        } else {
            return response()->json([
                'status' => 'Failed',
                'message' => 'Please Select Screenshot.',
            ]);
        }
    } else {
        return response()->json([
            'status' => 'Failed',
            'message' => 'Please Pass Screenshot Parameter.',
        ]);
    }
} 
 catch (\Exception $err) {
        DB::rollback();

        return response()->json([
            'status' => 'Failed',
            'message' => 'Data Insert Failed: ' . $err->getMessage(),
        ]);
    }
}

  
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
