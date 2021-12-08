<?php

namespace App\Http\Controllers\Client;

use DB;
use App\Http\Controllers\Controller;
use App\Service\SchemaService;
use App\Models\Client\License;
use App\Models\Client\Client;
use App\Models\MasterProvince;
use App\Models\MasterRegency;
use App\Models\MasterDistrict;
use App\Models\MasterVillage;
use Illuminate\Http\Request;

class LicensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Request
     */
    public function index($id = '', Request $request)
    {
        $data = $request->all();
        $filters = filters($data);
        
        $data = License::data($filters, $id);

        if ($data->count() > 0 ) {
            // Check is license is activated?
            if ($id && $data->activated_at) {
                set_error_response('License number already activated.', 'HTTP_NOT_ACCEPTABLE');
            }

            // Check is license is expired?
            if ($id && $data->expired_at < date('Y-m-d H:i:s')) {
                set_error_response('License number already expired.', 'HTTP_NOT_ACCEPTABLE');
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

    /**
     * Store a newly created resource in storage.
     *
     * @    param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        //validate incoming request 
        $this->validate($request, [
            'limit_users' => 'required|numeric',
            'activation_time_from' => 'required|date|date_format:Y-m-d',
            'activation_time_to' => 'required|date|date_format:Y-m-d|after_or_equal:activation_time_from',
            'price' => 'required|numeric'
        ]);

        do {
            $randomCode = generateRandomString();
        } while (License::whereCode($randomCode)->count() > 0);
        
        $data['code'] = $randomCode;
        $data['expired_at'] = $data['activation_time_to'];
        $province = new License;
        
        $province->create($data);

        set_response('Store data success', ['code' => $randomCode]);    
    }

    /**
     * Activate license code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request)
    {
        $data = $request->all();

        $messages = [
            'headmaster_email.unique'       => 'Email sudah digunakan!',
            'headmaster_email.required'     => 'Email harus diisi',
            'headmaster_password.required'  => 'Password harus diisi',
            'headmaster_password.confirmed' => 'Konfirmasi Password tidak cocok',
            'headmaster_password.min'       => 'Password minimal harus 8 karakter.',
            'headmaster_password.max'       => 'Password maksimal harus 8 karakter.',
            'headmaster_password.regex'     => 'Password harus mengandung abjad, huruf kapital, dan numerik, contoh: (Indonesia1945)',
            'term_of_service.required'      => 'Anda harus menyetujui Syarat dan Ketentuan Layanan!',
            'term_of_service.gte'           => 'Anda harus menyetujui Syarat dan Ketentuan Layanan!',
            'school_domain.alpha_num'       => 'Domain sekolah hanya boleh berisi huruf dan angka (tanpa spasi)!',
            'school_domain.unique'          => 'Domain sekolah sudah digunakan!',
        ];

        //validate incoming request 
        $this->validate($request, [
            'term_of_service'     => 'required|gte:1',
            'code'                => 'required|max:25',
            'activated_at'        => 'required|date',
            'npsn'                => 'required|numeric',
            'school_name'         => 'required',
            'province_id'         => 'required',
            'regency_id'          => 'required',
            'district_id'         => 'required',
            'village_id'          => 'required',
            'postal_code'         => 'required',
            'address'             => 'required',
            'headmaster_nip'      => 'required',
            'headmaster_name'     => 'required',
            'headmaster_email'    => 'required|email',
            'headmaster_password' => ['required','confirmed','min: 8','max: 20','regex: /[a-z]/', 'regex: /[A-Z]/', 'regex: /[0-9]/'],
            'headmaster_password_confirmation' => 'required',
            'school_domain'       => 'required|string|max:30|alpha_num|unique:clients,domain'
        ], $messages);

        $licenseCode = License::whereCode($data['code'])->first();

        if ($licenseCode) {
            // Check is license is activated?
            if ($licenseCode->activated_at) {
                set_error_response('License number already activated.', 'HTTP_NOT_ACCEPTABLE');
            }

            // Check is license is expired?
            if ($licenseCode->expired_at < date('Y-m-d H:i:s')) {
                set_error_response('License number already expired.', 'HTTP_NOT_ACCEPTABLE');
            }
    
            try {
                DB::beginTransaction();

                $client = new Client;
                $client->addNewClient($data, $licenseCode, $request->ip());
                
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                set_error_response('Error occurred', 'HTTP_NOT_FOUND');
            }

            set_response('Activation Success', $data);
        } else {
            set_error_response('No record can be displayed', 'HTTP_NOT_FOUND');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
