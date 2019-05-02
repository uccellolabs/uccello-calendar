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

        $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.request('entityType').'/'.request('entityId');

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

        $event = new \Google_Service_Calendar_Event(array(
            'summary' => request('subject'),
            'location' => request('location'),
            'description' => (request('description') ?? '').(request('entityType')!=null && request('entityId')!=null ? ' - '.$uccelloLink : ''),
            'start' => $startArray,
            'end' => $endArray,
        ));

        $calendarId = request('calendarId');
        $event = $service->events->insert($calendarId, $event);

        return [ 'success' => true];
    }

    public function retrieve(Domain $domain, Module $module)
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

        $regexFound = preg_match('` - '.$uccelloUrl.'/[0-9]+/([a-z]+)/([0-9]+)`', $event->description, $matches);
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
        $returnEvent->calendarId =      request('calendarId');
        $returnEvent->accountId =       request('accountId');

        return json_encode($returnEvent);
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
            $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.request('entityType').'/'.request('entityId');

            $event->setDescription((request('description') ?? '').
                (request('entityType')!=null && request('entityId')!=null ? ' - '.$uccelloLink : ''));
        }

        $event->setStart($start);
        $event->setEnd($end);

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
