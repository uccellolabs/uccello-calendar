<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;


class EventsController extends Controller
{
    /**
     * Check user permissions
     */
    protected function checkPermissions()
    {
        $this->middleware('uccello.permissions:retrieve');
    }

    protected function index(Domain $domain, $type, Module $module,  Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventsController';
        
        $calendarType = new $calendarClass();
        return $calendarType->index($domain, $module, $request);
    }

    public static function getCalendars(Domain $domain, $type, $accountId, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventsController';
        
        $calendarType = new $calendarClass();
        $calendars = $calendarType->getCalendars($domain, $module, $request, $accountId);    

        $disabledCalendars = \Uccello\Calendar\Http\Controllers\CalendarsController::getDisabledCalendars($accountId);

        foreach($calendars as $calendar)
        {
            if(property_exists($disabledCalendars, $calendar->id))
                $calendar->disabled = true;
            else
                $calendar->disabled = false;
        }
        
        return $calendars;
    }

    public static function addCalendar(Domain $domain, $type, $accountId, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventsController';
        
        $calendarType = new $calendarClass();
        return $calendarType->addCalendar($domain, $module, $request, $accountId);    
    }

    public static function removeCalendar(Domain $domain, CalendarAccount $account, $calendarId, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $account->service_name)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventsController';
        
        $calendarType = new $calendarClass();
        return $calendarType->removeCalendar($domain, $module, $request, $account, $calendarId);   
    }
}