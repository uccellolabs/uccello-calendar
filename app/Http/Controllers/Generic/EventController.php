<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Uccello\Calendar\CalendarEntityEvent;
use Illuminate\Http\Request;
use stdClass;
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

    public function retrieve(Domain $domain, Module $module, $returnJson=true, $params=[])
    {
        if(request()->has('type'))
            $type = request('type');
        else
            $type = $params['type'];

        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass = $calendarTypeModel->namespace.'\EventController';

        $calendarType = new $calendarClass();
        return $calendarType->retrieve($domain, $module, $returnJson, $params);
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

    public static function generateEntityLink(Domain $domain)
    {
        $module = Module::where('name', 'calendar')->first();
        $uccelloLink = '';
        if(config('calendar.event.comment'))
            $uccelloLink = '<br/>#'.uctrans('before_url', $module).env('APP_NAME').' :';

        if (uccello()->useMultiDomains()) {
            $uccelloLink.= ' '.env('APP_URL').'/'.$domain->id.'/'.request('moduleName').'/'.request('recordId').'/link';
        } else {
            $uccelloLink.= env('APP_URL').'/'.request('moduleName').'/'.request('recordId').'/link';
        }

        if(config('calendar.event.comment'))
            $uccelloLink.=' - '.uctrans('after_url', $module).'.#';

        return $uccelloLink;
    }

    public function classify(Domain $d, Module $module, Request $request)
    {

        if($request->input('start') && $request->input('end'))
        {
            $events = [];
            $domains = Domain::all();
            foreach($domains as $domain)
            {
                $dom_events = $this->all($domain, $module);
                foreach($dom_events as $event)
                {
                    $params = [];
                    $params['calendarId']   = $event['calendarId'];
                    $params['accountId']    = $event['accountId'];
                    $params['eventId']      = $event['id'];
                    $params['type']         = $event['calendarType'];
                    $events[] = $this->retrieve($domain, $module, false, $params);
                }
            }

            foreach($events as $event)
            {
                if($event->moduleName && $event->recordId)
                {

                    $entityevent = CalendarEntityEvent::firstOrNew([
                        'entity_id' => $event->recordId,
                        'module_id' => ucmodule($event->moduleName)->id]);
                    if(!$entityevent->events || $entityevent==null)
                    {
                        $minifiyed_event = new stdClass;
                        $minifiyed_event->id = $event->id;
                        $minifiyed_event->calendarId = $event->calendarId;
                        $minifiyed_event->calendarType = $event->calendarType;
                        $minifiyed_event->accountId = $event->accountId;

                        $array = [];
                        $array[] = $minifiyed_event;

                        $entityevent->events = $array;
                    }
                    else
                    {
                        $exists = false;
                        foreach($entityevent->events as $a_event)
                        {

                            if($a_event->id === $event->id)
                            {
                                $exists = true;
                                break;
                            }
                        }

                        if(!$exists)
                        {
                            $minifiyed_event = new stdClass;
                            $minifiyed_event->id = $event->id;
                            $minifiyed_event->calendarId = $event->calendarId;
                            $minifiyed_event->calendarType = $event->calendarType;
                            $minifiyed_event->accountId = $event->accountId;
                            $allEvents = $entityevent->events;
                            $allEvents[] = $minifiyed_event;
                            $entityevent->events = $allEvents;
                        }
                    }

                    $entityevent->save();
                }
            }
        }
        else
            return "'stard' date and 'end' date are requiered";
    }

    public function related(Domain $d, Module $module, Request $request)
    {
        if($request->input('module_id') && $request->input('entity_id'))
        {
            $entityEvent = CalendarEntityEvent::where([
                'module_id' => $request->input('module_id'),
                'entity_id' => $request->input('entity_id')
            ])->first();
            return $entityEvent->events;
        }
    }
}
