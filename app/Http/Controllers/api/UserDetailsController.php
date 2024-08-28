<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Activity;
use App\Models\project;
class UserDetailsController extends Controller
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
public function show($id)
{  
    // Fetch the user with the given ID
    $user = User::find($id);
    
    if ($user) {
        $response = [
            'message' => 'User Found',
            'status' => 'success',
        ];
        
        // Fetch projects where member_id contains the given ID
        $projects = Project::where(function ($query) use ($id) {
            $query->where('member_id', $id)
                  ->orWhere('member_id', 'LIKE', "%,$id,%")
                  ->orWhere('member_id', 'LIKE', "$id,%")
                  ->orWhere('member_id', 'LIKE', "%,$id")
                  ->orWhere('member_id', 'LIKE', "$id");
        })->get();
        
        if ($projects->isNotEmpty()) {
            $project_data = $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'project_id' => $project->project_id,
                    'member_id' => $project->member_id,
                    'status' => $project->status
                ];
            });

            $response['message'] = 'Project Found';
            $response['project_data'] = [
                'projects' => $project_data
            ];
        } else {
            $response = [
                'message' => 'Project Not Found',
                'status' => 'success',
            ];
        }
    } else {
        $response = [
            'message' => 'User Not Found',
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
