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
     * @param Request $request
     * @return void
     */
    public function create(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarAccount::find(request(['account']))->first();

        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $account->service_name)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';
        
        $calendarType = new $calendarClass();
        return $calendarType->addCalendar($domain, $module, $request, $account->id);    
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }

    /**
     * Returns a list of all calendars for a given service
     *
     * @param Domain $domain
     * @param [type] $type
     * @param [type] $accountId
     * @param Module $module
     * @param Request $request
     * @return void
     */
    public function list(Domain $domain, $type, $accountId, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';
        
        $calendarType = new $calendarClass();
        $calendars = $calendarType->list($domain, $module, $request, $accountId);    

        $disabledCalendars = $this->getDisabledCalendars($accountId);

        foreach($calendars as $calendar)
        {
            if(property_exists($disabledCalendars, $calendar->id))
                $calendar->disabled = true;
            else
                $calendar->disabled = false;
        }
        
        return $calendars;
    }

    public function retrieve(Domain $domain, $accountId, $calendarId, Module $module, Request $request)
    {
        $account = \Uccello\Calendar\CalendarAccount::where('id', $accountId)->get()->first();
        $calendars = $this->list($domain, $account->service_name, $accountId, $module, $request);
        foreach($calendars as $calendar)
        {
            if($calendar->id == $calendarId)
                return $calendar;   
        }
    }

    /**
     * Activate or desactivate a calendar
     *
     * @param Domain $domain
     * @param [type] $accountId
     * @param [type] $calendarId
     * @param Module $module
     * @param Request $request
     * @return void
     */
    public function toggle(Domain $domain, $accountId, $calendarId, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarAccount::find($accountId);

        $calendarsDisabled = json_decode($account->disabled_calendars);

        if($calendarsDisabled==null)
            $calendarsDisabled = new \StdClass;

        if(property_exists($calendarsDisabled, $calendarId))
            unset($calendarsDisabled->$calendarId);
        else
            $calendarsDisabled->$calendarId = 'true';

        $account->disabled_calendars = json_encode($calendarsDisabled);

        $account->save();
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }

    /**
     * Returns a list of all disabled calendars for a given account
     *
     * @param [type] $accountId
     * @return void
     */
    public static function getDisabledCalendars($accountId)
    {
        $account = \Uccello\Calendar\CalendarAccount::find($accountId);
        $disabledCalendars = json_decode($account->disabled_calendars);
        if($disabledCalendars==null)
            $disabledCalendars = new \StdClass;

        return $disabledCalendars;
    }

    /**
     * Removes a calendar
     *
     * @param Domain $domain
     * @param [type] $accountId
     * @param [type] $calendarId
     * @param Module $module
     * @param Request $request
     * @return void
     */
    public function destroy(Domain $domain, $accountId, $calendarId, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarAccount::find($accountId);


        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $account->service_name)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\CalendarController';
        
        $calendarType = new $calendarClass();
        return $calendarType->removeCalendar($domain, $module, $request, $account, $calendarId);   
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }

    

}