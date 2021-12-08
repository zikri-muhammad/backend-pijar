<?php

namespace App\Http\Controllers;

use App\Models\SystemMailTemplate;
use DB;
use URL;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client\Client;
use App\Models\Client\Student;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;



/**
 * Auth Controller
 *
 * @package     PIJAR App
 * @subpackage  Auth Controller
 * @author      Mohamad Febrian Mosii <febrianaries@gmail.com
 * 
 * !! Every function must be written in english     !!
 * !! Please read coding standards references below !!
 * @link https://www.laravelbestpractices.com/
 * @link https://codeigniter.com/userguide3/general/styleguide.html
 * !! End !!
 */

class AuthController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Request
     */
    public function index($id = '', Request $request)
    {
        // exit('ok');
        $data = $request->all();
        $filters = filters($data);

        $data = User::data($filters, $id);

        // dd($data);

        if ($data->count() > 0) {
            // Check is license is activated?
            if ($id && $data->activated_at) {
                set_error_response('Nis number already activated.', 'HTTP_NOT_ACCEPTABLE');
            }

            if ($id) {
                set_response('OK', $data);
            } else {
                set_response('Success to retrieve data.', $data);
            }
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    }

    public function withEmail($email, Request $request)
    {
        // exit($email);
        $data = $request->all();
        // Select statement
        $query = User::select(DB::raw('MD5(id::text) as idx, name, login, email, client_id'))

        // Relation
        ->with(['client' => function ($q) {
            $q->select('id', 'school_name', 'domain');
        }])

        ->where(DB::raw('email'), $email)->get();

        if ($query->count() > 0) {
                set_response('Success to retrieve data', $query);
            // }
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    }
    /**
     * Registration endpoint for Backend's Administrator
     *
     * @param   object  $request    Request Object
     * @return  JSON Response Status
     */
    public function registerAdmin(Request $request)
    {
        $rules = [
            'term_of_service' => 'required|rgte:1',
            'email'           => 'required|email|between:6,255|unique:users,email',
            'login'           => 'required|unique:users,login',
            'name'            => 'required',
            'password'        => ['required', 'confirmed', 'min:8', 'max:20', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ];

        $messages = [
            'login.unique'       => 'Login sudah digunakan!',
            'login.required'     => 'Login harus diisi',
            'email.unique'       => 'Email sudah digunakan!',
            'email.required'     => 'Email harus diisi',
            'password.required'  => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi Password tidak cocok',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.max'       => 'Password maksimal harus 8 karakter.',
            'password.regex'     => 'Password harus mengandung abjad, huruf kapital, dan numerik, contoh: (Indonesia1945)',
            'term_of_service.required' => 'Anda harus menyetujui Syarat dan Ketentuan Layanan!',
            'term_of_service.gte' => 'Anda harus menyetujui Syarat dan Ketentuan Layanan!',
        ];


        // Validate incoming request 
        $this->validate($request, $rules, $messages);

        $activationCode = md5(microtime());

        $data                    = $request->input();
        $data['activation_code'] = $activationCode;
        $data['ip_address']      = $request->ip();

        unset($data['password_confirmation']);

        try {
            DB::beginTransaction();

            $user = new User;
            $user->register($data);

            $payload = [
                'base_url_string' => URL::to('/'),
                'account_name' => $data['name'],
                'link' => URL::to('/') . '/activate/' . $activationCode
            ];

            Mail::send('user.activation', $payload, function ($message) use ($data) {
                $message->subject('Aktivasi Akun');
                $message->to($data['email'], $data['name']);
            });

            DB::commit();
            set_response("Registration success!");
        } catch (\Exception $th) {
            dd($th);
            set_error_response("Registration failed!");
        }
    }

    /**
     * Login endpoint for Backend's Administrator, and Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function auth(Request $request)
    {
        $data = $request->all();
        //validate incoming request 
        $this->validate($request, [
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);
    

        $credentials = array_merge($request->all(['email', 'password']));
        

        $users = User::where('email', $request->input('email'))->first();

        if (!$token = Auth::attempt($credentials)) {
            set_error_response('Invalid email or password', 'HTTP_UNAUTHORIZED');
        }

        $school_info = $users->client;

        $return = [
            'id'            => sha1($users->id),
            'name'          => $users->name,
            'email'         => $users->email,
            'mst_class_id'  => md5($users->mst_class_id),
            'role_id'       => $users->role_id,
            'role'          => $users->role->name,
            'role_code'     => $users->role->code,
            'school_info'   => [
                "license"         => $school_info->license->code,
                "npsn"            => $school_info->npsn,
                "school_name"     => $school_info->school_name,
                "mst_province_id" => $school_info->mst_province_id,
                "mst_regency_id"  => $school_info->mst_regency_id,
                "mst_district_id" => $school_info->mst_district_id,
                "mst_village_id"  => $school_info->mst_village_id,
                "address"         => $school_info->address,
                "postal_code"     => $school_info->postal_code,
                "deleted_at"      => $school_info->deleted_at,
                "domain"          => $school_info->domain
            ]
        ];

        set_response('Login success!', [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'data' => $return
        ]);
    }

    // auth kepala sekolah
    public function authHeadMaster(Request $request)
    {
        $data = $request->all();
        //validate incoming request 
        $this->validate($request, [
            'domains'    => 'required',
            'password' => 'required|string',
        ]);
        $credentials = array_merge($request->all(['domains', 'password']), ['client_id' => config('client_id')]);
        // dd($credentials);
        $users = User::where('domains', $request->input('domains'))->first();
        // dump($users);
        // dd(Auth::attempt($credentials));
        if (!$token = Auth::attempt($credentials)) {
            set_error_response('Invalid domain or password', 'HTTP_UNAUTHORIZED');
        }

        $school_info = $users->client;

        $return = [
            'id'            => sha1($users->id),
            'name'          => $users->name,
            'email'         => $users->email,
            'mst_class_id'  => md5($users->mst_class_id),
            'role_id'       => $users->role_id,
            'role'          => $users->role->name,
            'role_code'     => $users->role->code,
            'school_info'   => [
                "license"         => $school_info->license->code,
                "npsn"            => $school_info->npsn,
                "school_name"     => $school_info->school_name,
                "mst_province_id" => $school_info->mst_province_id,
                "mst_regency_id"  => $school_info->mst_regency_id,
                "mst_district_id" => $school_info->mst_district_id,
                "mst_village_id"  => $school_info->mst_village_id,
                "address"         => $school_info->address,
                "postal_code"     => $school_info->postal_code,
                "deleted_at"      => $school_info->deleted_at,
                "domain"          => $school_info->domain
            ]
        ];

        set_response('Login success!', [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'data' => $return
        ]);
    }

    /**
     * Activate user's account
     *
     * @param   string  $code    Activation Code
     * @return  JSON Response Status
     */
    public function activation($code)
    {
        if (!$code)
            exit('Sorry there is nothing to do here or your URL is invalid');

        try {
            DB::beginTransaction();

            $user = User::whereActivationCode($code)->whereNull('activated_at')->first();

            if (!$user)
                exit('Sorry there is nothing to do here or your URL is invalid');


            $user->activated_at    = date('Y-m-d H:i:s');
            $user->activation_code = NULL;
            $user->save();

            DB::commit();

            echo "Your account is successfully activated, <a href='" . URL::to('/') . "'>click here to login</a>";
        } catch (\Exception $th) {
            exit('Sorry there is nothing to do here or your URL is invalid!');
        }
    }

    public function forgetPassword(Request $request)
    {
        $data = $request->all();
        // dd($data);

        $this->validate($request, [
            'email'    => 'required|string',
        ]);
        $credentials = array_merge($request->all(['email']), ['client_id' => config('client_id')]);
        $users = User::where('email', $request->input('email'))->first();

        $return = [
            'email' => $users->email
        ];

        set_response('success!', [
            'data' => $return

        ]);
    }

    public function checkdomain(Request $request)
    {
        $data = $request->all();
        // dd($data);

        $this->validate($request, [
            'domain' => 'required|string'
        ]);

        $client = Client::where('domain', $request->only('domain'))->first();

        if (!$client) {
            set_error_response('Invalid Domain', 'HTTP_UNAUTHORIZED');
        }

        $return = [
            'school_name' => $client['school_name'],
            'npsn'  => $client['npsn'],
            'domain' => $client['domain']
        ];

        set_response('domain cocok', [
            'data' => $return
        ]);
    }

    // check ativation account 
    public function checkAktivationAccount(Request $request)
    {
        // $data = $request->all();

        $this->validate($request, [
            'email'  => 'required',
        ]);

        $student = Student::where('email', $request->only('email'))->first();
        // dd($request->only('email'));

        if (!$student) {
            set_error_response('Invalid Email', 'HTTP_UNAUTHORIZED');
        }

        $return = [
            'nis'           => $student['nis'],
            'name'          => $student['name'],
            'email'         => $student['email'],
            'class_name'    => $student['class_name'],
            'client_id'     => $student['client_id'],
        ];

        set_response('success !!', [
            'data' => $return
        ]);
    }

    // activation account
    public function aktivationAccount(Request $request)
    {
        // $data = $request->all();

        $rules = [
            'term_of_service' => 'required',
            'email'           => 'required|email|between:6,255|unique:users,email',
            'name'            => 'required',
            'password'        => ['required', 'confirmed', 'min:8', 'max:20', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ];

        $messages = [
            'login.unique'       => 'Login sudah digunakan!',
            'login.required'     => 'Login harus diisi',
            'email.unique'       => 'Email sudah digunakan!',
            'email.required'     => 'Email harus diisi',
            'password.required'  => 'Password harus diisi',
            'password.confirmed' => 'Konfirmasi Password tidak cocok',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.max'       => 'Password maksimal harus 8 karakter.',
            'password.regex'     => 'Password harus mengandung abjad, huruf kapital, dan numerik, contoh: (Indonesia1945)',
            'term_of_service.required' => 'Anda harus menyetujui Syarat dan Ketentuan Layanan!',
            'term_of_service.gte' => 'Anda harus menyetujui Syarat dan Ketentuan Layanan!',
        ];


        // Validate incoming request 
        $this->validate($request, $rules, $messages);

        try {
            DB::beginTransaction();

            $activationCode = md5(microtime());

            $data = new User;
            $data->name = $request->input('name');
            $data->login = $request->input('email');
            $data->email = $request->input('email');
            $data->client_id = $request->input('client_id');
            $data->role_id = 3;
            $data->activation_code = $activationCode;
            $data->ip_address = $request->ip();
            $data->password = app('hash')->make($request->input('password'));
            unset($data['password_confirmation']);


            $data->save();

            DB::commit();
            set_response("Registration success!");
        } catch (\Exception $th) {
            dd($th);
            set_error_response("Registration failed!");
        }
    }
}
