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
    public function list(Domain $domain, Module $module)
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
            $calendars = $calendarController->list($domain, $module, $account->id);
            $calendarsDisabled = (array) \Uccello\Calendar\Http\Controllers\Generic\CalendarController::getDisabledCalendars($account->id);

            foreach($calendars as $calendar)
            {
                if(!in_array($calendar->id, $calendarsFetched) && !in_array($calendar->id, $calendarsDisabled))
                {
                    $calendarsFetched[] = $calendar->id;
                    $oauthClient = $accountController->initClient($account->id);
                    $service = new \Google_Service_Calendar($oauthClient);

                    // Print the next 10 events on the user's calendar.
                    $start = new Carbon(request('start'));
                    $end = new Carbon(request('end'));

                    $optParams = array(
                        'orderBy' => 'startTime',
                        'singleEvents' => true,
                        'timeMin' => $start->toIso8601String(),
                        'timeMax' => $end->toIso8601String(),
                    );

                    $results = $service->events->listEvents($calendar->id, $optParams);

                    $items = $results->getItems();

                    foreach ($items as $event) {

                        $uccelloUrl = str_replace('.', '\.',env('APP_URL'));
                        $regexFound = preg_match('`'.$uccelloUrl.'/to/[0-9]*/?([a-z]+)/([0-9]+)`', $event->description, $matches);
                        $moduleName = '';
                        $recordId = '';
                        if($regexFound)
                        {
                            $moduleName = $matches[1] ?? '';
                            $recordId = $matches[2] ?? '';
                        }

                        $events[] = [
                            "id" => $event->id,
                            "title" => $event->summary ?? '(no title)',
                            "start" => $event->start->dateTime ?? $event->start->date,
                            "end" => $event->end->dateTime ?? $event->end->date,
                            "color" => $calendar->color,
                            "calendarId" => $calendar->id,
                            "accountId" => $account->id,
                            "calendarType" => $account->service_name,
                            "editable" => !$calendar->read_only,
                            "moduleName" => $moduleName,
                            "recordId" => $recordId,
                            "categories" => null,
                        ];
                    }
                }
            }
        }

        return $events;
    }

    public function create(Domain $domain, Module $module)
    {
        //https://developers.google.com/calendar/v3/reference/events/insert
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient(request('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $datetimeRegex = '/\d{2}\/\d{2}\/\d{4}\ \d{2}\:\d{2}/';

        $dateOnly = true;
        $conferenceArray = [];
        $startArray = [];
        $endArray = [];
        $startArray['timeZone'] =config('app.timezone', 'UTC');
        $endArray['timeZone'] = config('app.timezone', 'UTC');

        $uccelloLink = \Uccello\Calendar\Http\Controllers\Generic\EventController::generateEntityLink($domain);

        if(preg_match($datetimeRegex, request('start_date')) || preg_match($datetimeRegex, request('end_date')))
            $dateOnly = false;

        if($dateOnly)
        {
            $startDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $startArray['date'] = $startDate->toDateString();
            $endArray['date'] =  $endDate->toDateString();
        }
        else
        {
            $startDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $startArray['dateTime'] =$startDate->toAtomString();
            $endArray['dateTime'] =  $endDate->toAtomString();
        }

        $attendees = [];
        if(request('attendees') && count(request('attendees'))>0)
        {
            foreach(request('attendees') as $a_attendee)
            {
                $attendee = [];
                $attendee['email'] = $a_attendee;
                $calendarAccount = CalendarAccount::where('username', $a_attendee)->first();
                if($calendarAccount)
                    $attendee['displayName'] = $calendarAccount->user->name;
                $attendees[] = $attendee;
            }
        }

        $event = new \Google_Service_Calendar_Event(array(
            'summary' => request('subject'),
            'location' => request('location'),
            'description' => (request('description') ?? '').(request('moduleName')!=null && request('recordId')!=null ? $uccelloLink : ''),
            'start' => $startArray,
            'end' => $endArray,
            'attendees' => $attendees,
        ));

        $calendarId = request('calendarId');
        $event = $service->events->insert($calendarId, $event);

        return [ 'success' => true];
    }

    public function retrieve(Domain $domain, Module $module, $returnJson = true)
    {
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient(request('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $event = $service->events->get(request('calendarId'), request('id'));

        if($event->start->dateTime)
        {
            $startDate = Carbon::createFromFormat(\DateTime::ISO8601, $event->start->dateTime)
                ->setTimezone($event->start->timeZone ?? config('app.timezone', 'UTC'));
            $start = $startDate->format(config('uccello.format.php.datetime'));
        }
        else
        {
            $startDate = Carbon::createFromFormat('Y-m-d', $event->start->date)
                ->setTimezone($event->start->timeZone ?? config('app.timezone', 'UTC'));
            $start = $startDate->format(config('uccello.format.php.date'));
        }

        if($event->end->dateTime)
        {
            $endDate = Carbon::createFromFormat(\DateTime::ISO8601, $event->end->dateTime)
                ->setTimezone($event->end->timeZone ?? config('app.timezone', 'UTC'));
            $end = $endDate->format(config('uccello.format.php.datetime'));
        }
        else
        {
            $endDate = Carbon::createFromFormat('Y-m-d', $event->end->date)
                ->setTimezone($event->end->timeZone ?? config('app.timezone', 'UTC'));
            $end = $endDate->format(config('uccello.format.php.date'));
        }

        $uccelloUrl = str_replace('.', '\.',env('APP_URL'));
        $regexFound = preg_match('`'.$uccelloUrl.'/to/[0-9]*/?([a-z]+)/([0-9]+)`', $event->description, $matches);
        $moduleName = '';
        $recordId = '';
        if($regexFound)
        {
            $moduleName = $matches[1] ?? '';
            $recordId = $matches[2] ?? '';
        }

        $attendees = [];
        foreach($event->getAttendees() as $a_attendee)
        {
            $attendee = new \StdClass;
            $attendee->email = $a_attendee->email;
            $attendee->name = $a_attendee->displayName;
            $attendee->img = asset(CalendarAccount::where('username', $attendee->email)->first()->user->image) ?? '';
            $attendees[] = $attendee;
        }

        $returnEvent = new \StdClass;
        $returnEvent->id =              $event->id;
        $returnEvent->title =           $event->summary ?? '(no title)';
        $returnEvent->start =           $start;
        $returnEvent->end =             $end;
        $returnEvent->allDay =          $event->start->dateTime || $event->end->dateTime ? false : true;
        $returnEvent->location =        $event->location;
        $returnEvent->description =     $regexFound ? str_replace($matches[0],'',$event->description) : $event->description;
        $returnEvent->moduleName =      $moduleName;
        $returnEvent->recordId =        $recordId;
        $returnEvent->calendarId =      request('calendarId');
        $returnEvent->calendarType =    'microsoft';
        $returnEvent->accountId =       request('accountId');
        $returnEvent->categories =      null; //TODO:
        $returnEvent->attendees =       $attendees;

        if($returnJson)
            return json_encode($returnEvent);
        else
            return $returnEvent;
    }

    public function update(Domain $domain, Module $module)
    {
        $accountController = new AccountController();
        $oauthClient = $accountController->initClient(request('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $event = $service->events->get(request('calendarId'), request('id'));

        $start = $event->getStart();
        $end = $event->getEnd();

        $datetimeRegex = '/\d{2}\/\d{2}\/\d{4}\ \d{2}\:\d{2}/';

        $dateOnly = true;
        $start->setTimeZone(config('app.timezone', 'UTC'));
        $end->setTimeZone(config('app.timezone', 'UTC'));

        if(preg_match($datetimeRegex, request('start_date')) || preg_match($datetimeRegex, request('end_date'))) {
            $dateOnly = false;
        }

        if($dateOnly) {
            $start->setDateTime(null);
            $end->setDateTime(null);

            $startDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('start_date'))
                ->setTime(0,0,0)
                ->setTimezone(config('app.timezone', 'UTC'));

            $endDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('end_date'))
                ->setTime(0,0,0)
                ->setTimezone(config('app.timezone', 'UTC'));

            $start->setDate($startDate->toDateString());
            $end->setDate($endDate->toDateString());

        } else {
            $start->setDate(null);
            $end->setDate(null);

            $startDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));

            $start->setDateTime($startDate->toAtomString());
            $end->setDateTime($endDate->toAtomString());
        }

        if (request()->has('subject')) {
            $event->setSummary(request('subject'));
        }

        if (request()->has('location')) {
            $event->setLocation(request('location'));
        }

        if (request()->has('description')) {
            $uccelloLink = \Uccello\Calendar\Http\Controllers\Generic\EventController::generateEntityLink($domain);

            $event->setDescription((request('description') ?? '').
                (request('moduleName')!=null && request('recordId')!=null ? $uccelloLink : ''));
        }

        $event->setStart($start);
        $event->setEnd($end);

        $attendees = [];
        if(request('attendees') && count(request('attendees'))>0)
        {
            foreach(request('attendees') as $a_attendee)
            {
                $attendee = [];
                $attendee['email'] = $a_attendee;
                $calendarAccount = CalendarAccount::where('username', $a_attendee)->first();
                if($calendarAccount)
                    $attendee['displayName'] = $calendarAccount->user->name;
                $attendees[] = $attendee;
            }
        }
        $event->setAttendees($attendees);

        $event = $service->events->update(request('calendarId'), $event->getId(), $event);

        return ['success' => true];
    }

    public function delete(Domain $domain, Module $module)
    {

        $accountController = new AccountController();
        $oauthClient = $accountController->initClient(request('accountId'));
        $service = new \Google_Service_Calendar($oauthClient);

        $service->events->delete(request('calendarId'), request('id'));
    }
}
