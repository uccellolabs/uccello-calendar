<?php

namespace Uccello\Calendar;

use Illuminate\Database\Eloquent\Model;

class CalendarConfig extends Model
{
    public $fillable = [
        'domain_id', 'user_id', 'data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    protected $casts = [
        'data' => 'object',
    ];
}
