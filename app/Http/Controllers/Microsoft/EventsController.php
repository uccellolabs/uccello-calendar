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

    /**
     * Returns all events from all Microsoft accounts
     *
     * @param \Uccello\Core\Models\Domain|null $domain
     * @param \Uccello\Core\Models\Module $module
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function index(Domain $domain, Module $module, Request $request)
    {

        $accounts = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'microsoft',
            'user_id'       => auth()->id(),
        ])->get();

        $events = [];

        $calendarsFetched = [];

        foreach ($accounts as $account) {

            $calendars = $this->getCalendars($domain, $module, $request, $account->id);
            foreach($calendars as $calendar)
            {
                if(!in_array($calendar->id, $calendarsFetched))
                {
                    $graph = $this->initClient($account->id);

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
                }
            }
        }
        
        return $events;
    }

    /**
     * Returns all calendars from specified account
     *
     * @param \Uccello\Core\Models\Domain|null $domain
     * @param \Uccello\Core\Models\Module $module
     * @param \Illuminate\Http\Request $request
     * @param $accountId
     * @return array
     */
    public function getCalendars(Domain $domain, Module $module, Request $request, $accountId)
    {
        $graph = $this->initClient($accountId);

        $calendarList = $graph->createRequest('GET', '/me/calendars')
                        ->setReturnType(Model\Calendar::class)
                        ->execute();

        $calendars = [];

        foreach($calendarList as $calendarListEntry)
        {

            $calendar = new \StdClass;
            $calendar->name = $calendarListEntry->getName();
            $calendar->id = $calendarListEntry->getProperties()['id'];
            $calendar->service = 'microsoft';
            $calendar->color = $calendarListEntry->getProperties()['color'];
            $calendar->accountId = $accountId;

            if($calendar->color=='auto')
                $calendar->color = '#03A9F4';

            $calendars[] = $calendar;
        }

        return $calendars;
    }

    /**
     * Add a calendar with given name to a specified account
     *
     * @param \Uccello\Core\Models\Domain|null $domain
     * @param \Uccello\Core\Models\Module $module
     * @param \Illuminate\Http\Request $request
     * @param $accountId
     * @return void
     */
    public function addCalendar(Domain $domain, Module $module, Request $request, $accountId)
    {
        $graph = $this->initClient($accountId);

        $parameters = new \StdClass;
        $parameters->Name = $request['calendarName'];

        $calendar = $graph->createRequest('POST', '/me/calendars')
                        ->attachBody($parameters)
                        ->setReturnType(Model\Calendar::class)
                        ->execute();
    }

    /**
     * Removes calendar specified by id from a specified account
     *
     * @param \Uccello\Core\Models\Domain|null $domain
     * @param \Uccello\Core\Models\Module $module
     * @param \Illuminate\Http\Request $request
     * @param \Uccello\Calendar\CalendarAccount $account
     * @param $calendarId
     * @return void
     */
    public function removeCalendar(Domain $domain, Module $module, Request $request, CalendarAccount $account, $calendarId)
    {
        $graph = $this->initClient($account->id);

        $calendar = $graph->createRequest('DELETE', '/me/calendars/'.$calendarId)
                        ->setReturnType(Model\Calendar::class)
                        ->execute();   
    }

    /**
     * Heplper function to retrive Microsoft Graph client from accountId
     *
     * @param $accountId
     * @return Microsoft\Graph\Graph
     */
    private function initClient($accountId)
    {
        $tokenDb = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'microsoft',
            'user_id'       => auth()->id(),
            'id'            => $accountId,
        ])->first();

        $graph = new Graph();
        $graph->setAccessToken(
            AuthController::getAccessToken($tokenDb)
        );

        return $graph;
    }
}
