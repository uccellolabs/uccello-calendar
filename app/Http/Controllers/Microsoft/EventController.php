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

                    $eventsQueryParams = array (
                        // // Only return Subject, Start, and End fields
                        "\$select" => "*",
                        "\$filter" => 'Start/DateTime ge \''.$request->input('start').'\' and Start/DateTime le \''.$request->input('end').'\'',
                        // Sort by Start, oldest first
                        "\$orderby" => "Start/DateTime",
                        // Return at most 100 results
                        "\$top" => "100"
                    );

                    $getEventsUrl = '/me/calendars/'.$calendar->id.'/events?'.http_build_query($eventsQueryParams);
                    
                    $items = $graph->createRequest('GET', $getEventsUrl)
                                    ->setReturnType(Model\Event::class)
                                    ->execute();

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
        //https://docs.microsoft.com/en-us/graph/api/group-post-events?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient($request->input('accountId'));

        $datetimeRegex = '/\d{2}\/\d{2}\/\d{4}\ \d{2}\:\d{2}/';
        
        $dateOnly = true;
        $startDate = '';
        $endDate = '';
        $parameters = new \StdClass;
        $parameters->start = new \StdClass;
        $parameters->end = new \StdClass;

        $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.$request->input('entityType').'/'.$request->input('entityId');

        if($request->input('allDay')=="true")
        {
            $startDate = Carbon::createFromFormat('!d/m/Y', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('!d/m/Y', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));

            $endDate->addDay(1);

            $parameters->isAllDay = true;
            $parameters->end->dateTime = explode('+', $endDate->toAtomString())[0].'+00:00';
            $parameters->start->dateTime = explode('+', $startDate->toAtomString())[0].'+00:00';
            $parameters->end->timeZone = 'UTC';
            $parameters->start->timeZone = 'UTC';
        }
        else
        {
            $startDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $parameters->end->dateTime = $endDate->toAtomString();
            $parameters->start->dateTime = $startDate->toAtomString();
            $parameters->end->timeZone = config('app.timezone', 'UTC');
            $parameters->start->timeZone = config('app.timezone', 'UTC');
        }

        $parameters->subject = $request->input('subject');
        
        $parameters->location = new \StdClass;
        $parameters->location->displayName = $request->input('location') ?? '';
        $parameters->body = new \StdClass;
        $parameters->body->content = ($request->input('description') ?? '').($request->input('entityType')!=null && $request->input('entityId')!=null ? $uccelloLink : '');
        $parameters->body->contentType = "Text";

        $event = $graph->createRequest('POST', '/me/calendars/'.$request->input('calendarId').'/events')
                    ->attachBody($parameters)
                    ->setReturnType(Model\Event::class)
                    ->execute();

        return var_dump($event);
        
    }

    public function retrieve(Domain $domain, Module $module, Request $request)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-get-event?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient($request->input('accountId'));

        $getEventUrl = '/me/calendars/'.$request->input('calendarId').'/events/'.$request->input('id');//.http_build_query($eventsQueryParams);
        $event = $graph->createRequest('GET', $getEventUrl)
                        ->setReturnType(Model\Event::class)  
                        ->execute();


        $startDate = new Carbon($event->getStart()->getDateTime());

        $endDate = new Carbon($event->getEnd()->getDateTime());

        if(!$event->getIsAllDay())
        {
            $start = $startDate->format('d/m/Y H:i');   
            $end = $endDate->format('d/m/Y H:i');
        }
        else
        { 
            $endDate->addDay(-1);
            $start = $startDate->format('d/m/Y');
            $end = $endDate->format('d/m/Y');
        }

        $uccelloUrl = str_replace('.', '\.',env('APP_URL'));
        $regexFound = preg_match('`'.$uccelloUrl.'/[0-9]+/([a-z]+)/([0-9]+)`', $event->getBody()->getContent(), $matches);
        $entityType = '';
        $entityId = '';
        $expression = '';
        if($regexFound)
        {
            $expression = $matches[0] ?? '';
            $entityType = $matches[1] ?? '';
            $entityId = $matches[2] ?? '';
        }

        preg_match_all('/<div class="PlainText">(.+?)<\/div>/', $event->getBody()->getContent(), $matches, PREG_OFFSET_CAPTURE, 0);
        // dd($event->getBody()->getContent());
        // var_dump($event->getBody()->getContent());
        $description = '';
        foreach($matches[1] as $div)
        {
            $description.=$div[0]."\n"; 
        }

        $returnEvent = new \StdClass;
        $returnEvent->id =              $event->getId();
        $returnEvent->title =           $event->getSubject() ?? '(no title)';
        $returnEvent->start =           $start;
        $returnEvent->end =             $end;
        $returnEvent->allDay =          $event->getIsAllDay();
        $returnEvent->location =        $event->getLocation()->getDisplayName();
        $returnEvent->description =     preg_replace('`<a .+? href="'.$uccelloUrl.'.+?">.+?</a>`', '', $description);
        $returnEvent->entityType =      $entityType;
        $returnEvent->entityId =        $entityId;
        $returnEvent->calendarId =      $request->input('calendarId');
        $returnEvent->accountId =       $request->input('accountId');


        return json_encode($returnEvent);
    }

    public function update(Domain $domain, Module $module, Request $request)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-update-event?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient($request->input('accountId'));

        $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.$request->input('entityType').'/'.$request->input('entityId');

        $parameters = new \StdClass;
        $parameters->start = new \StdClass;
        $parameters->end = new \StdClass;

        if($request->input('allDay')=="true")
        {
            $startDate = Carbon::createFromFormat('!d/m/Y', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('!d/m/Y', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));

            $endDate->addDay(1);

            $parameters->isAllDay = true;
            $parameters->end->dateTime = explode('+', $endDate->toAtomString())[0].'+00:00';
            $parameters->start->dateTime = explode('+', $startDate->toAtomString())[0].'+00:00';
            $parameters->end->timeZone = 'UTC';
            $parameters->start->timeZone = 'UTC';
        }
        else
        {
            $startDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat('d/m/Y H:i', $request->input('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $parameters->end->dateTime = $endDate->toAtomString();
            $parameters->start->dateTime = $startDate->toAtomString();
            $parameters->end->timeZone = config('app.timezone', 'UTC');
            $parameters->start->timeZone = config('app.timezone', 'UTC');
        }

        $parameters->subject = $request->input('subject');
        
        $parameters->location = new \StdClass;
        $parameters->location->displayName = $request->input('location') ?? '';
        $parameters->body = new \StdClass;
        $parameters->body->content = ($request->input('description') ?? '').
            ($request->input('entityType')!=null && $request->input('entityId')!=null ? $uccelloLink : '');
        $parameters->body->contentType = "Text";

        $event = $graph->createRequest('PATCH', '/me/calendars/'.$request->input('calendarId').'/events/'.$request->input('id'))
                    ->attachBody($parameters)
                    ->setReturnType(Model\Event::class)
                    ->execute();

        //return var_dump($event);
    }

    public function delete(Domain $domain, Module $module,  Request $request)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-delete-event?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient($request->input('accountId'));

        $getEventUrl = '/me/calendars/'.$request->input('calendarId').'/events/'.$request->input('id');
        $returnData = $graph->createRequest('DELETE', $getEventUrl)
                        ->execute();
    }
}
