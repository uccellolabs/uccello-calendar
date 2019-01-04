<?php

namespace Uccello\Calendar;

use Illuminate\Database\Eloquent\Model;

class CalendarToken extends Model
{
    public $fillable = [
        'user_id', 'service_name', 'token', 'refresh_token', 'expiration'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
