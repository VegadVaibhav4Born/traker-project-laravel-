<?php
namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Activity;
use Carbon\Carbon;

class SelectBoxRefreshController extends Controller
{
    private function processProjectDetails($project)
{
    $activities = Activity::where('project_id', $project->project_id)->get();
    $totalSeconds = 0;
    $dailyDurations = [];
    $activity_data = [];

    foreach ($activities as $activity) {
        $startTime = new Carbon($activity->start_time);
        $endTime = new Carbon($activity->end_time);
        $duration = $startTime->diffInSeconds($endTime);
        $totalSeconds += $duration;

        $activityDate = $startTime->format('d M');
        if (!isset($dailyDurations[$activityDate])) {
            $dailyDurations[$activityDate] = 0;
        }
        $dailyDurations[$activityDate] += $duration;

        $project_name = Project::where('project_id', $activity->project_id)->value('project_name') ?? 'Unknown Project';
        $member = User::find($activity->member_id);
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

    $sumOfGroupDurations = $this->calculateGroupDurations($dailyDurations);

    $user_data = $this->getUserData($project);

    return [
        'id' => $project->id,
        'project_id' => $project->project_id,
        'project_logo' => $project->project_logo,
        'project_name' => $project->project_name,
        'total_duration' => $this->formatDurationInSeconds($totalSeconds),
        'daily_durations' => $this->formatDailyDurations($dailyDurations),
        'user_data' => $user_data,
        'group_durations' => $sumOfGroupDurations,
        'activity_data' => $activity_data, // Ensure this key is always set
    ];
}

    public function index(Request $request)
    {
        $email = $request->session()->get('email');
        $type = $request->session()->get('type');

        if (!$email) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $user_h = User::where('status_type', 'complete')
            ->where('email', $email)
            ->where('type', $type)
            ->first();

        if (!$user_h) {
            return redirect()->route('otp-verify')->with('error', 'Please First OTP verify.');
        }

        $projects_data = Project::where('email', $email)->get();
        $select_by = $request->input('select_by');
        $projectDetails = [];

        foreach ($projects_data as $project) {
            $projectDetails[] = $this->getProjectDetailsBySelection($project, $select_by);
        }

        $view = view('frontend.test_selectBox', [
            'select_by' => $select_by,
            'projectDetails' => $projectDetails,
            'user' => $user_h
        ])->render();

        return response()->json(['html' => $view]);
    }

    private function getProjectDetailsBySelection($project, $select_by)
    {
        return $this->processProjectDetails($project);
    }

   
    private function calculateGroupDurations($dailyDurations)
    {
        // Implement your logic for calculating group durations here
        return []; // Placeholder
    }

    private function getUserData($project)
{
    $memberIds = is_array($project->member_id) ? $project->member_id : explode(',', $project->member_id);
    $user_data = [];

    foreach ($memberIds as $memberId) {
        $durationmember = 0;
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
                'totalDuration' => $this->formatDurationInSeconds($durationmember),
            ];
        }
    }

    return $user_data;
}
public function fetchData(Request $request)
{
    $select_by = $request->input('select_by');
    $data = [];

    if ($select_by === 'show_by_project') {
        $data = Project::all(); // Fetch projects or adjust as needed
    } elseif ($select_by === 'show_by_member') {
        $data = User::all(); // Fetch users or adjust as needed
    } elseif ($select_by === 'show_by_activity') {
        $data = Activity::all(); // Fetch activities or adjust as needed
    }

    // Generate the HTML content from a Blade view
    $htmlContent = view('frontend.test_selectBox', ['data' => $data])->render();

    return response()->json(['html' => $htmlContent]);
}




    private function formatDurationInSeconds($seconds)
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $seconds = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function formatDailyDurations($dailyDurations)
    {
        $formattedDurations = [];
        foreach ($dailyDurations as $date => $duration) {
            $formattedDurations[$date] = $this->formatDurationInSeconds($duration);
        }
        return $formattedDurations;
    }
}
