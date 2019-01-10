<?php

namespace Uccello\Calendar\Http\Controllers;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;

class MainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        $calendarsType = \Uccello\Calendar\CalendarTypes::all();
        $accounts = \Uccello\Calendar\CalendarToken::all();

        $calendars = [];

        foreach($accounts as $account){
            $currentCalendars = Generic\EventsController::getCalendars($domain, $account->service_name, $account->id, $module, $request);
            // $calendars = array_merge($calendars, $currentCalendars);
            array_push($calendars, $currentCalendars);
        }

        $this->viewName = 'manage.main';

        return $this->autoView([
            'calendarsType' => $calendarsType,
            'accounts' => $accounts,
            'calendars' => $calendars,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeAccount(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarToken::find(request(['id']))->first();

        $account->delete();

        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }

    public function addCalendar(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarToken::find(request(['account']))->first();

        EventsController::addCalendar($domain, $account->service_name, $account->id, $module, $request);
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }

    public function removeCalendar(Domain $domain, $calendarId, Module $module,  Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarToken::find(request(['account']))->first();

        EventsController::removeCalendar($domain, $account->service_name, $account->id, $module, $request);
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }
}
