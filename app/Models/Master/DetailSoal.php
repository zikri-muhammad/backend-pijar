<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;


class DetailSoal extends Model
{
    use  SoftDeletes;

    protected $table = 'mst_detail_soal';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_soal_id',
        'nomor',
        'soal',
        'jawaban_a',
        'jawaban_b',
        'jawaban_c',
        'jawaban_d'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'mst_soal_id',
        'nomor',
        'soal',
        'jawaban_a',
        'jawaban_b',
        'jawaban_c',
        'jawaban_d'
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
        $query->select(DB::raw('MD5(id::text) as idx, mst_soal_id, nomor, soal, jawaban_a, jawaban_b, jawaban_c, jawaban_d'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        if ($id) {
            return $query->where(DB::raw('md5(id::text)'), $id)->first();
        }

        $isAll = false;

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

    public function editData($data) {
        $this->fill($data);
        $this->save();

        return $this->id;
    }
}
