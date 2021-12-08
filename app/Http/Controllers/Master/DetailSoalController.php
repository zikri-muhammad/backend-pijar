<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\DetailSoal;
use Illuminate\Http\Request;
use DB;

class DetailSoalController extends Controller
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

        try {
            $data = DetailSoal::all();
            // dump($data);
        } catch (\Throwable $th) {
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ($data->count() > 0) {
            // $json = json_decode($data);
            // var_dump($json);die;
            // for($x = 0; $x < count($data); $x++) {
            //     // $id = $data[$x]->id;
            //     $idx = $data{$x}->id;
            //     // $soal[] = $data[$x]->soal;
            // }
            // print_r($idx);die;
            // $result = [
            //     'data' => $data,
            //     // 'soal' => $soal
            // ];
            // die;
        // header('Content-Type: application/json');

        //     echo json_encode($result);exit;
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
            'mst_soal_id' => 'required',
            'nomor' => 'required',
            'soal' => 'required',
            'jawaban_a' => 'required',
            'jawaban_b' => 'required',
            'jawaban_c' => 'required',
            'jawaban_d' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $detailSoal = new DetailSoal;
            $detailSoal->create($data);

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

        $detailSoal = DetailSoal::where(DB::raw('md5(id::text)'), $id)->first();
        // dd($detailSoal);
        if(! $detailSoal)
            set_error_response('Data not Found', 'HTTP_NOT_FOUND');

        // validate incoming request
        $this->validate($request, [
            'mst_soal_id' => 'required',
            'nomor' => 'required',
            'soal' => 'required',
            'jawaban_a' => 'required',
            'jawaban_b' => 'required',
            'jawaban_c' => 'required',
            'jawaban_d' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // $detailSoal = new soal;
            $detailSoal->editData($data);

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
        $detailSoal = DetailSoal::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $detailSoal)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $detailSoal->delete();

        set_response('Delete data success');
    }
}
