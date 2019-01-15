<?php

namespace Uccello\Calendar\Http\Controllers;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;

class CalendarsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarAccount::find(request(['account']))->first();

        Generic\EventsController::addCalendar($domain, $account->service_name, $account->id, $module, $request);
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }

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
     * Undocumented function
     *
     * @param [type] $accountId
     * @return void
     */
    static function getDisabledCalendars($accountId)
    {
        $account = \Uccello\Calendar\CalendarAccount::find($accountId);
        $disabledCalendars = json_decode($account->disabled_calendars);
        if($disabledCalendars==null)
            $disabledCalendars = new \StdClass;
        return $disabledCalendars;
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function retrieve($id)
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
    public function destroy(Domain $domain, $accountId, $calendarId, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarAccount::find($accountId);

        Generic\EventsController::removeCalendar($domain, $account, $calendarId, $module, $request);
        
        return redirect(route('uccello.calendar.manage', ['domain' => $domain->slug]));
    }
}
