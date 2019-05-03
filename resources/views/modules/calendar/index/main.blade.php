@extends('uccello::modules.default.index.main')

@section('page', 'calendar')

@section('extra-meta')
<meta name="calendar-manage-url" content="{{ ucroute('calendar.manage', $domain, $module) }}">
<meta name="calendar-events-url" content="{{ ucroute('calendar.events.all', $domain, $module) }}">
<meta name="calendar-create-event-url" content="{{ ucroute('calendar.events.create', $domain, $module) }}">
<meta name="calendar-retrieve-event-url" content="{{ ucroute('calendar.events.retrieve', $domain, $module) }}">
<meta name="calendar-update-event-url" content="{{ ucroute('calendar.events.update', $domain, $module) }}">
<meta name="calendar-delete-event-url" content="{{ ucroute('calendar.events.delete', $domain, $module) }}">
<meta name="calendar-toggle-url" content="{{ ucroute('calendar.toggle', $domain, $module) }}">
<meta name="calendar-date-format-js" content="{{ config('uccello.format.js.date') }}">
<meta name="calendar-datetime-format-js" content="{{ config('uccello.format.js.datetime') }}">
<meta name="calendar-time-format-js" content="{{ config('uccello.format.js.time') }}">
@append

@section('sidebar-main-menu-after')
    @include('calendar::modules.calendar.index.calendars')
@append

@section('content')
<div id="calendar-loader" class="row" style="margin-bottom: 0">
    <div class="col s12">
        <div class="progress transparent" style="margin: 0">
            <div class="indeterminate green"></div>
        </div>
    </div>
</div>

<div class="row" style="margin-bottom: 0">
    <div class="col s12">
      <div class="card" style="margin: 0">
        <div class="card-content">
            {{-- Calendar --}}
            <div id="calendar"></div>
        </div>
      </div>
    </div>
</div>
@endsection

@section('extra-content')
    @include('calendar::modules.calendar.index.modal')
@append

@section('css')
    {{ Html::style(mix('css/app.css', 'vendor/uccello/calendar')) }}
@append

@section('script')
    {{ Html::script(mix('js/app.js', 'vendor/uccello/calendar')) }}
@append

