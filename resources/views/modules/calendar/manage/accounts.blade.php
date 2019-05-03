<div class="col s12 m6">
    <div class="card">
        <div class="card-content">
            <span class="card-title">
                <i class="material-icons primary-text left">people</i>
                {{ uctrans('accounts', $module) }}

                <a href="#"
                    class="btn-small btn-floating waves-effect green right dropdown-trigger"
                    data-target="add-account-dropdown"
                    data-constrain-width="false"
                    data-tooltip="{{ uctrans('button.add_account', $module) }}"
                    data-position="left">
                    <i class="material-icons">add</i>
                </a>

                <ul id="add-account-dropdown" class="dropdown-content">
                    @foreach ($calendarsType as $calendarType)
                        @continue($calendarType->name === 'tasks')
                        <li><a href="{{ ucroute('calendar.account.signin', $domain, $module, ['type' => $calendarType->name]) }}">{{ uctrans($calendarType->friendly_name, $module) }}</a></li>
                    @endforeach
                </ul>
            </span>

            <ul class="collection">
                <?php $cpt = 0; ?>
                @foreach ($calendarsType as $calendarType)
                    @foreach($accounts as $account)
                        @continue($account->service_name !== $calendarType->name)
                        <?php $cpt++; ?>
                        <li class="collection-item avatar">
                            <img src="{{ asset('vendor/uccello/calendar/images/'.$calendarType->icon) }}" alt="{{ $account->service_name }}" class="circle">
                            <span class="title"><b>{{ $account->username }}</b></span>
                            <p>{{ uctrans($calendarType->friendly_name, $module) }}</p>

                            @if ($calendarType->name !== 'tasks')
                            <a href="{{ ucroute('calendar.account.remove', $domain, $module, ['id' => $account->id]) }}"
                                class="secondary-content primary-text"
                                data-tooltip="{{ uctrans('button.delete_account', $module) }}"
                                data-position="left"
                                data-config='{"actionType":"link", "confirm":true}'>
                                <i class="material-icons">delete</i>
                            </a>
                            @endif
                        </li>
                    @endforeach
                @endforeach

                @if ($cpt === 0)
                <li class="collection-item grey lighten-4 center-align">
                    {{ uctrans('empty.account', $module) }}
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>