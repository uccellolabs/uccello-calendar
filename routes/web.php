<?php

use Uccello\Calendar\CalendarTypes;

Route::name('uccello.calendar.')->group(function () {

    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }

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