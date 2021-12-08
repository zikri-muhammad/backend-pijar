<?php

namespace App\Http\Controllers\Master;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\Regency;

class RegenciesController extends Controller
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
    public function index($id ='', Request $request){

        $data = $request->all();
        $filters = filters($data);
        
        try {
            $data = Regency::data($filters, $id);
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
        $data = $request->all();

        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:mst_regencies'
        ]);

        try {
            DB::beginTransaction();

            $regency = new Regency;
            $regency->create($data);
            
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
        
        $regency = Regency::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $regency)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $id = $regency->id;

        //validate incoming request 
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:mst_regencies,code,'.$id
        ]);

        try {
            DB::beginTransaction();
            
            $regency->editData($data);
            
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
        $regency = Regency::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $regency)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $regency->delete();

        set_response('Delete data success');
    } // delete a domain
}
