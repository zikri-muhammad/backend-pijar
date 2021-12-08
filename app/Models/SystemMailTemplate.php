<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SystemMailTemplate extends Model {
    use  SoftDeletes;

    protected $table = 'system_mail_templates';    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
