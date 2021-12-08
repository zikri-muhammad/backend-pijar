<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Pengerjaan;
use App\Models\Master\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
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

        try {
            $data = Task::data($filters, $id);
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
            'mst_clas_id'       => 'required',
            'mst_course_id'     => 'required',
            'star_at'           => 'required',
            'end_at'            => 'required',
            'teacher_id'        => 'required',
            'mst_jenis_task'    => 'required',
            'mst_soal_id'       => 'required',
            'waktu_pengerjaan'  => 'required'
        ]);

        try {
            DB::beginTransaction();

            $task = new Task;
            $task->addNewTask($data);


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        set_response('Store data success');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function whereClassId($mstClassId, Request $request)
    {
        // dd($mstClassId);
        $data = $request->all();
        // try {
        $task = Task::select(DB::raw('MD5(id::text) as idx, mst_clas_id, mst_course_id, star_at, end_at, teacher_id, mst_type_task_id, mst_soal_id, descriptions'))
            ->with(['mst_clas_id' => function ($q) {
                $q->select('id', 'name');
            }])->with(['mst_course_id' => function ($q) {
                $q->select('id', 'code');
            }])->with(['teacher_id' => function ($q) {
                $q->select('id', 'name');
            }])->with(['mst_type_task_id' => function ($q) {
                $q->select('id', 'name');
            }])->with(['mst_soal_id' => function ($q) {
                $q->select('id', 'paket_soal');
            }])
            ->where(DB::raw('md5(mst_clas_id::text)'), $mstClassId)->get();
        // $task = Task::where($mstClassId);
        // } catch (\Throwable $th) {
        //     set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        // }

        if ($task->count() > 0) {
            set_response('Success retrieve data', $task);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function wherePengerjaanId($mstTaskId, Request $request)
    {
        // dd($mstClassId);
        $data = $request->all();
        // try {
        $task = Pengerjaan::select(DB::raw('MD5(id::text) as idx, mst_task_id, mst_student_id, nilai'))
            ->with(['mst_task_id' => function ($q) {
                $q->select('id');
            }])->with(['mst_student_id' => function ($q) {
                $q->select('id','name', 'nis');
            }])
            ->where(DB::raw('md5(mst_task_id::text)'), $mstTaskId)->get();
        // $task = Task::where($mstClassId);
        // } catch (\Throwable $th) {
        //     set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        // }

        if ($task->count() > 0) {
            set_response('Success retrieve data', $task);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
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

        $task = Task::where(DB::raw('md5(id::text)'), $id)->first();
        // dd($task);
        if (!$task)
            set_error_response('Data not Found', 'HTTP_NOT_FOUND');

        // validate incoming request
        $this->validate($request, [
            'mst_clas_id'       => 'required',
            'mst_course_id'     => 'required',
            'star_at'           => 'required',
            'end_at'            => 'required',
            'teacher_id'        => 'required',
            'mst_type_task_id'  => 'required',
            'mst_soal_id'       => 'required'
        ]);

        try {
            DB::beginTransaction();

            // $task = new Task;
            $task->editData($data);

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
        $task = Task::where(DB::raw('md5(id::text)'), $id)->first();

        if (!$task)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $task->delete();

        set_response('Delete data success');
    }
}
