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

        $itemByPage = (int) request('length', 15);
        $page = (int) request('page', 1);

        $calendarEvents = $this->getCalendarEvents(request('id'), request('src_module'));
        $dateFilteredEvents = collect();

        if (request('start') && request('end')) {
            foreach($calendarEvents as $event) {
                $start = Carbon::createFromFormat('d/m/Y', $event->start);
                $end = Carbon::createFromFormat('d/m/Y', $event->end);
                $start_max = Carbon::createFromFormat('Y-m-d', request('start'));
                $end_max = Carbon::createFromFormat('Y-m-d', request('end'));

                if($start<=$end_max && $end>=$start_max) {
                    $dateFilteredEvents[] = $event;
                }
            }
        } else {
            $dateFilteredEvents = collect($calendarEvents);
        }

        $countTotal = $dateFilteredEvents->count();

        $limitStart = ($page-1)*$itemByPage;

        $pageFilteredEvents = $dateFilteredEvents->slice($limitStart)->take($itemByPage);
        $pageFilteredEvents = $this->setUpEvents($pageFilteredEvents);

        $pageTotal = ceil($countTotal / $itemByPage);

        $records = [];
        $records['data'] = $pageFilteredEvents;
        $records['current_page'] = $page;
        $records['last_page'] = $pageTotal;
        $records['first_page_url'] = ucroute('calendar.events.list.content', $domain, $module, ['page' => 1]);
        $records['last_page_url'] = ucroute('calendar.events.list.content', $domain, $module, ['page' => $pageTotal]);
        $records['prev_page_url'] = $page > 1 ? ucroute('calendar.events.list.content', $domain, $module, ['page' => $page-1]) : null;
        $records['next_page_url'] = $page < $pageTotal ? ucroute('calendar.events.list.content', $domain, $module, ['page' => $page+1]) : null;
        $records['page'] = ucroute('calendar.events.list.content', $domain, $module);
        $records['from'] = $limitStart;
        $records['to'] = $itemByPage*$page;
        $records['total'] = $countTotal;
        $records['per_page'] = $itemByPage;

        return $records;
    }

    protected function getCalendarEvents($recordId, $moduleId) {
        $events = [];

        $calEntityEvent = CalendarEntityEvent::where([
            'entity_id' => $recordId,
            'module_id' => $moduleId
        ])->first();

        if ($calEntityEvent) {
            $eventController = new EventController();

            foreach ($calEntityEvent->events as $event)
            {
                $params = [
                    'type' => $event->calendarType,
                    'eventId' => $event->id,
                    'accountId' => $event->accountId,
                    'calendarId' => $event->calendarId,
                ];

                $_event = $eventController->retrieve($this->domain, $this->module, false, $params);
                if ($_event !== null) {
                    $events[] = $_event;
                }
            }
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

            $calendarController = new \Uccello\Calendar\Http\Controllers\Generic\CalendarController();
            $calendar = $calendarController->retrieve($this->domain, $event->accountId, $event->calendarId, $this->module);

            $records[] = [
                // 'subject_html' => '<i class="material-icons primary-text left">people</i>'.$event['title'],
                'subject_html' => $event->title,
                'category_html' => !empty($event->categories) ? implode(',', $event->categories) : '',
                'start_date_html' => Carbon::createFromFormat($format, $event->start)->format($format),
                'end_date_html' => Carbon::createFromFormat($format, $event->end)->format($format),
                'location_html' => $event->location,
                'calendar_html' => $calendar->name,
                // 'assigned_user_html' => $relatedUser ? '<a href="'.ucroute('uccello.detail', $this->domain, ucmodule('user'), [ 'id' => $relatedUser->getKey()]).'" class="primary-text">'.$relatedUser->recordLabel.'</a>' : '',
>>>>>>> 9015796622095e007b6d2043d1dc1efd4e06be80
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
