<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use  App\User;
use DB;
use App\Models\Client\Teacher;
use App\Models\Master\Subject;
use Illuminate\Support\Facades\Input;


class TeachersController extends Controller
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
        $filters = filters($data);
        
        try {
            $data = Teacher::data($filters, $id);
        } catch (\Throwable $th) {
            dd($th);
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        if ( ! $id) {
            foreach ($data as $key => $value) {
                $data[$key]['mst_subject_id'] = '';
                
                $mst_subject_id = md5($value->mst_subject_id);;
    
                unset($data[$key]['mst_subject_id']);

                $data[$key]['mst_subject_id'] = $mst_subject_id;
                
                if ( ! empty($value->subject)) {
                    $data[$key]['subject']['idx'] = $mst_subject_id;
    
                    unset($data[$key]['subject']['id']);
                } 
            }
        } else {
            $mst_subject_id = md5($data['mst_subject_id']);
            $subject_name   = $data['subject']['name']; 

            $data['mst_subject_id'] = $mst_subject_id;

            unset($data['subject']);

            $data['subject'] = [
                'id'   => $mst_subject_id,
                'name' => $subject_name
            ];
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
            'nik'       => [
                'required', 'string', 'max:16', Rule::unique('teachers')->where('client_id', config('client_id'))
            ],
            'nip'       => [
                'required', 'string', 'max:16', Rule::unique('teachers')->where('client_id', config('client_id'))
            ],
            'name'       => 'required',
            'status'     => 'nullable',
            'subject_id' => 'required|string|max:255',
            'year'       => 'required|numeric|digits:4',
            'email'       => 'required|string|max:60',
            'dob'        => 'required|date'
        ], [
            'nik.required' => 'NIK wajib diisi!',
            'nip.required' => 'NIP wajib diisi!',
            'nama.required' => 'Nama guru wajib diisi!',
            'email.required' => 'Email guru wajib diisi!',
            'subject_id.required' => 'Mata pelajaran wajib diisi!',
            'year.required' => 'Tahun mengajar guru wajib diisi!',
            'dob.required' => 'Tanggal lahir guru wajib diisi!'
        ]);

        if (isset($data['subject_id'])) {
            $mst_subject_id = Subject::where(DB::raw('md5(id::text)'), $data['subject_id'])->first();

            if ( ! $mst_subject_id)
                set_error_response('Master class not found', 'HTTP_NOT_FOUND');

            unset($data['subject_id']);

            $data['mst_subject_id'] = $mst_subject_id->id;
        }

        unset($data['domain']);
        
        $data['client_id'] = config('client_id');

        try {
            DB::beginTransaction();            

            $teacher = new Teacher;
            $teacher->create($data);
            
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
        
        $teacher = Teacher::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $teacher)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        //validate incoming request 
        $this->validate($request, [
            'nik'       => [
                'required', 'string', 'max:16', Rule::unique('teachers')->whereNot('id', $teacher->id)->where('client_id', config('client_id'))
            ],
            'nip'       => [
                'required', 'string', 'max:16', Rule::unique('teachers')->whereNot('id', $teacher->id)->where('client_id', config('client_id'))
            ],
            'name'       => 'required',
            'status'     => 'nullable',
            'subject_id' => 'required|string|max:255',
            'year'       => 'required|numeric|digits:4',
            'email'       => 'required|string|max:60',
            'phone'       => 'required|string|max:20',
            'dob'        => 'required|date'
        ], [
            'nik.required' => 'NIK wajib diisi!',
            'nip.required' => 'NIP wajib diisi!',
            'nama.required' => 'Nama guru wajib diisi!',
            'subject_id.required' => 'Mata pelajaran wajib diisi!',
            'year.required' => 'Tahun mengajar guru wajib diisi!',
            'dob.required' => 'Tanggal lahir guru wajib diisi!',
            'phone.required' => 'Nomor telepon guru wajib diisi!',
            'email.required' => 'Email guru wajib diisi!',
        ]);

        
        try {
            DB::beginTransaction();
            $teacher->editData($data);
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
        $teacher = Teacher::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $teacher)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        try {
            DB::beginTransaction();

            $teacher->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            set_error_response('Internal server error', 'HTTP_INTERNAL_SERVER_ERROR');
        }

        set_response('Delete data success');
    } // delete a domain
}
