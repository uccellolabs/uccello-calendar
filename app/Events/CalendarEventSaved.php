<?php

namespace Uccello\Calendar\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CalendarEventSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $calendarEvent;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($calendarEvent)
    {
        $this->calendarEvent = $calendarEvent;
    }
}
