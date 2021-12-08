<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Master\CourseList;
use App\Models\Master\Calendar;

class CalendarController extends Controller
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
            $data = Calendar::data($filters, $id);
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
        // dd($data);

        // validate incoming request
        $this->validate($request, [
            'kategori_id'           => 'required',
            'mst_course_id'         => 'required',
            'name_aktivitas'        => 'required',
            'tanggal_aktivitas'     => 'required',
            'teacher_id'            => 'required',
        ]);

        try {
            DB::beginTransaction();

            $task = new Calendar();
            $task->addNewCalendar($data);


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
        $task = Calendar::where(DB::raw('md5(id::text)'), $id)->first();

        if (!$task)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $task->delete();

        set_response('Delete data success');
    }
}
