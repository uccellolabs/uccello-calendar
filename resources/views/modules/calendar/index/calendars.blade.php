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
        @forelse ($accounts as $i => $account)
        <li class="submenu">
            <a href="javascript:void(0)" class="collapsible-header truncate" tabindex="0">
                <span>{{ $account->username }}</span>
            </a>
            <div class="collapsible-body">
                <ul>
                    @foreach ($calendars[ $account->id ] as $calendar)
                        <li>
                            <a href="#" class="calendar-name truncate" data-account-id="{{ $account->id }}" data-calendar-id="{{ $calendar->id }}" data-readonly="{{ $calendar->read_only ? 'true' : 'false' }}" style="margin-left: 0" title="{{ $calendar->name }}">
                                <i class="material-icons is-active">@if($calendar->disabled)check_box_outline_blank @else check_box @endif</i>
                                <span>{{ $calendar->name }}</span>
                                <i class="material-icons right" style="color: {{ $calendar->color }}">stop</i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </li>
        @empty
        <li class="center-align white-text">{{ uctrans('empty.calendar', $module) }}</li>
        @endforelse

    </ul>
</li>