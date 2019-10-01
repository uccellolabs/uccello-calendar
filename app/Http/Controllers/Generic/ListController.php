<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\ListController as DefaultListController;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Uccello\Core\Models\Filter;
use Uccello\Core\Models\Relatedlist;
use Uccello\Calendar\Http\Controllers\Generic\EventController;
use Uccello\Calendar\CalendarAccount;
use Carbon\Carbon;
use Uccello\Calendar\CalendarEntityEvent;
use Uccello\Core\Helpers\Uccello;

class ListController extends DefaultListController
{
    /**
     * @inheritDoc
     */
    public function process(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        // Selected filter
        $selectedFilterId = $request->input('filter') ?? null;
        $selectedFilter = Filter::find($selectedFilterId);

        // if (empty($selectedFilter)) {
        //     $selectedFilter = $module->filters()->where('name', 'filter.all')->first();
        //     $selectedFilter->conditions = json_decode('{"search":{"assigned_user":"'.auth()->id().'"}}'); // TODO: Très crade !!!! Ne pas mettre à jour le filtre
        //     $selectedFilter->save();
        // }

        // Get datatable columns
        $datatableColumns = Uccello::getDatatableColumns($module, $selectedFilterId);

        // Get filters
        $filters = Filter::where('module_id', $module->id)
            ->where('type', 'list')
            ->get();

        return $this->autoView(compact('datatableColumns', 'filters', 'selectedFilter'));
    }

    /**
     * @inheritDoc
     */
    public function processForContent(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        // Set start date if not defined
        if (!request('start')) {
            request()->merge(['start' => (new Carbon())->startOfWeek()->format('Y-m-d')]);
        }

        // Set end date if not defined
        if (!request('end')) {
            request()->merge(['end' => (new Carbon())->endOfWeek()->format('Y-m-d')]);
        }

        $itemByPage = 5;
        $page = 1;//à mettre à jour : prendre en compte la page affichée

        $calendarEvents = $this->getCalendarEvents(request('id'), request('src_module'), $domain, $module, $request);
        $dateFilteredEvents = [];
        foreach($calendarEvents as $event)
        {
            
            $start = Carbon::createFromFormat('d/m/Y', $event->start);
            $end = Carbon::createFromFormat('d/m/Y', $event->end);
            $start_max = Carbon::createFromFormat('Y-m-d', request('start'));
            $end_max = Carbon::createFromFormat('Y-m-d', request('end'));
            if($start<=$end_max && $end>=$start_max)
                $dateFilteredEvents[] = $event;
        }
        $countTotal = count($dateFilteredEvents);

        $pageFilteredEvents = array_slice($dateFilteredEvents, $page*$itemByPage, $itemByPage);
        $pageFilteredEvents = $this->setUpEvents($pageFilteredEvents);

        $records = [];
        $records['data'] = $pageFilteredEvents;
        $records['to'] = $itemByPage*$page; // TODO: set good data
        $records['total'] = $countTotal;

        return $records;
    }

    protected function getCalendarEvents($recordId, $moduleName, Domain $domain, Module $module, Request $request) {
        $events = [];

        $calEntityEvent = CalendarEntityEvent::where([
            'entity_id' => $recordId,
            'entity_class' => $moduleName
        ])->first();

        $eventController = new EventController();

        foreach($calEntityEvent->events as $event)
        {
            $event['type'] = $event['calendarType'];
            $event['eventId'] = $event['id'];
            $rEvent = $eventController->retrieve($domain, $module, false, $event);
            if($rEvent!=null)
                $events[] = $rEvent;
        }

        

        return $events;
    }

    protected function setUpEvents($events)
    {
        $records = [];
        foreach ($events as $event) {
            $calendarAccount = CalendarAccount::find($event->accountId);
            $relatedUser = $calendarAccount ? $calendarAccount->user : null;

            if($event->allDay)
                $format= config('uccello.format.php.date');
            else
                $format= config('uccello.format.php.datetime');

            $records[] = [
                // 'subject_html' => '<i class="material-icons primary-text left">people</i>'.$event['title'],
                'subject_html' => $event->title,
                'category_html' => !empty($event->categories) ? implode(',', $event->categories) : '',
                'start_date_html' => Carbon::createFromFormat($format, $event->start)->format($format),
                'end_date_html' => Carbon::createFromFormat($format, $event->end)->format($format),
                'location_html' => $event->location,
                'assigned_user_html' => $relatedUser ? '<a href="'.ucroute('uccello.detail', $this->domain, ucmodule('user'), [ 'id' => $relatedUser->getKey()]).'" class="primary-text">'.$relatedUser->recordLabel.'</a>' : '',
            ];
        }
        return $records;
    }

    /**
     * @inheritDoc
     */
    protected function buildContentQuery()
    {
        $query = parent::buildContentQuery();

        $userId = request()->has('user_id') ? request('user_id') : null;

        if (!auth()->user()->is_admin) {
            $query->where('assigned_user_id', auth()->id());
        } else {
            if ($userId === 'me') {
                $userId = auth()->id();
            }

            if ($userId && $userId !== 'all') {
                $query->where('assigned_user_id', $userId);
            }
        }

        if (request('start') && request('end')) {
            $query->where(function($query) {
                $query->whereBetween('datetime', [request('start'), request('end')])
                    ->orWhere(function($query) {
                        $query->where('datetime', '<', request('start'))
                        ->where('done', false);
                    });
            });
        }

        return $query;
    }
}
