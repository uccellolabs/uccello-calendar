<?php

// This makes it possible to adapt the parameters according to the use or not of the multi domains
if (!uccello()->useMultiDomains()) {
    $domainParam = '';
    $domainAndModuleParams = '{module}';
} else {
    $domainParam = '{domain}';
    $domainAndModuleParams = '{domain}/{module}';
}

Route::middleware('web', 'auth')
->name('calendar.')
->group(function() {

    // This makes it possible to adapt the parameters according to the use or not of the multi domains
    if (!uccello()->useMultiDomains()) {
        $domainParam = '';
        $domainAndModuleParams = '{module}';
    } else {
        $domainParam = '{domain}';
        $domainAndModuleParams = '{domain}/{module}';
    }

    //Events
    Route::get($domainParam.'/calendar/{type}/events', 'Generic\EventController@list')
        ->defaults('module', 'calendar')
        ->name('events.list');

    Route::get($domainParam.'/calendar/events', 'Generic\EventController@all')
        ->defaults('module', 'calendar')
        ->name('events.all');

    Route::post($domainParam.'/calendar/event', 'Generic\EventController@create')
        ->defaults('module', 'calendar')
        ->name('events.create');

    Route::get($domainParam.'/calendar/event', 'Generic\EventController@retrieve')
        ->defaults('module', 'calendar')
        ->name('events.retrieve');

    Route::post($domainParam.'/calendar/event/update', 'Generic\EventController@update')
        ->defaults('module', 'calendar')
        ->name('events.update');

    Route::post($domainParam.'/calendar/event/delete', 'Generic\EventController@delete')
        ->defaults('module', 'calendar')
        ->name('events.delete');

    Route::get($domainParam.'/calendar/events/classify', 'Generic\EventController@classify')
        ->defaults('module', 'calendar')
        ->name('events.classify');

    Route::get($domainParam.'/calendar/events/related', 'Generic\EventController@related')
        ->defaults('module', 'calendar')
        ->name('events.related');


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

    Route::get($domainParam.'/calendar/toggle', 'Generic\CalendarController@toggle')
        ->defaults('module', 'calendar')
        ->name('toggle');

    Route::get($domainParam.'/calendar/categories/{accountId}', 'Generic\CalendarController@getCategories')
        ->defaults('module', 'calendar')
        ->name('categories');

    //Accounts
    Route::get($domainParam.'/calendar/{type}/signin', 'Generic\AccountController@signin')
        ->defaults('module', 'calendar')
        ->name('account.signin');

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

    Route::get($domainParam.'/calendar/config', 'ConfigController@setup')
        ->defaults('module', 'calendar')
        ->name('config');

    Route::post($domainParam.'/calendar/config/save', 'ConfigController@saveConfig')
        ->defaults('module', 'calendar')
        ->name('config.save');

    Route::get($domainParam.'/calendar/config/process', 'ConfigController@processAutomaticAssignment')
        ->defaults('module', 'calendar')
        ->name('config.process');

    Route::get($domainParam.'/{module}/{id}/link', 'Core\DetailController@processSpecific')
        ->name('entity.detail');
});

Route::get($domainParam.'/calendar/{type}/authorize', 'Generic\AccountController@gettoken')
        ->defaults('module', 'calendar')
        ->middleware('web')
        ->name('calendar.account.gettoken');