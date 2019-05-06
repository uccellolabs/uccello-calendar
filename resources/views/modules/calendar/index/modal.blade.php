<div id="addEventModal" class="modal">
    <div class="modal-content">
        <h4>
            {{ uctrans('event.add', $module) }}
            <a class="waves-effect red white-text btn-small right hide delete"><i class="material-icons">delete</i></a>
        </h4>
      <div class="row">
        <form class="col s12">
            <input type="hidden" id="id">

            <div class="row" style="margin-bottom: 0">
                <div class="input-field col s12 m8">
                    {{-- <i class="material-icons prefix">short_text</i> --}}
                    <input id="subject" type="text" autocomplete="off">
                    <label for="subject">{{ uctrans('field.subject', $module) }}</label>
                </div>

                <div class="input-field col s12 m4">
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
            </div>

            <div class="row" style="margin-bottom: 0">
                <div class="input-field col s12 m8">
                    {{-- <i class="material-icons prefix">location_on</i> --}}
                    <input id="location" type="text" autocomplete="off">
                    <label for="location">{{ uctrans('field.location', $module) }}</label>
                </div>

                <div class="input-field col s12 m4">
                    @foreach ($accounts as $account)
                        <select id="category-{{$account->id}}" class="category browser-default" data-account-id="{{ $account->id }}" style="display: none">
                            <option value="">&nbsp;</option>
                            @foreach ($categories[ $account->id ] as $category)
                                <option value="{{ $category->value }}">{{ $category->label }}</option>
                            @endforeach
                        </select>
                    @endforeach
                </div>
            </div>

            <div class="row" style="margin-bottom: 0">
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
            </div>

            <div class="row" style="margin-bottom: 0">
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