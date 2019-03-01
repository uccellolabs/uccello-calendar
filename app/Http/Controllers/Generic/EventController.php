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

        foreach($globalEvents as $event_short)
        {
            $request->request->add([
                'accountId' => $event_short['accountId'],
                'calendarId' => $event_short['calendarId'],
                'id' => $event_short['id']]
            );
            $full_event = json_decode($this->retrieve($domain, $event_short['calendarType'], $module, $request));
            (new \Uccello\Calendar\Http\Controllers\ConfigController)->processAutomaticAssignment($domain, $module, $request, $full_event);
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

    protected function retrieve(Domain $domain, $type, Module $module,  Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->retrieve($domain, $module, $request);
    }

    protected function update(Domain $domain, $type, Module $module, Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->update($domain, $module, $request);
    }

    protected function delete(Domain $domain, $type, Module $module,  Request $request)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->delete($domain, $module, $request);
    }
}
