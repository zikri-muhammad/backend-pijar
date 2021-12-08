<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use App\Models\Master\ClassList;
use App\Models\Master\Subject;
use App\Models\Client\Teacher;


class LiveSesion extends Model
{
    use SoftDeletes;

    protected $table = 'mst_live_session';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mst_class_id',
        'mst_subject_id',
        'link_meeting',
        'tanggal_meeting',
        'descriptions',
        'teacher_id'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchableColumns = [
        'mst_class_id',
        'mst_subject_id',
        'link_meeting',
        'tanggal_meeting',
        'descriptions',
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
        $query->select(DB::raw('MD5(id::text) as idx, mst_class_id, mst_subject_id, link_meeting, tanggal_meeting, teacher_id, descriptions'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        // Relation
        $query->with(['mst_class_id' => function ($q) {
            $q->select('id', 'name', 'code');

        }]);
        $query->with(['mst_subject_id' => function ($q) {
            $q->select('id', 'code');
        }]);
        $query->with(['teacher_id' => function ($q) {
            $q->select('name');
            // $q->select(DB::raw('MD5(id::text) as idx, name'));
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

    // crete 
    public function addNewLiveSession($data)
    {
        $db = new \App\Service\DatabaseService;

        $mst_clas_id = $db->findDecryptedId('mst_classes', $data['mst_class_id']);
        // dd($mst_clas_id);
        if (!$mst_clas_id) {
            set_error_response('Kelas is not valid', 'HTTP_NOT_FOUND');
        }

        $mst_subject_id = $db->findDecryptedId('mst_subjects', $data['mst_subject_id']);
        // dd($mst_subject_id);

        if (!$mst_subject_id) {
            set_error_response('Subject is not valid', 'HTTP_NOT_FOUND');
        }

        // Add instance of Client
        $newSubject = [
            'mst_class_id'       => $mst_clas_id,
            'mst_subject_id'     => $mst_subject_id,
            'link_meeting'       => $data['link_meeting'],
            'tanggal_meeting'    => $data['tanggal_meeting'],
            'teacher_id'         => $data['teacher_id'],
            'descriptions'       => $data['descriptions'],

        ];

        $new = new LiveSesion();
        $new->fill($newSubject);
        $new->save();
    }

    // edit 
    public function editData($data)
    {
        $this->fill($data);
        $this->save();

        return $this->id;
    }

    public function mst_class_id()
    {
        return $this->belongsTo(ClassList::class, 'mst_class_id', 'id');
    }

    public function mst_subject_id()
    {
        return $this->belongsTo(Subject::class, 'mst_subject_id', 'id');
    }

    public function teacher_id()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}
