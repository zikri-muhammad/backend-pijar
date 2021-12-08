<?php
namespace App\Models\Master;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use  SoftDeletes;

    protected $table = 'mst_provinces';    

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

    protected $dates = ['deleted_at'];


    public function scopeData($query, $filters, $id) {
        // Select statement
        $query->select(DB::raw('MD5(id::text) as idx, code, name'));

        // Order
        $query->orderBy($filters['order_by'], $filters['order_type']);

        if ($id) {
            return $query->where(DB::raw('md5(id::text)'), $id)->first();
        }

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
                        } else {
                            $query->where($search['column'], $value);
                        }
                    }
                } else {
                    set_error_response('Search column "'.$search['column'].'" is not valid', 'HTTP_BAD_REQUEST');
                }
            }
        }

        return $query->paginate($filters['limit']);
    }

    public function editData($data, $id) {
        $this->fill($data);
        $this->save();

        return $this->id;
    }
}
