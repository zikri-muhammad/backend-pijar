<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Pengerjaan;
use Illuminate\Http\Request;
use DB;

class PengerjaanController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = '', Request $request)
    {
        $data = $request->all();
        $filters = filters($data);
        // $mst_class_id = $request->user()->id;
        // dd($id);

        try {
            $data = Pengerjaan::data($filters, $id);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ($data->count() > 0) {
            set_response('Success retrive data', $data);
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
        $data = $request->all();

        // validate incoming request
        $this->validate($request, [
            'mst_task_id' => 'required',
            'mst_student_id' => 'required',
            'nilai' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $pengerjaan = new Pengerjaan;
            $pengerjaan->create($data);

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

        $pengerjaan = Pengerjaan::where(DB::raw('md5(id::text)'), $id)->first();
        // dd($pengerjaan);
        if(! $pengerjaan)
            set_error_response('Data not Found', 'HTTP_NOT_FOUND');

        // validate incoming request
        $this->validate($request, [
            'mst_task_id' => 'required',
            'mst_student_id' => 'required',
            'nilai' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // $pengerjaan = new Task;
            $pengerjaan->editData($data);

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
    public function destroy($id)
    {
        $pengerjaan = Pengerjaan::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $pengerjaan)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $pengerjaan->delete();

        set_response('Delete data success');
    }
}
