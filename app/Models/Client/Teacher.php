<?php

namespace App\Models\Client;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use SoftDeletes;

    protected $table = 'teachers';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'nik',
        'nip',
        'name',
        'email',
        'year',
        'phone',
        'status',
        'mst_subject_id',
        'dob',
        'is_activated',
        'activated_at'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'client_id',
        'nik',
        'nip',
        'name',
        'phone',
        'status',
        'email',
        'mst_subject_id',
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
        $query->select(DB::raw('MD5(id::text) as idx, mst_subject_id, email, nik, nip, name, status, dob, is_activated, activated_at, year, phone'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        // Relation
        $query->with(['subject' => function($q) {
            $q->select('id', 'name');
        }]);
        
        // Filter by client
        $query->whereClientId(config('client_id'));
        
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
        }

        return $query->paginate($filters['limit']);
    }

    public function editData($data) {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function subject(){
        return $this->belongsTo(\App\Models\Master\Subject::class, 'mst_subject_id', 'id');
    }
}
