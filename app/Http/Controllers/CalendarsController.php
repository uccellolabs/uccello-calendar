<?php

namespace Uccello\Calendar\Http\Controllers;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;

class CalendarsController extends Controller
{
    public function manageAccounts(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        $calendarsType = \Uccello\Calendar\CalendarTypes::all();
        $accounts = \Uccello\Calendar\CalendarAccount::all();

        $calendars = [];

        foreach($accounts as $account){
            $calendarController = new Generic\CalendarController();
            $currentCalendars = $calendarController->list($domain, $account->service_name, $account->id, $module, $request);
            array_push($calendars, $currentCalendars);
        }

        $this->viewName = 'manage.main';

        return $this->autoView([
            'calendarsType' => $calendarsType,
            'accounts' => $accounts,
            'calendars' => $calendars,
        ]);
    }

    public function list(?Domain $domain, Module $module)
    {
        // Pre-process
        $this->preProcess($domain, $module, request());

        $this->viewName = 'index.main';

        // $calendarsType = \Uccello\Calendar\CalendarTypes::all();
        $accounts = \Uccello\Calendar\CalendarAccount::all();

        if ($accounts->count() === 0) {
            return redirect(ucroute('calendar.manage', $domain));
        }

        $calendars = [ ];
        $categories = [ ];

        foreach($accounts as $account) {
            $calendars[ $account->id ] = [ ];

            $calendarController = new Generic\CalendarController();
            $accountCalendars = $calendarController->list($domain, $account->service_name, $account->id, $module);
            $accountCategories = $calendarController->getCategories($domain, $account->id, $module);

            $calendars[ $account->id ] = $accountCalendars;
            $categories[ $account->id] = $accountCategories;
        }

        return $this->autoView([
            'accounts' => $accounts,
            'calendars' => $calendars,
            'categories' => $categories,
        ]);
    }
}
