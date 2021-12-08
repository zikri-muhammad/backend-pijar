<?php

namespace App\Models;

use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'login',
        'email',
        'domain',
        'client',
        'role_id',
        'ip_address',
        'password_confirmation',
        'activation_code',
        'activated_at',
        'reset_password_code', 
        'identity_id',
        'client_id',
        'entity_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function register($data) {
        $data['password'] = app('hash')->make($data['password']);
        
        $this->create($data);        
    }

    public function scopeData($query, $filters, $id)
    {
        // Select statement
        $query->select(DB::raw('MD5(id::text) as idx, name, login, email, client_id'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        // Relation
        $query->with(['client' => function ($q) {
            $q->select('id', 'school_name', 'domain');
        }]);


        if ($id) {
            return $query->where(DB::raw('md5(id::text)'), $id)->first();
        }

        $isAll = false;

        // Filters
        if (!empty($filters['search'])) {
            foreach ($filters['search'] as $key => $search) {
                if (in_array($search['column'], $this->searchableColumns)) {
                    $value     = !empty($search['value']) ? $search['value'] : null;
                    $condition = !empty($search['condition_type']) ? $search['condition_type'] : 'contain';

                    if (!$value) {
                        $query->whereNull($search['column']);
                    } else {
                        if ($condition == 'contain') {
                            $query->where($search['column'], 'ilike', "%" . $value . "%");
                        } else if ($condition == 'equal_enc') {
                            $isAll = true;

                            $query->where(DB::raw('md5(' . $search['column'] . '::text)'), $value);
                        } else {
                            $query->where($search['column'], $value);
                        }
                    }
                } else {
                    set_error_response('Search column "' . $search['column'] . '" is not valid', 'HTTP_BAD_REQUEST');
                }
            }
        }

        if ($isAll == true) {
            return $query->get();
        } else {
            return $query->paginate($filters['limit']);
        }
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role(){
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function client(){
        return $this->belongsTo(\App\Models\Client\Client::class, 'client_id', 'id');
    }
}
