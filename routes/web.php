<?php

Route::name('uccello.calendar.')->group(function () {

    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }

    // Overrided routes
    Route::get($domainParam.'/calendar/events', 'EventsController@index')
        ->defaults('module', 'calendar')
        ->name('events.index');

    Route::get($domainParam.'/calendar/signin', 'AuthController@signin')
        ->defaults('module', 'calendar')
        ->name('signin');

    Route::get($domainParam.'/calendar/authorize', 'AuthController@gettoken')
        ->defaults('module', 'calendar')
        ->name('gettoken');
});