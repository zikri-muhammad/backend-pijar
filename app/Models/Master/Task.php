<?php

namespace App\Models\Master;

use App\Models\Client\Teacher;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\ClassList;
use App\Models\Master\TypeTask;
use App\Models\Master\Soal;
use App\Models\Master\CourseList;


class Task extends Model
{
    use  SoftDeletes;

    protected $table = 'mst_task';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_clas_id',
        'mst_course_id',
        'star_at',
        'end_at',
        'teacher_id',
        'mst_type_task_id',
        'mst_soal_id',
        'descriptions',
        'mst_jenis_task',
        'limit_pengerjaan'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'mst_clas_id',
        'mst_course_id',
        'star_at',
        'end_at',
        'teacher_id',
        'mst_type_task_id',
        'mst_soal_id',
        'descriptions',
        'mst_jenis_task',
        'limit_pengerjaan'
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
        $query->select(DB::raw('MD5(id::text) as idx, mst_clas_id, mst_course_id, star_at, end_at, teacher_id, mst_type_task_id, mst_soal_id, descriptions, limit_pengerjaan, mst_jenis_task'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        // Relation
        $query->with(['mst_clas_id' => function ($q) {
            $q->select('id', 'name', 'code');

        }]);
        $query->with(['mst_course_id' => function ($q) {
            $q->select('id', 'code');
        }]);
        $query->with(['teacher_id' => function ($q) {
            $q->select('name');
            // $q->select(DB::raw('MD5(id::text) as idx, name'));
        }]);
        $query->with(['mst_type_task_id' => function ($q) {
            $q->select('id', 'name');

        }]);
        $query->with(['mst_soal_id' => function ($q) {
            $q->select('id', 'paket_soal');
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

    public function addNewTask($data)
    {
        $db = new \App\Service\DatabaseService;

        $mst_clas_id = $db->findDecryptedId('mst_classes', $data['mst_clas_id']);
        // dd($mst_clas_id);
        if (!$mst_clas_id) {
            set_error_response('Kelas is not valid', 'HTTP_NOT_FOUND');
        }

        $mst_course_id = $db->findDecryptedId('mst_courses', $data['mst_course_id']);

        if (!$mst_course_id) {
            set_error_response('Course is not valid', 'HTTP_NOT_FOUND');
        }

        $mst_soal_id = $db->findDecryptedId('mst_soal', $data['mst_soal_id']);

        if (!$mst_soal_id) {
            set_error_response('Soal is not valid', 'HTTP_NOT_FOUND');
        }

        // Add instance of Client
        $newTask = [
            'mst_clas_id'       => $mst_clas_id,
            'mst_course_id'     => $mst_course_id,
            'star_at'           => $data['star_at'],
            'end_at'            => $data['end_at'],
            'teacher_id'        => $data['teacher_id'],
            // 'mst_type_task_id'  => $data['mst_type_task_id'],
            'mst_soal_id'       => $mst_soal_id,
            'descriptions'      => $data['descriptions'],
            'mst_jenis_task'    => $data['mst_jenis_task'],
            'limit_pengerjaan'  => $data['waktu_pengerjaan']

        ];

        $new = new Task();
        $new->fill($newTask);
        $new->save();
    }

    public function editData($data)
    {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function mst_clas_id()
    {
        return $this->belongsTo(ClassList::class, 'mst_clas_id', 'id');
    }

    public function mst_course_id()
    {
        return $this->belongsTo(CourseList::class, 'mst_course_id', 'id');
    }

    public function teacher_id()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function mst_type_task_id()
    {
        return $this->belongsTo(TypeTask::class, 'mst_type_task_id', 'id');
    }

    public function mst_soal_id()
    {
        return $this->belongsTo(Soal::class, 'mst_soal_id', 'id');
    }
}
