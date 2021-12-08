<?php

namespace App\Models\Master;

use App\Models\Client\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;


class Pengerjaan extends Model
{
    use  SoftDeletes;

    protected $table = 'mst_pengerjaan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_task_id',
        'mst_student_id',
        'nilai'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'mst_task_id',
        'mst_student_id',
        'nilai'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function scopeData($query, $filters, $id)
    {
        // Select statement
        $query->select(DB::raw('MD5(id::text) as idx, mst_task_id, mst_student_id, nilai'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        // Relation
        $query->with(['mst_student' => function ($q) {
            // dd($q);
            // $q->select('id', 'name');
            // $q->select('MD5(id::text) as idx, name');
            $q->select('id', 'name', 'nis', 'nisn');
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
            return $query->simplePaginate($filters['limit']);
        }
    }

    public function editData($data)
    {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function mst_student()
    {
        return $this->belongsTo(Student::class, 'mst_student_id', 'id');
    }

    public function mst_task_id()
    {
        return $this->belongsTo(Task::class, 'mst_task_id', 'id');
    }

    public function mst_student_id()
    {
        return $this->belongsTo(Student::class, 'mst_student_id', 'id');
    }
}
