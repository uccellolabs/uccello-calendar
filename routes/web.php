<?php

use Uccello\Calendar\CalendarTypes;

Route::name('uccello.calendar.')->group(function () {

    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }


    // $calendarTypes = CalendarTypes::all();

    // foreach($calendarTypes as $calendarType)
    // {
    //     $name = $calendarType->name;
    //     // Overrided routes
    //     Route::get($domainParam.'/calendar/'.$name.'/events', ucfirst($name).'\\EventsController@index')
    //         ->defaults('module', 'calendar')
    //         ->name($name.'.events.index');

    //     Route::get($domainParam.'/calendar/'.$name.'/signin', ucfirst($name).'\\AuthController@signin')
    //         ->defaults('module', 'calendar')
    //         ->name($name.'.signin');

    //     Route::get($domainParam.'/calendar/'.$name.'/authorize', ucfirst($name).'\\AuthController@gettoken')
    //         ->defaults('module', 'calendar')
    //         ->name($name.'.gettoken');
    // }

    Route::get($domainParam.'/calendar/{type}/events', 'EventsController@index')
        ->defaults('module', 'calendar')
        ->name('events.index');

    Route::get($domainParam.'/calendar/{type}/signin', 'AuthController@signin')
        ->defaults('module', 'calendar')
        ->name('signin');

    Route::get($domainParam.'/calendar/{type}/authorize', 'AuthController@gettoken')
        ->defaults('module', 'calendar')
        ->name('gettoken');
});