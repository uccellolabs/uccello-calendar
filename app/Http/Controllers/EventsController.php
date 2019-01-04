<?php

namespace Uccello\Calendar\Http\Controllers;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;


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
}
