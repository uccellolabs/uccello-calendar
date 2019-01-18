@forelse ($accounts as $account)
    <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    {{ $account->service_name }} 
                    <small> {{ $account->username }} </small>
                </h2>
            </div>
            <div class="body">
                <ul class="list-group">
                    
                        @forelse ($calendars[$loop->index] as $calendar)
                            
                            <li class="list-group-item">  
                                <input type="checkbox" class="calendar-toggle"
                                    data-accountid = {{ $account->id }} data-calendarid = {{ $calendar->id }}
                                    @if(!$calendar->disabled)checked @endif
                                    id="{{ $account->id.$calendar->id }}">
                                <label for="{{ $account->id.$calendar->id }}">{{ $calendar->name }}</label>
                                <a href="{{ ucroute('uccello.calendar.remove', $domain, $module, ['accountId'=> $account->id, 'id' => $calendar->id]) }}" 
                                    title="{{ uctrans('button.delete', $module) }}" 
                                    class="delete-btn" 
                                    data-config='{"actionType":"link","confirm":true,"dialog":{"title":"{{ uctrans('button.delete.confirm', $module) }}"}}'>
                                    <i class="material-icons">delete</i>
                                </a>
                                <span style="background-color: {{ $calendar->color }}" class="badge">{{ uctrans('color', $module) }}</span>
                            </li>   
    
                        @empty
                        <div
                            <span class="label label-default">{{ uctrans('none', $module) }}</span>
                            
                        </div>
                        @endforelse

                </ul>
                <a role="button" class="btn btn-primary waves-effect" data-toggle="collapse" aria-expanded="false"
                    href="#addCalendar{{$loop->index}}">
                    <i class="material-icons">add</i>
                    <span>{{ uctrans('calendar.create', $module) }}</span>
                </a>
                <div class="collapse .p-t-10" id="addCalendar{{$loop->index}}" aria-expanded="false" style="height: 0px;">
                    <div class="body">
                        <form method="POST" action=" {{ route('uccello.calendar.add', ['domain' => $domain->slug]) }} ">
                            <input type="hidden" name="account" value=" {{ $account->id }} ">
                            <div class="row clearfix">
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-18">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <input type="text" name="calendarName" class="form-control" placeholder="{{ uctrans('calendar.name', $module) }} ">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                                    <button type="submit" class="btn btn-primary btn-lg m-l-15 waves-effect">{{ uctrans('create', $module) }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <h3><span class="label label-info">{{ uctrans('none', $module) }}</span></h3>
@endforelse