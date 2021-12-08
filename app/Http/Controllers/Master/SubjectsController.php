<?php

namespace App\Http\Controllers\Master;

use DB;
use App\Http\Controllers\Controller;
use App\Models\Master\ClassList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use  App\User;
use App\Models\Master\Subject;
use Illuminate\Support\Facades\Input;

class SubjectsController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * Getting lists of Data.
     *
     * @return void
     */
    public function index($id = '', Request $request){
        // $mst_class_id = $request->user()->mst_class_id;
        // dd($mst_class_id);
        $data = $request->all();
        $filters = filters($data);
        $class = ClassList::all();

        
        try {
            $data = Subject::data($filters, $id);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ($data->count() > 0 ) {
            set_response('Success retrieve data', $data);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    } 
    

    /**
     * Store data to database.
     *
     * @return void
     */
    public function store(Request $request){
        // $data = $request->all();
        $name = $request->input('name');
        $code = $request->input('code');
        $mst_class_id = $request->input('mst_class_id');
        $slug = str_slug($name, "-"); 
        $data = [
            'name' => $name,
            'code' => $code,
            'mst_class_id' => $mst_class_id,
            'slug' => $slug
        ];

        // dd($data);

        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:mst_subjects',
            'code' => 'required|string|max:10|unique:mst_subjects',
            'slug' => 'string|unique:mst_subjects'
        ]);

        try {
            DB::beginTransaction();

            $subject = new Subject;
            $subject->create($data);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        set_response('Store data success');
    } 

    /**
     * Update data.
     *
     * @return void
     */
    public function update(Request $request, $id){
        $data = $request->all();
        
        $subject = Subject::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $subject)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $id = $subject->id;

        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:mst_subjects,code,'.$id
        ]);

        try {
            DB::beginTransaction();
            
            $subject->editData($data);
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }
        set_response('Update data success');
    } // handle show edit page POST

    /**
     * Delete data.
     *
     * @return void
     */
    public function destroy($id =''){
        $subject = Subject::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $subject)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $subject->delete();

        set_response('Delete data success');
    } // delete a domain
}
