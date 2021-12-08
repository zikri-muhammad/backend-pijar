<?php

namespace App\Models\Client;

use DB;
use URL;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\Client\Headmaster;
use Illuminate\Support\Facades\Mail;


class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'license_id',
        'npsn',
        'school_name',
        'mst_province_id',
        'mst_regency_id',
        'mst_district_id',
        'mst_village_id',
        'address',
        'postal_code',
        'domain'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'license_id',
        'npsn',
        'school_name',
        'mst_province_id',
        'mst_regency_id',
        'mst_district_id',
        'mst_village_id',
        'address',
        'postal_code'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $dates = ['deleted_at'];


    public function scopeData($query, $filters, $id) {
        // Select statement
        $query->select(DB::raw('MD5(id::text) as idx, npsn, school_name, address, postal_code'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        if ($id) {
            return $query->whereCode($id)->first();
        }
     
        // Filters
        if ( ! empty($filters['search'])) {
            foreach ($filters['search'] as $key => $search) {
                if (in_array($search['column'], $this->searchableColumns)) {
                    $value     = ! empty($search['value']) ? $search['value'] : null; 

                    if ($search['column'] == 'is_activated') {
                        if ($value == 0) {
                            $query->whereNull('activated_at');
                        } else {
                            $query->whereNotNull('activated_at');
                        }
                    } else {
                        $condition = ! empty($search['condition_type']) ? $search['condition_type'] : 'contain';
                        
                        
                        if ( ! $value) {
                            $query->whereNull($search['column']);
                        } else {
                            if ($condition == 'contain') {
                                $query->where($search['column'], 'ilike', "%".$value."%");
                            } else {
                                $query->where($search['column'], $value);
                            }
                        }
                    }
                } else {
                    set_error_response('Search column "'.$search['column'].'" is not valid', 'HTTP_BAD_REQUEST');
                }
            }
        }

        // dd($query->toSql(), $query->getBindings());

        return $query->simplePaginate($filters['limit']);
    }

    public function editData($data, $id) {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function addNewClient($data, $license, $ipaddress) {
        $db = new \App\Service\DatabaseService;

        $provinceId = $db->findDecryptedId('mst_provinces', $data['province_id']);
        
        if ( ! $provinceId) {
            set_error_response('Province is not valid', 'HTTP_NOT_FOUND');
        }

        $regencyId  = $db->findDecryptedId('mst_regencies', $data['regency_id']);

        if ( ! $regencyId) {
            set_error_response('Regency is not valid', 'HTTP_NOT_FOUND');
        }

        $districtId = $db->findDecryptedId('mst_districts', $data['district_id']);

        if ( ! $districtId) {
            set_error_response('District is not valid', 'HTTP_NOT_FOUND');
        }
        
        $villageId  = $db->findDecryptedId('mst_villages', $data['village_id']);

        if ( ! $villageId) {
            set_error_response('Village is not valid', 'HTTP_NOT_FOUND');
        }
        
        // Add instance of Client
        $newClient = [
            'license_id'      => $license->id,
            'npsn'            => $data['npsn'],
            'school_name'     => $data['school_name'],
            'mst_province_id' => $provinceId,
            'mst_regency_id'  => $regencyId,
            'mst_district_id' => $districtId,
            'postal_code'     => $data['postal_code'],
            'address'         => $data['address'],
            'mst_village_id'  => $villageId,
            'domain'          => $data['school_domain']
        ];
        
        $new = new static;
        $new->fill($newClient);
        $new->save();
    
        $clientId = $new->id; 

        // Add new Headmaster 
        $newHeadmaster = [
            'client_id' => $clientId,
            'nip'       => $data['headmaster_nip'],
            'name'      => $data['headmaster_name'],
            'email'     => $data['headmaster_email'],
        ];

        $headmaster = new Headmaster;
        $headmaster->fill($newHeadmaster);
        $headmaster->save();

        // Add user's login
        $activationCode = md5(microtime());

        $newUser = [
            'client_id'   => $clientId,
            'entity_id'   => $headmaster->id,
            'identity_id' => $data['headmaster_nip'],
            'name'        => $data['headmaster_name'],
            'email'       => $data['headmaster_email'],
            'login'       => $data['headmaster_email'],
            'password'    => $data['headmaster_password'],
            'role_id'     => env('KEPSEK_ROLE_ID', 2),
            'ip_address'  => $ipaddress,
            'activation_code' => $activationCode
        ];
        
        $user = new User;
        $user->register($newUser);

        $payload = [
            'base_url_string' => URL::to('/'),
            'account_name' => $newUser['name'],
            'link' => URL::to('/').'/user/activate/'.$activationCode
        ];

        Mail::send('user.activation', $payload, function($message) use ($data) {
            $message->subject('Aktivasi Akun');
            $message->to($data['headmaster_email'], $data['headmaster_name']);
        });

        // Set license of being used
        $license->activated_at = date('Y-m-d H:i:s');
        $license->save();
    }

    public function license(){
        return $this->belongsTo(\App\Models\Client\License::class, 'license_id', 'id');
    }
}
