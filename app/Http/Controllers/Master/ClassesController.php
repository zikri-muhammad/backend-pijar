<?php

namespace App\Http\Controllers\Master;

use DB;
use App\Http\Controllers\Controller;
use App\Models\Client\Student;
use Illuminate\Http\Request;
use App\Models\Master\ClassList;
use App\Models\User;
use Auth;


class ClassesController extends Controller
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

        $data = $request->all();
        
        /**
         * If the id is = 'group':
         * => Display available class of the school
         */
        if ($id == 'group') {
            //validate incoming request 
            $this->validate($request, [
                'domain' => 'required|string|max:255'
            ]);
            
            $data = Student::select('class_name')->whereNotNull('class_name')->groupBy('class_name')->whereClientId(config('client_id'))->orderBy('class_name')->get();

            if ($data->count() > 0) {
                set_response('Success retrieve data', $data);
            } else {
                set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
            }
        } else {
            $filters = filters($data);

            try {
                $data = ClassList::data($filters, $id);
            } catch (\Throwable $th) {
                set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
            }

            if ($data->count() > 0 ) {
                set_response('Success retrieve data', $data);
            } else {
                set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
            }
        }

    } 

    public function whereUserAuth(Request $request){
        $mst_class_id = $request->user()->mst_class_id;
        // $user = Auth::user()->mst_class_id;
        // dd($mst_class_id);
        $data = $request->all();
        // $filters = filters($data);
        
        try {

            $data = ClassList::where('id', $mst_class_id)->first();
            // dd($data);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ($data->count() > 0 ) {
            $results = [
                'idx'  => md5($data->id),
                'name' => $data->name,
                'code' => $data->code
            ];
            set_response('Success retrieve data', $results);
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
        $data = $request->all();

        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:mst_classes'
        ]);

        try {
            DB::beginTransaction();

            $province = new ClassList;
            $province->create($data);
            
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
        
        $province = ClassList::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $province)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $id = $province->id;

        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:mst_classes,code,'.$id
        ]);

        try {
            DB::beginTransaction();
            
            $province->editData($data);
            
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
        $province = ClassList::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $province)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        try {
            $province->delete();
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        set_response('Delete data success');
    } // delete a domain
}
