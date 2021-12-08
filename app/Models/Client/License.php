<?php

namespace App\Models\Client;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use SoftDeletes;

    protected $table = 'licenses';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'limit_users',
        'activation_time_from',
        'activation_time_to',
        'activated_at',
        'expired_at',
        'price'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'code',
        'is_activated',
        'limit_users',
        'activation_time_from',
        'activation_time_to',
        'activated_at',
        'expired_at',
        'price'
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
        $query->select(DB::raw('code, price, limit_users, expired_at, activated_at'));

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
}
