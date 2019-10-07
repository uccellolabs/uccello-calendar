<?php

namespace Uccello\Calendar\Listeners;

use Uccello\Calendar\Events\CalendarEventSaved;
use Uccello\Calendar\CalendarEntityEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveCalendarEntityEvent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CalendarEventSaved  $event
     * @return void
     */
    public function handle(CalendarEventSaved $e)
    {
        $event = $e->calendarEvent;
        if($event->moduleName && $event->recordId)
        {

            
            $entityevent = CalendarEntityEvent::firstOrNew([
                'entity_id' => $event->recordId,
                'module_id' => ucmodule($event->moduleName)->id]);
            if(!$entityevent->events || $entityevent==null)
            {
                $minifiyed_event = new \StdClass;
                $minifiyed_event->id = $event->id;
                $minifiyed_event->calendarId = $event->calendarId;
                $minifiyed_event->calendarType = $event->calendarType;
                $minifiyed_event->accountId = $event->accountId;

                $array = [];
                $array[] = $minifiyed_event;

                $entityevent->events = $array;
            }
            else
            {
                $exists = false;
                foreach($entityevent->events as $a_event)
                {

                    if($a_event->id === $event->id)
                    {
                        $exists = true;
                        break;
                    }
                }

                if(!$exists)
                {
                    $minifiyed_event = new \StdClass;
                    $minifiyed_event->id = $event->id;
                    $minifiyed_event->calendarId = $event->calendarId;
                    $minifiyed_event->calendarType = $event->calendarType;
                    $minifiyed_event->accountId = $event->accountId;
                    $allEvents = $entityevent->events;
                    $allEvents[] = $minifiyed_event;
                    $entityevent->events = $allEvents;
                }
            }

            $entityevent->save();
        }
    }
}
