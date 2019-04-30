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
@append

@section('sidebar-main-menu-after')
    <li style="position: relative">
        <a class="subheader">
            {{ uctrans('calendars', $module) }}
        </a>
        <a href="{{ ucroute('calendar.manage', $domain, $module) }}" style="position: absolute; right: 0; top: 0;" data-tooltip="{{ uctrans('manage_accounts', $module) }}" data-position="right">
            <i class="material-icons">settings</i>
        </a>
    </li>

    <li>
        <ul class="collapsible collapsible-accordion">
            @foreach ($accounts as $i => $account)
            <li class="submenu">
                <a href="javascript:void(0)" class="collapsible-header" tabindex="0">
                    <span>{{ $account->username }}</span>
                </a>
                <div class="collapsible-body">
                    <ul>
                        @foreach ($calendars as $calendar)
                            @continue($calendar->service !== $account->service_name)
                            <li>
                                <a href="#" class="calendar-name" data-account-id="{{ $account->id }}" data-calendar-id="{{ $calendar->id }}" data-readonly="{{ $calendar->read_only ? 'true' : 'false' }}" style="margin-left: 0">
                                    <i class="material-icons is-active">@if($calendar->disabled)check_box_outline_blank @else check_box @endif</i>
                                    <span>{{ $calendar->name }}</span>
                                    <i class="material-icons right" style="color: {{ $calendar->color }}">stop</i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            @endforeach

        </ul>
    </li>
@append

@section('content')
<div id="calendar-loader" class="row" style="margin-bottom: 0">
    <div class="col s12">
        <div class="progress transparent" style="margin: 0">
            <div class="indeterminate green"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
      <div class="card" style="margin-top: 0">
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
            <a class="waves-effect red white-text btn-small right hide delete"><i class="material-icons">delete</i></a>
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

                <div class="input-field col s12">
                    <select id="all_calendars">
                        @foreach ($calendars as $calendar)
                            @continue($calendar->disabled || $calendar->read_only)
                            <option value="{!! $calendar->id !!}"
                                data-calendar-type="{{ $calendar->service }}"
                                data-account-id="{{ $calendar->accountId }}">{{ $calendar->name }}</option>
                        @endforeach
                    </select>
                    <label>{{ uctrans('calendar', $module) }}</label>
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


        </form>
      </div>
    </div>
    <div class="modal-footer">
        <a class="btn-flat modal-close waves-effect" data-dismiss="modal">{{ uctrans('cancel', $module) }}</a>
        <a class="btn-flat waves-effect green-text save" data-dismiss="modal">{{ uctrans('event.save', $module) }}</a>
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

