<?php

namespace Uccello\Calendar;

use Illuminate\Database\Eloquent\Model;

class CalendarEntityEvent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'calendar_entity_events';

    protected $casts = [
        'events' => 'object',
    ];

    protected $fillable = [
        'entity_id',
        'module_id',
        'events'
    ];
}
