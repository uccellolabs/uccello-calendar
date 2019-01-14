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


class EventsController extends Controller
{
    public function index(Domain $domain, Module $module, Request $request)
    {
        $oauthClient = $this->initClient();

        $service = new \Google_Service_Calendar($oauthClient);

        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => date('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);

        $events = [];
        $items = $results->getItems();

        foreach ($items as $event) {

            $dateStart = new Carbon($event->start->dateTime);
            $dateEnd = new Carbon($event->end->dateTime);

            if ($dateStart->toTimeString() === '00:00:00' && $dateEnd->toTimeString() === '00:00:00') {
                $dateStartStr = $dateStart->toDateString();
                $dateEndStr = $dateEnd->toDateString();
                // $color = '#D32F2F';
                $className = "bg-primary";
            } else {
                $dateStartStr = str_replace(' ', 'T', $dateStart->toDateTimeString());
                $dateEndStr = str_replace(' ', 'T', $dateEnd->toDateTimeString());
                // $color = '#7B1FA2';
                $className = "bg-red";
            }

            $events[] = [
                "id" => $event->id,
                "title" => $event->summary ?? '(no title)',
                "start" => $dateStartStr,
                "end" => $dateEndStr,
                // "color" => $color,
                "className" => $className
            ];
        }

        return $events;
    }

    public function getCalendars(Domain $domain, Module $module, Request $request, $accountId)
    {
        $oauthClient = $this->initClient($accountId);

        $service = new \Google_Service_Calendar($oauthClient);

        $calendarList = $service->calendarList->listCalendarList();

        $colors = $service->colors->get();

        $calendars = [];

        foreach($calendarList->getItems() as $calendarListEntry)
        {
            $calendar = [
                'name' => $calendarListEntry->getSummary(),
                'id' => $calendarListEntry->getId(),
                'service' => 'google',
                'color' => $colors->getCalendar()[$calendarListEntry->colorId]->background,
                'accountId' => $accountId
            ];
            array_push($calendars, $calendar);
        }



        return $calendars;
    }

    public function addCalendar(Domain $domain, Module $module, Request $request, $accountId)
    {
        $oauthClient = $this->initClient($accountId);
        $service = new \Google_Service_Calendar($oauthClient);

        $calendar = new \Google_Service_Calendar_Calendar();
        $calendar->setSummary($request['calendarName']);
        $calendar->setTimeZone('America/Los_Angeles');
        $createdCalendar = $service->calendars->insert($calendar);
    }

    public function removeCalendar(Domain $domain, Module $module, Request $request, CalendarAccount $account, $calendarId)
    {
        $oauthClient = $this->initClient($account->id);
        $service = new \Google_Service_Calendar($oauthClient);

        $service->calendars->delete($calendarId);
    }

    private function initClient($accountId)
    {
        // Initialize the OAuth client
        $oauthClient = new \Google_Client([
            'application_name'          => env('APP_NAME'),
            'client_id'                 => env('GOOGLE_CLIENT_ID'),
            'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
        ]);
        $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
        $oauthClient->setAccessType('offline');

        $account = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'google',
            'user_id'       => auth()->id(),
            'id'            => $accountId,
        ])->first();

        $oauthClient->setAccessToken(
            AuthController::getAccessToken($account)
        );

        return $oauthClient;
    }
}
