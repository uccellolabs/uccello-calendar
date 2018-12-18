<?php

namespace Uccello\Calendar\Http\Controllers;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\TokenStore\TokenCache;
use Carbon\Carbon;


class EventsController extends Controller
{
    /**
     * Check user permissions
     */
    protected function checkPermissions()
    {
        $this->middleware('uccello.permissions:retrieve');
    }

    protected function index(Domain $domain, Module $module, Request $request)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $tokenCache = new TokenCache;

        $graph = new Graph();
        $graph->setAccessToken($tokenCache->getAccessToken());

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
}
