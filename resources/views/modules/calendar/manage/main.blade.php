@extends('uccello::modules.default.index.main')

@section('content')

<div class="block-header">
    <h2>{{ uctrans('accounts', $module) }}</h2>
</div>

@include('calendar::modules.calendar.manage.accounts')

<div class="block-header">
    <h2>{{ uctrans('calendars', $module) }}</h2>
</div>

@include('calendar::modules.calendar.manage.calendars')

@endsection

@section('extra-script')
    {{ Html::script(ucasset('js/calendar-manager.js', 'uccello/calendar')) }}
@endsection