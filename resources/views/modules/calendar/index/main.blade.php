@extends('uccello::modules.default.index.main')

@section('page', 'calendar')

@section('extra-meta')
<meta name="calendar-manage-url" content="{{ ucroute('calendar.manage', $domain, $module) }}">
<meta name="calendar-events-url" content="{{ ucroute('calendar.events.all', $domain, $module) }}">
<meta name="calendar-retrieve-event-url" content="{{ ucroute('calendar.events.retrieve', $domain, $module) }}">
@append

@section('sidebar-main-menu-after')
    <li style="position: relative">
        <a class="subheader">
            {{ uctrans('calendars', $module) }}
        </a>
        <a href="{{ ucroute('calendar.manage', $domain, $module) }}" style="position: absolute; right: 0; top: 0;" data-tooltip="{{ uctrans('calendars.manage', $module) }}" data-position="right">
            <i class="material-icons">settings</i>
        </a>
    </li>

    {{-- <li>
        <a href="{{ ucroute('calendar.manage', $domain, $module) }}">
            <i class="material-icons">date_range</i>
            <span>{{ uctrans('calendars.manage', $module) }}</span>
        </a>
    </li> --}}

    @foreach ($calendars as $calendar)
    @continue($calendar->disabled)
    <li>
        <a href="#">
            <span>{{ $calendar->name }}</span>
            <i class="material-icons right" style="color: {{ $calendar->color }}">stop</i>
        </a>
    </li>
    @endforeach
@append

@section('content')
<div class="row">
    <div class="col s12">
      <div class="card">
        <div class="card-content">
            {{-- Calendar --}}
            <div id="calendar"></div>
        </div>
      </div>
    </div>
</div>
@endsection

@section('extra-content')
<div id="addEventModal" class="modal">
    <div class="modal-content">
        <h4>
            {{ uctrans('event.add', $module) }}
            <a class="modal-close waves-effect red white-text btn-small right"><i class="material-icons">delete</i></a>
        </h4>
      <div class="row">
        <form class="col s12">
            <input type="hidden" id="id">
            <div class="row">
                <div class="input-field col s12 m8">
                    {{-- <i class="material-icons prefix">short_text</i> --}}
                    <input id="subject" type="text" autocomplete="off">
                    <label for="subject">{{ uctrans('field.subject', $module) }}</label>
                </div>

                <div class="input-field col s12 m4">
                    {{-- <i class="material-icons prefix">location_on</i> --}}
                    <input id="location" type="text" autocomplete="off">
                    <label for="location">{{ uctrans('field.location', $module) }}</label>
                </div>

                <div class="input-field col s12 m4 l5">
                    {{-- <i class="material-icons prefix">date_range</i> --}}
                    <input id="start_date" type="text" autocomplete="off" class="datetimepicker" data-format="{{ config('uccello.format.js.datetime') }}">
                    <label for="start_date">{{ uctrans('field.start_date', $module) }}</label>
                </div>

                <div class="input-field col s12 m4 l5">
                    <input id="end_date" type="text" autocomplete="off" class="datetimepicker" data-format="{{ config('uccello.format.js.datetime') }}">
                    <label for="end_date">{{ uctrans('field.end_date', $module) }}</label>
                </div>

                <div class="col s12 m4 l2">
                    <p>
                        <label>
                            <input id="all_day" type="checkbox" />
                            <span>{{ uctrans('event.allday', $module) }}</span>
                        </label>
                    </p>
                </div>

                <div class="input-field col s3">
                    {{-- <i class="material-icons prefix">extension</i> --}}
                    <select id="entityType">
                        @foreach ($modules as $_module)
                            @continue(!$_module->model_class || $_module->isAdminModule())
                            <option value="{{ $_module->id }}">{{ uctrans($_module->name, $_module) }}</option>
                        @endforeach
                    </select>
                    <label for="entityType">{{ uctrans('field.entity_type', $module) }}</label>
                </div>

                <div class="input-field col s9">
                    <input id="entityId" type="text">
                    {{-- <label for="entityId">{{ uctrans('field.entity_id', $module) }}</label> --}}
                </div>

                <div class="input-field col s12">
                    {{-- <i class="material-icons prefix">subject</i> --}}
                    <textarea id="description" class="materialize-textarea"></textarea>
                    <label for="description">{{ uctrans('field.description', $module) }}</label>
                    <span class="helper-text">
                        {{ uctrans('field.info.new_line', $module) }}
                    </span>
                </div>
            </div>

            {{-- @foreach ($calendars as $calendar)
                @if(!$calendar->disabled)
                    <input name="calendars" type="radio" id='{!! $calendar->id !!}' value='{!! $calendar->id !!}'
                        class="radio-col-blue" data-calendar-type="{{ $calendar->service }}" data-account-id="{{ $calendar->accountId }}"
                        @if($calendar->read_only)
                        readonly="true" disabled="disabled"
                        @endif
                        >
                    <label for="{{ $calendar->id }}">{{ $calendar->name }}</label>
                @endif
            @endforeach --}}
        </form>
      </div>
    </div>
    <div class="modal-footer">
        <a class="btn-flat modal-close waves-effect" data-dismiss="modal">{{ uctrans('cancel', $module) }}</a>
        <a class="btn-flat waves-effect green-text" data-dismiss="modal">{{ uctrans('event.save', $module) }}</a>
    </div>
</div>
@append

@section('css')
    {{ Html::style(mix('css/app.css', 'vendor/uccello/calendar')) }}
@append

@section('script')
    {{ Html::script(mix('js/app.js', 'vendor/uccello/calendar')) }}
    {{-- {{ Html::script('https://maps.googleapis.com/maps/api/js?key=YOU_GOOGLE_API_KEY_GOES_HERE&libraries=places') }} --}}
@append

