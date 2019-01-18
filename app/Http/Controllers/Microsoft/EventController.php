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


class EventController extends Controller
{

    /**
     * Returns all events from all Microsoft accounts
     *
     * @param \Uccello\Core\Models\Domain|null $domain
     * @param \Uccello\Core\Models\Module $module
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function list(Domain $domain, Module $module, Request $request)
    {

        $accounts = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'microsoft',
            'user_id'       => auth()->id(),
        ])->get();

        $events = [];

        $calendarsFetched = [];
        $accountController = new AccountController();

        foreach ($accounts as $account) {

            $calendarController = new CalendarController();
            $calendars = $calendarController->list($domain, $module, $request, $account->id);
            foreach($calendars as $calendar)
            {
                if(!in_array($calendar->id, $calendarsFetched))
                {
                    $graph = $accountController->initClient($account->id);

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

    public function create(Domain $domain, Module $module, Request $request)
    {
        dd($request);
    }
}
