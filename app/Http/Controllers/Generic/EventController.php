<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Calendar\CalendarAccount;


class EventController extends Controller
{
    /**
     * Check user permissions
     */
    protected function checkPermissions()
    {
        $this->middleware('uccello.permissions:retrieve');
    }

    /**
     * Returns all events for a given service
     *
     * @param Domain $domain
     * @param [type] $type
     * @param Module $module
     * @param Request $request
     * @return void
     */
    protected function list(Domain $domain, $type, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventController';
        
        $calendarType = new $calendarClass();
        return $calendarType->list($domain, $module, $request);
    }

    protected function all(Domain $domain, Module $module, Request $request)
    {
        $types = \Uccello\Calendar\CalendarTypes::all();
        $globalEvents = [];
        foreach($types as $calendarType)
        {
            $events = $this->list($domain, $calendarType->name, $module, $request);
            $globalEvents = array_merge($globalEvents, $events);
        }

        return $globalEvents;
    }

    protected function create(Domain $domain, $type, Module $module,  Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->create($domain, $module, $request);
    }

    protected function update(Domain $domain, $type, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->create($domain, $module, $request);
    }

    protected function delete(Domain $domain, $type, Module $module,  Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->create($domain, $module, $request);
    }
}
