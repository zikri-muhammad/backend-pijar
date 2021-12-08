<?php

namespace App\Http\Controllers\Client;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\User;
use Illuminate\Validation\Rule;
use App\Models\Client\Student;
use App\Models\Master\ClassList;
use Illuminate\Support\Facades\Input;


class StudentsController extends Controller
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
        
        $data = Student::data($filters, $id);

        if ( ! $id) {
            foreach ($data as $key => $value) {
                $data[$key]['mst_class_id'] = '';
                $data[$key]['class_name']   = $value->class_name ? $value->class_name : '-';
                
                $mst_class_id = md5($value->mst_class_id);;
    
                unset($data[$key]['mst_class_id']);
                
                if ( ! empty($value->class)) {
                    $data[$key]['class']['idx'] = $mst_class_id;
    
                    unset($data[$key]['class']['id']);
                } 
            }
        } else {
            $mst_class_id = md5($data['mst_class_id']);
            $class_name   = $data['class']['name']; 

            $data['mst_class_id'] = $mst_class_id;

            unset($data['class']);

            $data['class'] = [
                'id'   => $mst_class_id,
                'name' => $class_name
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

        // Validate incoming request 
        $data['nis']  = ! empty($data['nis']) ? $data['nis'] : '';
        $data['nisn'] = ! empty($data['nisn']) ? $data['nisn'] : '';

        $this->validate($request, [
            'nis'       => [
                'required', 'string', 'max:16', Rule::unique('students')->where('client_id', config('client_id'))
            ],
            'nisn'       => [
                'required', 'string', 'max:16', Rule::unique('students')->where('client_id', config('client_id'))
            ],
            'name'     => 'required|string|max:40',
            'phone'     => 'required|string|max:15',
            'email'     => 'required|email|max:50',
            'dob'       => 'required|date',
            'gender'    => 'required|in:female,male',
            'mst_class_id'    => 'required|string|max:50',
            'class_name'    => 'required|string|max:20'
        ], [
            'nis.unique' => "NIS sudah terpakai oleh siswa lain.",
            'nisn.unique' => "NISN sudah terpakai oleh siswa lain.",
            'name.required' => 'Nama Siswa wajib diisi!',
            'phone.required' => 'Nomor Telepon wajib diisi!',
            'email.required' => 'Email wajib diisi!',
            'dob.required' => 'Tanggal Lahir wajib diisi!',
            'gender.required' => 'Jenis Kelamin wajib diisi!',
            'nis.required' => 'NIS wajib diisi!',
            'nisn.required' => 'NISN wajib diisi!',
            'mst_class_id.required' => 'Tingkat Kelas wajib diisi!',
            'class_name.required' => 'Nama Kelas wajib diisi!'
        ]);

        unset($data['domain']);

        // Check Class

        $mst_class_id = ClassList::where(DB::raw('md5(id::text)'), $data['mst_class_id'])->first();

        if ( ! $mst_class_id)
            set_error_response('Master class not found', 'HTTP_NOT_FOUND');

            
        $data['client_id']    = config('client_id');
        $data['mst_class_id'] = $mst_class_id->id;
        
        $student = new Student;
        $student->create($data);

        set_response('Store data success');
    } 

    /**
     * Update data.
     *
     * @return void
     */
    public function update(Request $request, $id){
        $data = $request->all();
        
        $student = Student::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $student)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $id = $student->id;

        //validate incoming request 
        $this->validate($request, [
            'nis'       => [
                'required', 'string', 'max:16', Rule::unique('students')->where('id', '!=', $id)->where('client_id', config('client_id'))
            ],
            'nisn'       => [
                'required', 'string', 'max:16', Rule::unique('students')->where('id', '!=', $id)->where('client_id', config('client_id'))
            ],
            'phone'     => 'required|string|max:15',
            'email'     => 'required|email|max:50',
            'dob'       => 'required|date',
            'gender'    => 'required|in:female,male',
            'mst_class_id'    => 'required|string|max:50',
            'class_name'    => 'required|string|max:20'
        ], [
            'nis.unique' => "NIS sudah terpakai oleh siswa lain.",
            'nisn.unique' => "NISN sudah terpakai oleh siswa lain.",
            'name.required' => 'Nama Siswa wajib diisi!',
            'phone.required' => 'Nomor Telepon wajib diisi!',
            'email.required' => 'Email wajib diisi!',
            'dob.required' => 'Tanggal Lahir wajib diisi!',
            'gender.required' => 'Jenis Kelamin wajib diisi!',
            'nis.required' => 'NIS wajib diisi!',
            'nisn.required' => 'NISN wajib diisi!',
            'mst_class_id.required' => 'Tingkat Kelas wajib diisi!',
            'class_name.required' => 'Nama Kelas wajib diisi!'
        ]);

        $mst_class_id = ClassList::where(DB::raw('md5(id::text)'), $data['mst_class_id'])->first();

        if ( ! $mst_class_id)
            set_error_response('Master class not found', 'HTTP_NOT_FOUND');

        $data['mst_class_id'] = $mst_class_id->id;
        
        $student->editData($data);

        set_response('Update data success');
    } // handle show edit page POST

    /**
     * Delete data.
     *
     * @return void
     */
    public function destroy($id =''){
        $student = Student::where(DB::raw('md5(id::text)'), $id)->first();

        if ( ! $student)
            set_error_response('Data not found', 'HTTP_NOT_FOUND');

        $student->delete();

        set_response('Delete data success');
    } // delete a domain
}
