<?php

namespace Uccello\Calendar\Http\Controllers\Google;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;


class CalendarController extends Controller
{
    public function list(Domain $domain, Module $module, $accountId)
    {
        $oauthClient = $this->initClient($accountId);

        $service = new \Google_Service_Calendar($oauthClient);

        $calendarList = $service->calendarList->listCalendarList();

        $colors = $service->colors->get();

        $calendars = [];

        foreach($calendarList->getItems() as $calendarListEntry)
        {
            $calendar = new \StdClass;
            $calendar->name = $calendarListEntry->getSummary();
            $calendar->id = $calendarListEntry->getId();
            $calendar->service = 'google';
            $calendar->color = $colors->getCalendar()[$calendarListEntry->colorId]->background;
            $calendar->accountId = $accountId;

            $accessRole = $calendarListEntry->getAccessRole();
            if($accessRole=='owner' || $accessRole=='writer')
                $calendar->read_only = false;
            else
                $calendar->read_only = true;

            $calendars[] = $calendar;
        }

        return $calendars;
    }

    public function create(Domain $domain, Module $module, $accountId)
    {
        $oauthClient = $this->initClient($accountId);
        $service = new \Google_Service_Calendar($oauthClient);

        $calendar = new \Google_Service_Calendar_Calendar();
        $calendar->setSummary(request('calendarName'));
        $calendar->setTimeZone(config('timezone', 'UTC'));
        $createdCalendar = $service->calendars->insert($calendar);
    }

    public function destroy(Domain $domain, Module $module, CalendarAccount $account, $calendarId)
    {
        $oauthClient = $this->initClient($account->id);
        $service = new \Google_Service_Calendar($oauthClient);

        $service->calendars->delete($calendarId);
    }

    private function initClient($accountId)
    {
        $accountController = new AccountController();
        return $accountController->initClient($accountId);
    }
}