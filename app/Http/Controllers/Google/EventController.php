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
                    $start = new Carbon($request->input('start'));
                    $end = new Carbon($request->input('end'));

                    $optParams = array(
                    'orderBy' => 'startTime',
                    'singleEvents' => true,
                    'timeMin' => $start->toIso8601String(),
                    'timeMax' => $end->toIso8601String(),
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
        $conferenceArray = [];
        $startArray = [];
        $endArray = [];
        $startArray['timeZone'] =config('app.timezone', 'UTC');
        $endArray['timeZone'] = config('app.timezone', 'UTC');

        $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.$request->input('entityType').'/'.$request->input('entityId');

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
            'description' => ($request->input('description') ?? '').($request->input('entityType')!=null && $request->input('entityId')!=null ? $uccelloLink : ''),
            'start' => $startArray,
            'end' => $endArray,
        ));
        
        $calendarId = $request->input('calendarId');
        $event = $service->events->insert($calendarId, $event);

        return var_dump($event);
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

        $uccelloUrl = str_replace('.', '\.',env('APP_URL'));
        
        $regexFound = preg_match('`'.$uccelloUrl.'/[0-9]+/([a-z]+)/([0-9]+)`', $event->description, $matches);
        $entityType = '';
        $entityId = '';
        if($regexFound)
        {
            $entityType = $matches[1] ?? '';
            $entityId = $matches[2] ?? '';
        }

        $returnEvent = new \StdClass;
        $returnEvent->id =              $event->id;
        $returnEvent->title =           $event->summary ?? '(no title)';
        $returnEvent->start =           $start;
        $returnEvent->end =             $end;
        $returnEvent->allDay =          $event->start->dateTime || $event->end->dateTime ? false : true;
        $returnEvent->location =        $event->location;
        $returnEvent->description =     $regexFound ? str_replace($matches[0],'',$event->description) : $event->description;
        $returnEvent->entityType =      $entityType;
        $returnEvent->entityId =        $entityId;
        $returnEvent->calendarId =      $request->input('calendarId');
        $returnEvent->accountId =       $request->input('accountId');

        return json_encode($returnEvent);
    }

    public function update(Domain $domain, Module $module, Request $request)
    {
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient($request->input('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $event = $service->events->get($request->input('calendarId'), $request->input('id'));

        $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.$request->input('entityType').'/'.$request->input('entityId');
        $start = $event->getStart();
        $end = $event->getEnd();

        $datetimeRegex = '/\d{2}\/\d{2}\/\d{4}\ \d{2}\:\d{2}/';

        $dateOnly = true;
        $startArray = [];
        $endArray = [];
        $start->setTimeZone(config('app.timezone', 'UTC'));
        $end->setTimeZone(config('app.timezone', 'UTC'));

        if(preg_match($datetimeRegex, $request->input('start_date')) || preg_match($datetimeRegex, $request->input('end_date')))
            $dateOnly = false;

        if($dateOnly)
        {
            $start->setDateTime(null);
            $end->setDateTime(null);
            
            $startDate = Carbon::createFromFormat('!d/m/Y', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('!d/m/Y', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));

            $start->setDate($startDate->toDateString());
            $end->setDate($endDate->toDateString());
        }
        else
        {
            $start->setDate(null);
            $end->setDate(null);

            $startDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));

            $start->setDateTime($startDate->toAtomString());
            $end->setDateTime($endDate->toAtomString());
        }

        $event->setSummary($request->input('subject'));
        $event->setLocation($request->input('location'));
        $event->setDescription(($request->input('description') ?? '').
            ($request->input('entityType')!=null && $request->input('entityId')!=null ? $uccelloLink : ''));
        $event->setStart($start);
        $event->setEnd($end);

        $event = $service->events->update($request->input('calendarId'), $event->getId(), $event);

        //return var_dump($event);
    }

    public function delete(Domain $domain, Module $module,  Request $request)
    {
        
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient($request->input('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $service->events->delete($request->input('calendarId'), $request->input('id'));
    }
}
