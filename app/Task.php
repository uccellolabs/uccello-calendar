<?php

namespace Uccello\Calendar;

use Illuminate\Database\Eloquent\SoftDeletes;
use Uccello\Core\Database\Eloquent\Model;

class Task extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vtiger_id'
    ];

    public function assigned_user()
    {
        return $this->belongsTo(\Uccello\Core\Models\User::class);
    }

    /**
    * Returns record label
    *
    * @return string
    */
    public function getRecordLabelAttribute() : string
    {
        return $this->subject;
    }
}
