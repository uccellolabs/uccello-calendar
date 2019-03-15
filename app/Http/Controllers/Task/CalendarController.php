<?php

namespace Uccello\Calendar\Http\Controllers\Task;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;


class CalendarController extends Controller
{
    public function list(Domain $domain, Module $module, Request $request, $accountId)
    {
        $calendars = [];
        $calendar = new \StdClass;
        $calendar->name = uctrans('my_tasks', $module);
        $calendar->id = 1;
        $calendar->service = 'tasks';
        $calendar->color = 'green';
        $calendar->accountId = $accountId;
        $calendar->read_only = false;
        $calendars[] = $calendar;

        return $calendars;
    }

    public function create(Domain $domain, Module $module, Request $request, $accountId)
    {
        // $graph = $this->initClient($accountId);

        // $parameters = new \StdClass;
        // $parameters->Name = $request['calendarName'];

        // $calendar = $graph->createRequest('POST', '/me/calendars')
        //                 ->attachBody($parameters)
        //                 ->setReturnType(Model\Calendar::class)
        //                 ->execute();
    }

    public function destroy(Domain $domain, Module $module, Request $request, CalendarAccount $account, $calendarId)
    {
        // $graph = $this->initClient($account->id);

        // $calendar = $graph->createRequest('DELETE', '/me/calendars/'.$calendarId)
        //                 ->setReturnType(Model\Calendar::class)
        //                 ->execute(); 
    }

    private function initClient($accountId)
    {
        // $accountController = new AccountController();
        // return $accountController->initClient($accountId);
    }
}