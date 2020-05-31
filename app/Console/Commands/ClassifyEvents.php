<?php

namespace Uccello\Calendar\Console\Commands;

use Illuminate\Console\Command;
use stdClass;
use Uccello\Calendar\CalendarEntityEvent;
use Uccello\Core\Models\Domain;

class ClassifyEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:classify {user_id : id of user} {start : start time with format yyyy-mm-dd}{end}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify events';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $eventController = new \Uccello\Calendar\Http\Controllers\Generic\EventController;
        $module = ucmodule('calendar');
        $events = [];
        $domains = Domain::all();
        foreach ($domains as $domain) {
            $params = [];
            $params['start']        = $this->argument('start');
            $params['end']          = $this->argument('end');
            $params['user_id']      = $this->argument('user_id');
            $dom_events = $eventController->all($domain, $module, $params);
            foreach ($dom_events as $event) {
                $params['calendarId']   = $event['calendarId'];
                $params['accountId']    = $event['accountId'];
                $params['eventId']      = $event['id'];
                $params['type']         = $event['calendarType'];
                $events[] = $eventController->retrieve($domain, $module, false, $params);
            }
        }

        foreach ($events as $event) {
            if ($event->moduleName && $event->recordId) {
                $entityevent = CalendarEntityEvent::firstOrNew([
                    'entity_id' => $event->recordId,
                    'module_id' => ucmodule($event->moduleName)->id]);
                if (!$entityevent->events || $entityevent==null) {
                    $minifiyed_event = new stdClass;
                    $minifiyed_event->id = $event->id;
                    $minifiyed_event->calendarId = $event->calendarId;
                    $minifiyed_event->calendarType = $event->calendarType;
                    $minifiyed_event->accountId = $event->accountId;

                    $array = [];
                    $array[] = $minifiyed_event;

                    $entityevent->events = $array;
                } else {
                    $exists = false;
                    foreach ($entityevent->events as $a_event) {
                        if ($a_event->id === $event->id) {
                            $exists = true;
                            break;
                        }
                    }

                    if (!$exists) {
                        $minifiyed_event = new stdClass;
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
}
