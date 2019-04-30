<?php

namespace Uccello\Calendar\Http\Controllers\Generic;

use Illuminate\Http\Request;
use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;

class AccountController extends Controller
{
    public function signin(Domain $domain, $type)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();

        $calendarClass =  $calendarTypeModel->namespace.'\AccountController';

        $calendarType = new $calendarClass();
        return $calendarType->signin();
    }

    public function gettoken(?Domain $domain, $type, Module $module)
    {
        $this->preProcess($domain, $module, request());

        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\AccountController';

        $calendarType = new $calendarClass();
        return $calendarType->gettoken($domain, $module);
    }

    public function destroy(?Domain $domain, Module $module)
    {
        $this->preProcess($domain, $module, request());

        $account = \Uccello\Calendar\CalendarAccount::find(request(['id']))->first();

        $account->delete();

        return redirect(ucroute('calendar.manage', $domain, $module));
    }
}