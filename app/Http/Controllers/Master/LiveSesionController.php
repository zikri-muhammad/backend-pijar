<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\LiveSesion;
use App\Models\Master\Subject;
use App\Models\Client\Teacher;
use Illuminate\Http\Request;
use DB;

class LiveSesionController extends Controller
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

        // dd($data->mst_clas_id);

        // try {
            $data = LiveSesion::data($filters, $id);
        // } catch (\Throwable $th) {
        //     set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        // }

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
            'mst_class_id'      => 'required',
            'mst_subject_id'    => 'required',
            'link_meeting'      => 'required',
            'tanggal_meeting'   => 'required',
            'teacher_id'        => 'required',
        ]);

        try {
            DB::beginTransaction();

            $task = new LiveSesion();
            $task->addNewLiveSession($data);


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

        $liveSession = LiveSesion::where(DB::raw('md5(id::text)'), $id)->first();
        // dd($liveSession);
        if (!$liveSession)
            set_error_response('Data not Found', 'HTTP_NOT_FOUND');

        // validate incoming request
        $this->validate($request, [
            'mst_class_id'      => 'required',
            'mst_subject_id'    => 'required',
            'link_meeting'      => 'required',
            'tanggal_meeting'   => 'required',
            'teacher_id'        => 'required',
        ]);


        try {
            DB::beginTransaction();

            // $liveSession = new liveSession;
            $liveSession->editData($data);

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
        $task = LiveSesion::where(DB::raw('md5(id::text)'), $id)->first();

        if (!$task)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $task->delete();

        set_response('Delete data success');
    }
}
