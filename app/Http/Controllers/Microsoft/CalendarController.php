<?php

namespace Uccello\Calendar\Http\Controllers\Microsoft;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;


class CalendarController extends Controller
{
    public function list(Domain $domain, Module $module, Request $request, $accountId)
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
            $calendar->read_only = !boolval($calendarListEntry->getCanEdit());

            if($calendar->color=='auto')
                $calendar->color = '#03A9F4';

            $calendars[] = $calendar;
        }

        return $calendars;
    }

    public function create(Domain $domain, Module $module, Request $request, $accountId)
    {
        $graph = $this->initClient($accountId);

        $parameters = new \StdClass;
        $parameters->Name = $request['calendarName'];

        $calendar = $graph->createRequest('POST', '/me/calendars')
                        ->attachBody($parameters)
                        ->setReturnType(Model\Calendar::class)
                        ->execute();
    }

    public function destroy(Domain $domain, Module $module, Request $request, CalendarAccount $account, $calendarId)
    {
        $graph = $this->initClient($account->id);

        $calendar = $graph->createRequest('DELETE', '/me/calendars/'.$calendarId)
                        ->setReturnType(Model\Calendar::class)
                        ->execute(); 
    }

    private function initClient($accountId)
    {
        $accountController = new AccountController();
        return $accountController->initClient($accountId);
    }
}