<?php

namespace App\Http\Controllers\Master;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\CourseList;
use Auth;
// use JWTAuth;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = '', Request $request){

        $data = $request->all();
        $filters = filters($data);
        
        try {
            $data = CourseList::data($filters, $id);
            
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ($data->count() > 0 ) {
            set_response('Success retrieve data', $data);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    } 

    public function whereClassId(Request $request, $idx){

        $data = $request->all();
        
        try {
            $data = CourseList::select(DB::raw('MD5(id::text) as idx, MD5(mst_subject_id::text) as mst_subject_id, name, vidio, code, descriptions,slug'))

                                ->where(DB::raw('md5(mst_subject_id::text)'), $idx)
                                ->get();
            // dd($data);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }
        // dd($data);
       
        if ($data->count() > 0 ) {    
            set_response('Success retrieve data', $data);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    }
    public function whereClassIdUrl(Request $request, $id){
        // $mst_class_id = $request->user()->mst_class_id;
        // dd($mst_class_id);
        $data = $request->all();
        // $filters = filters($data);
        
        try {
            $data = CourseList::where(DB::raw('md5(mst_subject_id::text)'), $id)->get();
            // dd($data);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        // $result = [
        //     // 'idx' => md5($data->results->id),
        //     'name' => $data->name,
        //     'code' => $data->code
        // ];

        if ($data->count() > 0 ) {
            set_response('Success retrieve data', $data);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $data = $request->all();
        $mst_subject_id = $request->input('mst_subject_id');
        $vidio = $request->input('vidio');
        $code = $request->input('code');
        $name = $request->input('name');
        $descriptions = $request->input('descriptions');
        $slug = str_slug($name, "-");
        $data = [
            'mst_subject_id' => $mst_subject_id,
            'vidio' => $vidio,
            'code' => $code,
            'name' => $name,
            'descriptions' => $descriptions,
            'slug' => $slug
        ];
        // dd($data);

        $this->validate($request, [
            'vidio' => 'required',
            'code' => 'required',
            'mst_subject_id' => 'required',
            'slug' => 'string|unique:mst_courses'

        ]);
        try {
            DB::beginTransaction();

            $subject = new CourseList;
            // dd($subject);
            $subject->create($data);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        set_response('Store data success');
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
        $data = $request->all();
        
        $courses = CourseList::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $courses)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $id = $courses->id;

        //validate incoming request 
        $this->validate($request, [
            'vidio' => 'required',
            'code' => 'required|string|max:10'.$id,
            'mst_subject_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            
            $courses->editData($data);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }
        set_response('Update data success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id =''){
        $courses = CourseList::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $courses)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $courses->delete();

        set_response('Delete data success');
    } // delete a domain
}
