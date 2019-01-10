<?php

use Uccello\Calendar\CalendarTypes;

Route::name('uccello.calendar.')->group(function () {

    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }

    Route::resource('/calendars', "CalendarsController");

    Route::get($domainParam.'/calendar/{type}/events', 'Generic\EventsController@index')
        ->defaults('module', 'calendar')
        ->name('events.index');
    
    Route::get($domainParam.'/calendar/{type}/events/{accountId}', 'Generic\EventsController@index')
        ->defaults('module', 'calendar')
        ->name('events.calendars');

    Route::get($domainParam.'/calendar/{type}/signin', 'Generic\AuthController@signin')
        ->defaults('module', 'calendar')
        ->name('signin');

    Route::get($domainParam.'/calendar/{type}/authorize', 'Generic\AuthController@gettoken')
        ->defaults('module', 'calendar')
        ->name('gettoken');
    
    Route::get($domainParam.'/calendar/manage', 'MainController@index')
        ->defaults('module', 'calendar')
        ->name('manage');

    Route::get($domainParam.'/calendar/delete', 'MainController@removeAccount')
        ->defaults('module', 'calendar')
        ->name('removeAccount');

    Route::post($domainParam.'/calendar/add', 'MainController@addCalendar')
        ->defaults('module', 'calendar')
        ->name('addCalendar');

    Route::post($domainParam.'/calendar/remove/{type}/{id}', 'MainController@removeCalendar')
        ->defaults('module', 'calendar')
        ->name('removeCalendar');

});