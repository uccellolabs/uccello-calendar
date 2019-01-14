<?php

use Uccello\Calendar\CalendarTypes;

Route::name('uccello.calendar.')->group(function () {

    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }

    //GenericController
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
    

    //MainController
    Route::get($domainParam.'/calendar/manage', 'MainController@manageAccounts')
        ->defaults('module', 'calendar')
        ->name('manage');

    Route::get($domainParam.'/calendar', 'MainController@list')
        ->defaults('module', 'calendar')
        ->name('list');


    //AccountsController
    Route::get($domainParam.'/calendar/remove', 'AccountsController@destroy')
        ->defaults('module', 'calendar')
        ->name('removeAccount');


    //CalendarsController
    Route::post($domainParam.'/calendar/add', 'CalendarsController@create')
        ->defaults('module', 'calendar')
        ->name('addCalendar');

    Route::get($domainParam.'/calendar/remove/{accountId}/{id}', 'CalendarsController@destroy')
        ->defaults('module', 'calendar')
        ->name('removeCalendar');

});