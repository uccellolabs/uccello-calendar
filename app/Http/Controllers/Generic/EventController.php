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
     * @return array
     */
    protected function list(Domain $domain, $type, Module $module)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\EventController';
        $calendarType = new $calendarClass();
        return $calendarType->list($domain, $module);
    }

    public function all(Domain $domain, Module $module)
    {
        $types = \Uccello\Calendar\CalendarTypes::all();
        $globalEvents = [];

        foreach($types as $calendarType)
        {
            $events = $this->list($domain, $calendarType->name, $module);
            $globalEvents = array_merge($globalEvents, $events);
        }

        return $globalEvents;
    }

    protected function create(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->create($domain, $module);
    }

    public function retrieve(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->retrieve($domain, $module);
    }

    protected function update(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->update($domain, $module);
    }

    protected function delete(Domain $domain, Module $module)
    {
        $type = request('type');
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->delete($domain, $module);
    }
}
