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

                <div class="input-field col s10 m3 l3">
                    {{-- <i class="material-icons prefix">date_range</i> --}}
                    <input id="start_date" type="text" autocomplete="off" class="datepicker" data-format="{{ config('uccello.format.js.date') }}">
                    <label for="start_date">{{ uctrans('field.start_date', $module) }}</label>
                </div>

                <div class="input-field col s2 m1 l2">
                    <input id="start_time" type="time" autocomplete="off" class="timepicker">
                </div>

                <div class="input-field col s10 m3 l3">
                    <input id="end_date" type="text" autocomplete="off" class="datepicker" data-format="{{ config('uccello.format.js.date') }}">
                    <label for="end_date">{{ uctrans('field.end_date', $module) }}</label>
                </div>

                <div class="input-field col s2 m1 l2">
                    <input id="end_time" type="time" autocomplete="off" class="timepicker">
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
                        @foreach ($accounts as $account)
                        <optgroup label="{{ $account->username }}">
                            @foreach ($calendars[ $account->id ] as $calendar)
                                @continue($calendar->disabled || $calendar->read_only)
                                <option value="{!! $calendar->id !!}"
                                    data-calendar-type="{{ $calendar->service }}"
                                    data-account-id="{{ $calendar->accountId }}">{{ $calendar->name }}</option>
                            @endforeach
                        </optgroup>
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