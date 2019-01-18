<?php

namespace Uccello\Calendar\Http\Controllers\Google;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;
use Carbon\Carbon;
use Google\Client;


class EventController extends Controller
{
    public function list(Domain $domain, Module $module, Request $request)
    {
        $accounts = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'google',
            'user_id'       => auth()->id(),
        ])->get();

        $events = [];

        $accountController = new AccountController();

        $calendarsFetched = [];

        foreach ($accounts as $account) {

            $calendarController = new CalendarController();
            $calendars = $calendarController->list($domain, $module, $request, $account->id);
            $calendarsDisabled = \Uccello\Calendar\Http\Controllers\Generic\CalendarController::getDisabledCalendars($account->id);

            foreach($calendars as $calendar)
            {
                if(!in_array($calendar->id, $calendarsFetched) && !property_exists($calendarsDisabled, $calendar->id))
                {
                    $calendarsFetched[] = $calendar->id;
                    $oauthClient = $accountController->initClient($account->id);
                    $service = new \Google_Service_Calendar($oauthClient);

                    // Print the next 10 events on the user's calendar.
                    
                    $optParams = array(
                    'orderBy' => 'startTime',
                    'singleEvents' => true,
                    'timeMin' => date('c'),
                    );

                    $results = $service->events->listEvents($calendar->id, $optParams);

                    $items = $results->getItems();

                    foreach ($items as $event) {
                        
                        $events[] = [
                            "id" => $event->id,
                            "title" => $event->summary ?? '(no title)',
                            "start" => $event->start->dateTime ?? $event->start->date,
                            "end" => $event->end->dateTime ?? $event->end->date,
                            "color" => $calendar->color,
                            "calendarId" => $calendar->id
                        ];
                    }
                }
            }
        }   

        return $events;
    }

    public function create(Domain $domain, Module $module, Request $request)
    {

        $accounts = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'google',
            'user_id'       => auth()->id(),
        ])->get();


        $accountId = null;

        foreach($accounts as $account)
        {
            $calendarController = new CalendarController();
            $calendars = $calendarController->list($domain, $module, $request, $account->id);
            foreach($calendars as $calendar)
            {
                if($calendar->id==$request->input('calendar'))
                {
                    $accountId = $account->id;
                    break;
                }
            }
            if($accountId!=null)
                break;
        }

        if($accountId!=null)
        {
            $accountController = new AccountController();
            $oauthClient = $accountController->initClient($accountId);
            $service = new \Google_Service_Calendar($oauthClient);

            $startDate = Carbon::createFromFormat('Y-m-d H:i', $request->input('start_date').' '.$request->input('start_time'))
                ->setTimezone(config('timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('Y-m-d H:i', $request->input('end_date').' '.$request->input('end_time'))
                ->setTimezone(config('timezone', 'UTC'));

            $event = new \Google_Service_Calendar_Event(array(
                'summary' => $request->input('subject'),
                'location' => $request->input('location'),
                'description' => $request->input('location') ?? '',
                'start' => array(
                    'dateTime' => $startDate->toIso8601String(),
                    'timeZone' => config('timezone', 'UTC'),
                ),
                'end' => array(
                    'dateTime' => $endDate->toIso8601String(),
                    'timeZone' => config('timezone', 'UTC'),
                ),
            ));
            // $event = new \Google_Service_Calendar_Event(array(
            //     'summary' => 'Google I/O 2015',
            //     'location' => '800 Howard St., San Francisco, CA 94103',
            //     'description' => 'A chance to hear more about Google\'s developer products.',
            //     'start' => array(
            //       'dateTime' => '2015-05-28T09:00:00-07:00',
            //       'timeZone' => 'America/Los_Angeles',
            //     ),
            //     'end' => array(
            //       'dateTime' => '2015-05-28T17:00:00-07:00',
            //       'timeZone' => 'America/Los_Angeles',
            //     ),
            //     'recurrence' => array(
            //       'RRULE:FREQ=DAILY;COUNT=2'
            //     ),
            //     'attendees' => array(
            //       array('email' => 'lpage@example.com'),
            //       array('email' => 'sbrin@example.com'),
            //     ),
            //     'reminders' => array(
            //       'useDefault' => FALSE,
            //       'overrides' => array(
            //         array('method' => 'email', 'minutes' => 24 * 60),
            //         array('method' => 'popup', 'minutes' => 10),
            //       ),
            //     ),
            //   ));
            
            $calendarId = $request->input('calendar');
            $event = $service->events->insert($calendarId, $event);

            return var_dump($event);
        } 
    }
}
