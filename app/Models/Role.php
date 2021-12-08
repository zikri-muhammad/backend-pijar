<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'user_roles';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'name',
        'code'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function scopeData($query, $filters, $id) {
        // Select statement
        $query->select(DB::raw('MD5(id::text) as idx, code, name'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        $isAll = true;

        // Filters
        if ( ! empty($filters['search'])) {
            foreach ($filters['search'] as $key => $search) {
                if (in_array($search['column'], $this->searchableColumns)) {
                    $value     = ! empty($search['value']) ? $search['value'] : null; 
                    $condition = ! empty($search['condition_type']) ? $search['condition_type'] : 'contain';
                    
                    
                    if ( ! $value) {
                        $query->whereNull($search['column']);
                    } else {
                        if ($condition == 'contain') {
                            $query->where($search['column'], 'ilike', "%".$value."%");
                        } else if ($condition == 'equal_enc') {
                            $isAll = true;
                            
                            $query->where(DB::raw('md5('.$search['column'].'::text)'), $value);
                        } else {
                            $query->where($search['column'], $value);
                        }
                    }
                } else {
                    set_error_response('Search column "'.$search['column'].'" is not valid', 'HTTP_BAD_REQUEST');
                }
            }
        }
        
        if ($isAll == true) {
            return $query->get();
        } else {
            return $query->simplePaginate($filters['limit']);
        }
    }
}
