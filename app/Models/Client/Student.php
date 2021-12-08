<?php

namespace App\Models\Client;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use SoftDeletes;

    protected $table = 'students';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_class_id',
        'class_name',
        'client_id',
        'name',
        'nis',
        'nisn',
        'phone',
        'email',
        'dob',
        'is_activated',
        'gender',
        'activated_at'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'client_id',
        'nis',
        'nisn',
        'class_name',
        'phone',
        'gender',
        'email',
        'dob',
        'is_activated',
        'activated_at'
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
        $query->select(DB::raw('MD5(id::text) as idx, gender, mst_class_id, class_name, nis, nisn, name, phone, email, dob, is_activated, activated_at'));

        $query->with(['class' => function($q) {
            $q->select('id', 'name');
        }]);
        
        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);
        
        if ($id) {
            return $query->where(DB::raw('md5(id::text)'), $id)->first();
        }
     
                // Filters
        if ( ! empty($filters['search'])) {
            foreach ($filters['search'] as $key => $search) {
                if ($search['column'] == 'all') {
                    $value     = ! empty($search['value']) ? $search['value'] : null; 

                    $query->where(function($q) use ($value) {
                        foreach ($this->searchableColumns as $key => $field) {
                            $q->orWhere($field, 'ilike', '%'.$value.'%');
                        }
                    });
                } else {
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
                                } else if ($condition == 'equal_enc') {
                                    $isAll = true;
                                    
                                    $query->where(DB::raw('md5('.$search['column'].'::text)'), $value);
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
        }

        return $query->paginate($filters['limit']);
    }

    public function editData($data) {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function class(){
        return $this->belongsTo(\App\Models\Master\ClassList::class, 'mst_class_id', 'id');
    }
}
