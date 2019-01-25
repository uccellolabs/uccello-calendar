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
                            "calendarId" => $calendar->id,
                            "accountId" => $account->id,
                            "calendarType" => $account->service_name,
                        ];
                    }
                }
            }
        }   

        return $events;
    }

    public function create(Domain $domain, Module $module, Request $request)
    {
        //https://developers.google.com/calendar/v3/reference/events/insert
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient($request->input('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $datetimeRegex = '/\d{2}\/\d{2}\/\d{4}\ \d{2}\:\d{2}/';

        $dateOnly = true;
        $startArray = [];
        $endArray = [];
        $startArray['timeZone'] =config('app.timezone', 'UTC');
        $endArray['timeZone'] = config('app.timezone', 'UTC');

        if(preg_match($datetimeRegex, $request->input('start_date')) || preg_match($datetimeRegex, $request->input('end_date')))
            $dateOnly = false;

        if($dateOnly)
        {
            $startDate = Carbon::createFromFormat('!d/m/Y', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('!d/m/Y', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $startArray['date'] = $startDate->toDateString();
            $endArray['date'] =  $endDate->toDateString();
        }
        else
        {
            $startDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $startArray['dateTime'] =$startDate->toAtomString();
            $endArray['dateTime'] =  $endDate->toAtomString();
        }

        $event = new \Google_Service_Calendar_Event(array(
            'summary' => $request->input('subject'),
            'location' => $request->input('location'),
            'description' => $request->input('description') ?? '',
            'start' => $startArray,
            'end' => $endArray,
        ));
        
        $calendarId = $request->input('calendarId');
        $event = $service->events->insert($calendarId, $event);

        return var_dump($event);
        
    }

    public function delete(Domain $domain, Module $module,  Request $request)
    {
        
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient($request->input('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        return $service->events->delete($request->input('calendar'), $request->input('event'));
        
    }

    public function retrieve(Domain $domain, Module $module, Request $request)
    {
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient($request->input('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $event = $service->events->get($request->input('calendarId'), $request->input('id'));



        if($event->start->dateTime)
        {
            $startDate = Carbon::createFromFormat(\DateTime::ISO8601, $event->start->dateTime)
                ->setTimezone($event->start->timeZone ?? config('app.timezone', 'UTC'));
            $start = $startDate->format('d/m/Y H:i');
        }
        else
        {
            $startDate = Carbon::createFromFormat('Y-m-d', $event->start->date)
                ->setTimezone($event->start->timeZone ?? config('app.timezone', 'UTC'));  
            $start = $startDate->format('d/m/Y');
        }
                
        if($event->end->dateTime)
        {
            $endDate = Carbon::createFromFormat(\DateTime::ISO8601, $event->end->dateTime)
                ->setTimezone($event->end->timeZone ?? config('app.timezone', 'UTC'));
            $end = $endDate->format('d/m/Y H:i');
        }
        else
        {
            $endDate = Carbon::createFromFormat('Y-m-d', $event->end->date)
                ->setTimezone($event->end->timeZone ?? config('app.timezone', 'UTC'));
            $end = $endDate->format('d/m/Y');
        }

        

        $returnEvent = new \StdClass;
        $returnEvent->id =              $event->id;
        $returnEvent->title =           $event->summary ?? '(no title)';
        $returnEvent->start =           $start;
        $returnEvent->end =             $end;
        $returnEvent->allDay =          $event->start->dateTime || $event->end->dateTime ? false : true;
        $returnEvent->location =        $event->location;
        $returnEvent->description =     $event->description;
        $returnEvent->calendarId =      $request->input('calendarId');
        $returnEvent->accountId =       $request->input('accountId');

        return json_encode($returnEvent);
    }
}
