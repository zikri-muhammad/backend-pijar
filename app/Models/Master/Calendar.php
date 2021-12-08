<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use App\Models\Master\CourseList;
use App\Models\Client\Teacher;


class Calendar extends Model
{
    use SoftDeletes;
    protected $table = 'calender';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kategori_id',
        'mst_course_id',
        'name_aktivitas',
        'tanggal_aktivitas',
        'teacher_id'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'kategori_id',
        'mst_course_id',
        'name_aktivitas',
        'tanggal_aktivitas',
        'teacher_id'
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
        $query->select(DB::raw('MD5(id::text) as idx, mst_course_id, name_aktivitas, tanggal_aktivitas, teacher_id'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        // Relation

        $query->with(['mst_course_id' => function ($q) {
            $q->select('id', 'code');
        }]);
        $query->with(['teacher_id' => function ($q) {
            $q->select('name');
        }]);
        // $q->select(DB::raw('MD5(id::text) as idx, name'));

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

    public function addNewCalendar($data)
    {
        $db = new \App\Service\DatabaseService;

        $mst_course_id = $db->findDecryptedId('mst_courses', $data['mst_course_id']);
// dd($mst_course_id);
        if (!$mst_course_id) {
            set_error_response('Course is not valid', 'HTTP_NOT_FOUND');
        }

        // Add instance of Client
        $newCalendar = [
            'kategori_id'       => $data['kategori_id'],
            'mst_course_id'     => $mst_course_id,
            'name_aktivitas'    => $data['name_aktivitas'],
            'tanggal_aktivitas' => $data['tanggal_aktivitas'],
            'teacher_id'        => $data['teacher_id']

        ];

        $new = new Calendar();
        $new->fill($newCalendar);
        $new->save();
    }

    public function editData($data)
    {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function mst_course_id()
    {
        return $this->belongsTo(CourseList::class, 'mst_course_id', 'id');
    }

    public function teacher_id()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}
