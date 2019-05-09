<?php

namespace Uccello\Calendar;

use Illuminate\Database\Eloquent\Model;
use App\User;

class CalendarAccount extends Model
{
    public $fillable = [
        'user_id', 'service_name', 'username', 'token', 'refresh_token', 'expiration'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    public $casts = [
        'disabled_calendars' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
