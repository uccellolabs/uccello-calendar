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
     * @return array
     */
    public function list(Domain $domain, Module $module)
    {

        $accounts = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'microsoft',
            'user_id'       => auth()->id(),
        ])->get();

        $events = [];

        $calendarsFetched = [];
        $accountController = new AccountController();
        $calendarController = new CalendarController();

        foreach ($accounts as $account) {
            $calendarController = new CalendarController();
            $calendars = $calendarController->list($domain, $module, $account->id);
            $calendarsDisabled = (array) \Uccello\Calendar\Http\Controllers\Generic\CalendarController::getDisabledCalendars($account->id);

            $categoriesColorByName = $calendarController->getCategories($domain, $module, $account)->pluck('color', 'label');

            foreach($calendars as $calendar)
            {
                if(!in_array($calendar->id, $calendarsFetched) && !in_array($calendar->id, $calendarsDisabled))
                {
                    $graph = $accountController->initClient($account->id);

                    $eventsQueryParams = array (
                        // // Only return Subject, Start, and End fields
                        "\$select" => "*",
                        "\$filter" => 'Start/DateTime ge \''.request('start').'\' and Start/DateTime le \''.request('end').'\'',
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
                        $dateStart = (new Carbon($item->getStart()->getDateTime(), 'UTC'));
                        $dateEnd = (new Carbon($item->getEnd()->getDateTime(), 'UTC'));

                        if ($dateStart->toTimeString() === '00:00:00' && $dateEnd->toTimeString() === '00:00:00') {
                            $dateStartStr = $dateStart->toDateString();
                            $dateEndStr = $dateEnd->toDateString();
                            // $color = '#D32F2F';
                            // $className = "primary";
                        } else {
                            $dateStartStr = str_replace(' ', 'T', $dateStart->setTimezone(config('app.timezone', 'UTC'))->toDateTimeString());
                            $dateEndStr = str_replace(' ', 'T', $dateEnd->setTimezone(config('app.timezone', 'UTC'))->toDateTimeString());
                            // $color = '#7B1FA2';
                            // $className = "green";
                        }

                        $categories = $item->getCategories();
                        if (count($item->getCategories()) > 0) {
                            $color = $categoriesColorByName[$categories[0]];
                        } else {
                            $color = null;
                        }

                        if (empty($color)) {
                            $color = $calendar->color;
                        }

                        $uccelloUrl = str_replace('.', '\.',env('APP_URL'));
                        $regexFound = preg_match('`'.$uccelloUrl.'/[0-9]*/?([a-z]+)/([0-9]+)`', $item->getBody()->getContent(), $matches);
                        $moduleName = '';
                        $recordId = '';
                        if($regexFound)
                        {
                            $moduleName = $matches[1] ?? '';
                            $recordId = $matches[2] ?? '';
                        }

                        $events[] = [
                            "id" => $item->getId(),
                            "title" => $item->getSubject() ?? '(no title)',
                            "start" => $dateStartStr,
                            "end" => $dateEndStr,
                            "color" => $color,
                            "calendarId" => $calendar->id,
                            "accountId" => $account->id,
                            "calendarType" => $account->service_name,
                            "editable" => !$calendar->read_only,
                            "categories" => $categories,
                            "moduleName" => $moduleName,
                            "recordId" => $recordId,
                        ];
                    }
                }
            }
        }

        return $events;
    }

    public function create(Domain $domain, Module $module)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-post-events?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient(request('accountId'));

        $startDate = '';
        $endDate = '';
        $parameters = new \StdClass;
        $parameters->start = new \StdClass;
        $parameters->end = new \StdClass;

        if (uccello()->useMultiDomains()) {
            $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.request('moduleName').'/'.request('recordId');
        } else {
            $uccelloLink = env('APP_URL').'/'.request('moduleName').'/'.request('recordId');
        }

        if(request('allDay') === "true")
        {
            $startDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('start_date'))
                ->setTime(0,0,0)
                ->setTimezone(config('app.timezone', 'UTC'));

            $endDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('end_date'))
                ->setTime(0,0,0)
                ->setTimezone(config('app.timezone', 'UTC'));

            $endDate->addDay(1);

            $parameters->isAllDay = true;
            $parameters->start->dateTime = explode('+', $startDate->toAtomString())[0].'+00:00';
            $parameters->start->timeZone = 'UTC';
            $parameters->end->dateTime = explode('+', $endDate->toAtomString())[0].'+00:00';
            $parameters->end->timeZone = 'UTC';
        }
        else
        {
            $startDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $parameters->end->dateTime = $endDate->toAtomString();
            $parameters->start->dateTime = $startDate->toAtomString();
            $parameters->end->timeZone = config('app.timezone', 'UTC');
            $parameters->start->timeZone = config('app.timezone', 'UTC');
        }

        $parameters->subject = request('subject');
        $parameters->location = new \StdClass;
        $parameters->location->displayName = request('location') ?? '';
        $parameters->body = new \StdClass;
        $parameters->body->content = (request('description') ?? '').(request('moduleName')!==null && request('recordId')!==null ? ' - '.$uccelloLink : '');
        $parameters->body->contentType = "Text";
        $parameters->categories = (array) request('category');

        $graph->createRequest('POST', '/me/calendars/'.request('calendarId').'/events')
            ->attachBody($parameters)
            ->setReturnType(Model\Event::class)
            ->execute();

        return [ 'success' => true ];
    }

    public function retrieve(Domain $domain, Module $module)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-get-event?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient(request('accountId'));

        $getEventUrl = '/me/calendars/'.request('calendarId').'/events/'.request('id');//.http_build_query($eventsQueryParams);
        $event = $graph->createRequest('GET', $getEventUrl)
                        ->setReturnType(Model\Event::class)
                        ->execute();


        $startDate = new Carbon($event->getStart()->getDateTime(), 'UTC');

        $endDate = new Carbon($event->getEnd()->getDateTime(), 'UTC');

        if(!$event->getIsAllDay())
        {
            $start = $startDate->setTimezone(config('app.timezone', 'UTC'))->format(config('uccello.format.php.datetime'));
            $end = $endDate->setTimezone(config('app.timezone', 'UTC'))->format(config('uccello.format.php.datetime'));
        }
        else
        {
            $endDate->addDay(-1);
            $start = $startDate->format(config('uccello.format.php.date'));
            $end = $endDate->setTimezone(config('app.timezone', 'UTC'))->format(config('uccello.format.php.date'));
        }

        $uccelloUrl = str_replace('.', '\.',env('APP_URL'));
        $regexFound = preg_match('`'.$uccelloUrl.'/[0-9]*/?([a-z]+)/([0-9]+)`', $event->getBody()->getContent(), $matches);
        $moduleName = '';
        $recordId = '';
        if($regexFound)
        {
            $moduleName = $matches[1] ?? '';
            $recordId = $matches[2] ?? '';
        }

        preg_match_all('/<div class="PlainText">(.+?)<\/div>/', $event->getBody()->getContent(), $matches, PREG_OFFSET_CAPTURE, 0);

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
        $returnEvent->description =     html_entity_decode(preg_replace('` - <a.+?href="'.$uccelloUrl.'.+?">.+?</a>`', '', $description));
        $returnEvent->moduleName =      $moduleName;
        $returnEvent->recordId =        $recordId;
        $returnEvent->calendarId =      request('calendarId');
        $returnEvent->accountId =       request('accountId');
        $returnEvent->categories =      $event->getCategories();


        return json_encode($returnEvent);
    }

    public function update(Domain $domain, Module $module)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-update-event?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient(request('accountId'));

        $parameters = new \StdClass;
        $parameters->start = new \StdClass;
        $parameters->end = new \StdClass;

        if(request('allDay') === 'true')
        {
            $startDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('start_date'))
                ->setTime(0,0,0)
                ->setTimezone(config('app.timezone', 'UTC'));

            $endDate = Carbon::createFromFormat(config('uccello.format.php.date'), request('end_date'))
                ->setTime(0,0,0)
                ->setTimezone(config('app.timezone', 'UTC'));

            $parameters->isAllDay = true;
            $parameters->start->dateTime = explode('+', $startDate->toAtomString())[0].'+00:00';
            $parameters->start->timeZone = 'UTC';
            $parameters->end->dateTime = explode('+', $endDate->toAtomString())[0].'+00:00';
            $parameters->end->timeZone = 'UTC';
        }
        else
        {
            $startDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('start_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $endDate = Carbon::createFromFormat(config('uccello.format.php.datetime'), request('end_date'))
                ->setTimezone(config('app.timezone', 'UTC'));
            $parameters->start->dateTime = $startDate->toAtomString();
            $parameters->start->timeZone = config('app.timezone', 'UTC');
            $parameters->end->dateTime = $endDate->toAtomString();
            $parameters->end->timeZone = config('app.timezone', 'UTC');
        }

        if (request()->has('subject')) {
            $parameters->subject = request('subject');
        }

        if(request()->has('category')) {
            $parameters->categories = (array) request('category');
        }

        if (request()->has('location')) {
            $parameters->location = new \StdClass;
            $parameters->location->displayName = request('location') ?? '';
        }

        if (request()->has('description')) {
            if (uccello()->useMultiDomains()) {
                $uccelloLink = env('APP_URL').'/'.$domain->id.'/'.request('moduleName').'/'.request('recordId');
            } else {
                $uccelloLink = env('APP_URL').'/'.request('moduleName').'/'.request('recordId');
            }

            $parameters->body = new \StdClass;
            $parameters->body->content = (request('description') ?? '').
                (request('moduleName')!=null && request('recordId')!=null ? ' - '.$uccelloLink : '');

            $parameters->body->contentType = "Text";
        }

        $graph->createRequest('PATCH', '/me/calendars/'.request('calendarId').'/events/'.request('id'))
                    ->attachBody($parameters)
                    ->setReturnType(Model\Event::class)
                    ->execute();

        return ['success' => true];
    }

    public function delete(Domain $domain, Module $module)
    {
        //https://docs.microsoft.com/en-us/graph/api/group-delete-event?view=graph-rest-1.0

        $accountController = new AccountController();
        $graph = $accountController->initClient(request('accountId'));

        $getEventUrl = '/me/calendars/'.request('calendarId').'/events/'.request('id');
        $returnData = $graph->createRequest('DELETE', $getEventUrl)
                        ->execute();
    }
}
