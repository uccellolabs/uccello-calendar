<?php

namespace Uccello\Calendar;

use Illuminate\Database\Eloquent\Model;

class EntityEvent extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entity_events';

    protected $casts = [
        'events' => 'array',
    ];

    protected $fillable=[
        'entity_id', 'entity_class', 'events'
    ];
}
