<?php

namespace Uccello\Calendar\Http\Controllers\Google;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarToken;
use Carbon\Carbon;

use Google\Client;


class EventsController extends Controller
{
    public function index(Domain $domain, Module $module, Request $request)
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

        $tokenDb = \Uccello\Calendar\CalendarToken::where([
            'service_name'  => 'google',
            'user_id'       => auth()->id(),
        ])->first();

        $oauthClient->setAccessToken(
            AuthController::getAccessToken($tokenDb)
        );

        

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
}
