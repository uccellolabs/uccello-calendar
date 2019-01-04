<?php

namespace Uccello\Calendar\Http\Controllers;

use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Core\Models\Domain;

class AuthController extends Controller
{
    public function signin(Domain $domain, $type)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        
        $calendarClass =  $calendarTypeModel->namespace.'\AuthController';
        
        $calendarType = new $calendarClass();
        return $calendarType->signin();
    }

    public function gettoken(Domain $domain, $type)
    {
        $calendarTypeModel = \Uccello\Calendar\CalendarTypes::where('name', $type)->get()->first();
        $calendarClass =  $calendarTypeModel->namespace.'\AuthController';
        
        $calendarType = new $calendarClass();
        return $calendarType->gettoken();   
    }
}