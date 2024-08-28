<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Activity;
use App\Models\project;
class ProjectDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

// public function show($id)
// {
//     $user = User::find($id);

//     if ($user) {
//         // Fetch projects where either email matches or member_id matches the user ID
//         $projects = Project::where('email', $user->email)
//             ->orWhere('member_id', $user->id)
//             ->get();

//         // Check if there are projects matching email, member_id, or both
//         $emailProjects = Project::where('email', $user->email)->get();
        
//     //   $memberProjects = Project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])->get();
//         $memberProjects = Project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])
//             ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(status, ?)) = ?', [$user->id, 'accepted'])
//             ->get();
//         $emailFound = $emailProjects->isNotEmpty();
//         $memberIdFound = $memberProjects->isNotEmpty();

//         if ($emailFound && $memberIdFound) {
//             $response = [
//                 'message' => 'Projects found with both email and member ID.',
//                 'status' => 'success',
//                 'projects_name' => [
//                     'email_projects' => $emailProjects->pluck('project_name'),
//                     'member_projects' => $memberProjects->pluck('project_name'),
//                 ],
//             ];
//         } elseif ($emailFound) {
//             $response = [
//                 'message' => 'Projects found with the email.',
//                 'status' => 'success',
//                 'projects_name' => $emailProjects->pluck('project_name'),
//             ];
//         } elseif ($memberIdFound) {
//             $response = [
//                 'message' => 'Projects found with the member ID.',
//                 'status' => 'success',
//                 'projects_name' => $memberProjects->pluck('project_name'),
//             ];
//         } else {
//             $response = [
//                 'message' => 'No projects found for this user',
//                 'status' => 'failed',
//             ];
//         }
//     } else {
//         $response = [
//             'message' => 'User not found',
//             'status' => 'failed',
//         ];
//     }

//     return response()->json($response);
// }

// public function show($id)
// {
//     $user = User::find($id);

//     if ($user) {
//         $project = Project::all();
//         // Fetch projects where email matches
//         $emailProjects = Project::where('email', $user->email)->get();
        
//         // Fetch projects where member_id contains the user ID and status is "accepted"
//         // $memberProjects = Project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])
           
//         //     ->get();
//         $memberProjects = Project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])
//         ->whereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "accepted"', [$user->id])
//         ->get();
//         // Check if there are projects with the email or member ID
//         $emailFound = $emailProjects->isNotEmpty();
//         $memberIdFound = $memberProjects->isNotEmpty();

        

//         if ($emailFound && $memberIdFound) {
//             $response = [
                
//                 'message' => 'Projects found with both email and member ID.',
//                 'status' => 'success',
//                 'projects_name' => [
//                     'email_projects' => $emailProjects->pluck('project_name'),
//                     'member_projects' => $memberProjects->pluck('project_name'),
//                     'created by'=>$username->name,
//                 ],
//             ];
//         } elseif ($emailFound) {
//               $username = User::where('email', $emailProjects->email)
               
//                 ->get();
//             $response = [
//                 'message' => 'Projects found with the email.',
//                 'status' => 'success',
//                 'projects_name' => $emailProjects->pluck('project_name'),
//                 'created by'=>$username->name,
//             ];
//         } elseif ($memberIdFound) {
//               $username = User::Where('email', $memberProjects->email)
//     ->get();
//             $response = [
//                 'message' => 'Projects found with the member ID.',
//                 'status' => 'success',
//                 'projects_name' => $memberProjects->pluck('project_name'),
//                 'created by'=>$username->name,
//             ];
//         } else {
//             $response = [
//                 'message' => 'No projects found for this user',
//                 'status' => 'failed',
//             ];
//         }
//     } else {
//         $response = [
//             'message' => 'User not found',
//             'status' => 'failed',
//         ];
//     }

//     return response()->json($response);
// }

public function show($id)
{
    $user = User::find($id);

    if ($user) {
        // Fetch projects where email matches
        $emailProjects = Project::where('email', $user->email)->get();
        
        // Fetch projects where member_id contains the user ID and status is "accepted"
        $memberProjects = Project::whereRaw('FIND_IN_SET(?, member_id)', [$user->id])
            ->whereRaw('JSON_EXTRACT(status, CONCAT("$.", ?)) = "accepted"', [$user->id])
            ->get();
        
        // Check if there are projects with the email or member ID
        $emailFound = $emailProjects->isNotEmpty();
        $memberIdFound = $memberProjects->isNotEmpty();

        // Initialize the response array
        $projectCreatorMap = [];

        // Combine projects from email and member ID results
        $projects = $emailProjects->merge($memberProjects)->unique('id'); // Ensure unique by project ID

        foreach ($projects as $project) {
            $creatorEmail = $project->email;
            $creator = User::where('email', $creatorEmail)->first();
            $creatorName = $creator ? $creator->name : 'Unknown';
            $projectCreatorMap[$project->project_id] = [
                'project_name' => $project->project_name,
                'created_by' => $creatorName,
            ];
        }

        if ($emailFound || $memberIdFound) {
            $response = [
                'message' => 'Projects found.',
                'status' => 'success',
                'projects' => $projectCreatorMap,
            ];
        } else {
            $response = [
                'message' => 'No projects found for this user',
                'status' => 'failed',
            ];
        }
    } else {
        $response = [
            'message' => 'User not found',
            'status' => 'failed',
        ];
    }

    return response()->json($response);
}


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
