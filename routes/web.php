<?php

use Uccello\Calendar\CalendarTypes;

Route::name('uccello.calendar.')->group(function () {

    // Adapt params if we use or not multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
    } else {
        $domainParam = '{domain}';
    }

    //Events
    Route::get($domainParam.'/calendar/{type}/events', 'Generic\EventController@list')
        ->defaults('module', 'calendar')
        ->name('events.list');
    
    Route::get($domainParam.'/calendar/events', 'Generic\EventController@all')
        ->defaults('module', 'calendar')
        ->name('events.all');

    Route::post($domainParam.'/calendar/event/{type}', 'Generic\EventController@create')
        ->defaults('module', 'calendar')
        ->name('events.create');


    //Calendars
    Route::get($domainParam.'/calendar/{type}/calendars/{accountId}', 'Generic\CalendarController@list')
        ->defaults('module', 'calendar')
        ->name('list');

    Route::get($domainParam.'/calendar/remove', 'Generic\CalendarController@destroy')
        ->defaults('module', 'calendar')
        ->name('remove');

    Route::post($domainParam.'/calendar/add', 'Generic\CalendarController@create')
        ->defaults('module', 'calendar')
        ->name('add');

    Route::get($domainParam.'/calendar/toggle/{accountId}/{id}', 'Generic\CalendarController@toggle')
        ->defaults('module', 'calendar')
        ->name('toggle');

    //Accounts
    Route::get($domainParam.'/calendar/{type}/signin', 'Generic\AccountController@signin')
        ->defaults('module', 'calendar')
        ->name('account.signin');

    Route::get($domainParam.'/calendar/{type}/authorize', 'Generic\AccountController@gettoken')
        ->defaults('module', 'calendar')
        ->name('account.gettoken');

    Route::get($domainParam.'/calendar/account/remove', 'Generic\AccountController@destroy')
        ->defaults('module', 'calendar')
        ->name('account.remove');
    

    //ModuleController
    Route::get($domainParam.'/calendar/manage', 'CalendarsController@manageAccounts')
        ->defaults('module', 'calendar')
        ->name('manage');

    Route::get($domainParam.'/calendar', 'CalendarsController@list')
        ->defaults('module', 'calendar')
        ->name('list');
   
});