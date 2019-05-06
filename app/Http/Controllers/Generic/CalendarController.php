<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;

class CalendarController extends Controller
{

    /**
     * Create a new calendar
     *
     * @param Domain|null $domain
     * @param Module $module
     * @return void
     */
    public function create(?Domain $domain, Module $module)
    {
        $this->preProcess($domain, $module, request());

        $account = \Uccello\Calendar\CalendarAccount::find(request(['account']))->first();

        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $account->service_name)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';

        $calendarType = new $calendarClass();
        return $calendarType->create($domain, $module, $account->id);

        return redirect(ucroute('calendar.manage', $domain, $module));
    }

    /**
     * Returns a list of all calendars for a given service
     *
     * @param Domain $domain
     * @param [type] $type
     * @param [type] $accountId
     * @param Module $module
     * @return void
     */
    public function list(Domain $domain, $type, $accountId, Module $module)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';

        $calendarType = new $calendarClass();
        $calendars = $calendarType->list($domain, $module, $accountId);

        $disabledCalendars = (array) $this->getDisabledCalendars($accountId);

        foreach($calendars as $calendar)
        {
            if(in_array($calendar->id, $disabledCalendars))
                $calendar->disabled = true;
            else
                $calendar->disabled = false;
        }
        return $calendars;
    }

    public function retrieve(Domain $domain, $accountId, $calendarId, Module $module)
    {
        $account = \Uccello\Calendar\CalendarAccount::where('id', $accountId)->get()->first();
        $calendars = $this->list($domain, $account->service_name, $accountId, $module);

        foreach($calendars as $calendar) {
            if($calendar->id == $calendarId) {
                return $calendar;
            }
        }
    }

    /**
     * Activate or desactivate a calendar
     *
     * @param Domain $domain
     * @param Module $module
     * @return void
     */
    public function toggle(Domain $domain, Module $module)
    {
        $this->preProcess($domain, $module, request());

        $calendarId = urldecode(request('id'));
        $accountId = request('account_id');

        $account = \Uccello\Calendar\CalendarAccount::findOrFail($accountId);

        $disabledCalendars = (array) $account->disabled_calendars;

        if (empty($disabledCalendars)) {
            $disabledCalendars = [];
        }

        $index = array_search($calendarId, $disabledCalendars);
        if($index > -1) {
            unset($disabledCalendars[$index]);
        }
        else {
            $disabledCalendars[ ] = $calendarId;
        }

        $account->disabled_calendars = $disabledCalendars;

        $account->save();

        return $account;
    }

    /**
     * Returns a list of all disabled calendars for a given account
     *
     * @param int $accountId
     * @return array
     */
    public static function getDisabledCalendars($accountId)
    {
        $account = \Uccello\Calendar\CalendarAccount::find($accountId);
        $disabledCalendars = $account->disabled_calendars;

        if(!$disabledCalendars) {
            $disabledCalendars = [ ];
        }

        return $disabledCalendars;
    }

    /**
     * Removes a calendar
     *
     * @param Domain $domain
     * @param [type] $accountId
     * @param [type] $calendarId
     * @param Module $module
     * @return void
     */
    public function destroy(Domain $domain, $accountId, $calendarId, Module $module)
    {
        $this->preProcess($domain, $module, request());

        $account = \Uccello\Calendar\CalendarAccount::find($accountId);


        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $account->service_name)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';

        $calendarType = new $calendarClass();
        return $calendarType->removeCalendar($domain, $module, $account, $calendarId);

        return redirect(ucroute('calendar.manage', $domain, $module));
    }

    public function getCategories(Domain $domain, $accountId, Module $module)
    {
        $this->preProcess($domain, $module, request());

        $account = \Uccello\Calendar\CalendarAccount::findOrFail($accountId);


        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $account->service_name)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';

        $calendarType = new $calendarClass();

        $account = \Uccello\Calendar\CalendarAccount::where('id', $accountId)->get()->first();
        return $calendarType->getCategories($domain, $module, $account);
    }

}