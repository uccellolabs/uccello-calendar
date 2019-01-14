<?php

namespace Uccello\Calendar\Http\Controllers\Microsoft;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;
use Carbon\Carbon;


class EventsController extends Controller
{
    public function index(Domain $domain, Module $module, Request $request)
    {

        $tokenDb = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'microsoft',
            'user_id'       => auth()->id(),
        ])->first();

        $graph = new Graph();
        $graph->setAccessToken(
            AuthController::getAccessToken($tokenDb)
        );

        

        $user = $graph->createRequest('GET', '/me')
                        ->setReturnType(Model\User::class)
                        ->execute();

        $eventsQueryParams = array (
            // // Only return Subject, Start, and End fields
            "\$select" => "*",
            // Sort by Start, oldest first
            "\$orderby" => "Start/DateTime",
            // Return at most 10 results
            "\$top" => "100"
        );

        //TODO: Use &start= and &end= to adapt the query

        $getEventsUrl = '/me/events?'.http_build_query($eventsQueryParams);
        $items = $graph->createRequest('GET', $getEventsUrl)
                        ->setReturnType(Model\Event::class)
                        ->execute();

        $events = [];

        foreach ($items as $item) {
            $dateStart = new Carbon($item->getStart()->getDateTime());
            $dateEnd = new Carbon($item->getEnd()->getDateTime());

            if ($dateStart->toTimeString() === '00:00:00' && $dateEnd->toTimeString() === '00:00:00') {
                $dateStartStr = $dateStart->toDateString();
                $dateEndStr = $dateEnd->toDateString();
                // $color = '#D32F2F';
                $className = "bg-primary";
            } else {
                $dateStartStr = str_replace(' ', 'T', $dateStart->toDateTimeString());
                $dateEndStr = str_replace(' ', 'T', $dateEnd->toDateTimeString());
                // $color = '#7B1FA2';
                $className = "bg-green";
            }

            $events[] = [
                "id" => $item->getId(),
                "title" => $item->getSubject() ?? '(no title)',
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
        $tokenDb = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'microsoft',
            'user_id'       => auth()->id(),
        ])->first();

        $graph = new Graph();
        $graph->setAccessToken(
            AuthController::getAccessToken($tokenDb)
        );

        $calendarList = $graph->createRequest('GET', '/me/calendars')
                        ->setReturnType(Model\Calendar::class)
                        ->execute();

        $calendars = [];

        foreach($calendarList as $calendarListEntry)
        {
            $calendar = [
                'name' => $calendarListEntry->getName(),
                'id' => $calendarListEntry->getProperties()['id'],
                'service' => 'microsoft',
                'color' => $calendarListEntry->getProperties()['color'],
                'accountId' => $accountId
            ];
            if($calendar['color']=='auto')
                $calendar['color'] = '#03A9F4';
            array_push($calendars, $calendar);
        }

        return $calendars;
    }

    public function addCalendar(Domain $domain, Module $module, Request $request, $accountId)
    {
        
    }

    public function removeCalendar(Domain $domain, Module $module, Request $request, CalendarAccount $account, $calendarId)
    {
        dd('ici');    
    }
}
