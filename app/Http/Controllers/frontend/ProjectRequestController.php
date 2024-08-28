<?php
namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\project;
use App\Models\User;
class ProjectRequestController extends Controller
{
  public function index(Request $request)
{
    $projectId = $request->query('project');
    $userId = $request->query('userId'); // Get the user ID from the query

    // Retrieve the project by its ID
    $project = project::find($projectId);

    if ($project) {
        $status = json_decode($project->status, true); // Decode JSON to array

        // Get the status for the specific user ID
        $userStatus = $userId && isset($status[$userId]) ? $status[$userId] : 'No status available';
        $projectEmail = $project->email;
        $createdby = User::where('email',$projectEmail)->first();
        // Pass the project and user's status to the view
        return view('frontend.projectrequest', [
            'project' => $project,
            'userStatus' => $userStatus,
            'createdby'=>$createdby
            
        ]);
    }

    // Handle case where project is not found
    return redirect('/projects')->with('error', 'Project not found.');
}



   
}
