<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function signin(Domain $domain, $type)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();

        $calendarClass =  $calendarTypeModel->namespace.'\AccountController';

        $calendarType = new $calendarClass();
        return $calendarType->signin();
    }

    public function gettoken(Domain $domain, $type)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\AccountController';

        $calendarType = new $calendarClass();
        return $calendarType->gettoken();
    }

    public function destroy(?Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);

        $account = \Uccello\Calendar\CalendarAccount::find(request(['id']))->first();

        $account->delete();

        return redirect(ucroute('calendar.manage', $domain, $module));
    }
}