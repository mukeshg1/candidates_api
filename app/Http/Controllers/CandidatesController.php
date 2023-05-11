<?php

namespace App\Http\Controllers;

use App\Models\Candidates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CandidatesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = 10;
        if ($request->has('per_page')) $per_page = $request->per_page;

        $json['code'] = 200;
        $candidates = Candidates::paginate($per_page);
        $json['data'] = $candidates;
        return response()->json($json);
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
        $response_code = 200;
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:40',
            'last_name' => 'max:40',
            'email' => 'email|max:100',
            'contact_number' => 'max:100',
            'gender' => 'number|max:1',
            'specialization' => 'max:200',
            'work_ex_year' => 'max:30',
            'candidate_dob' => 'date',
            'address' => 'max:500',
        ]);
        if ($validator->fails()) {
            $response = [
                'message' => '',
                'status' => 'error',
                'errors' => $validator->errors()
            ];
            $response_code = 422;
        } else {
            $candidates = new Candidates();
            $candidates->first_name = $request->first_name;
            $candidates->last_name = $request->last_name;
            $candidates->email = $request->email;
            $candidates->contact_number = $request->contact_number;
            $candidates->gender = $request->gender;
            $candidates->specialization = $request->specialization;
            $candidates->candidate_dob = $request->candidate_dob;
            $candidates->address = $request->address;

            if ($request->hasFile('resume')) {
                $resume = $request->file('resume');
                $path = $resume->store('resume');
                $candidates->resume = $path;
            }

            $candidates->save();
            $response = [
                'message' => 'Candidate added.',
                'status' => 'success',
                'data' => $candidates
            ];
        }

        return response()->json($response, $response_code);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Candidates  $candidates
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $response_code = 200;
        $response = Candidates::find($id);
        return response()->json($response, $response_code);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidates  $candidates
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Candidates $candidates)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Candidates  $candidates
     * @return \Illuminate\Http\Response
     */
    public function destroy(Candidates $candidates)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Candidates  $candidates
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $per_page = 10;
        if ($request->has('per_page')) $per_page = $request->per_page;
        // Validate the request body
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $data =  $request->json()->all();
        $first_name = $data["first_name"];
            $last_name = $data["last_name"];
            $email = $data["email"];
        $candidates = Candidates::where('first_name', 'LIKE', "%$first_name%")
            ->orWhere('last_name', 'LIKE', "%$last_name%")
            ->orWhere('email', 'LIKE', "%$email%")
            ->paginate($per_page);
        $json['code'] = 200;
        $json['data'] = $candidates;
        return response()->json($json);
    }
}
