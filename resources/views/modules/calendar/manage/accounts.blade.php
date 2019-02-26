<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="card">
        
        <div class="body">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs tab-nav-right" role="tablist">
                @foreach ($calendarsType as $calendarType)
                    @if ($loop->first)
                        <li role="presentation" class="active">
                            <a href="#{{$calendarType->name}}" data-toggle="tab" aria-expanded="true">{{ mb_strtoupper(uctrans($calendarType->friendly_name, $module)) }}</a>
                        </li>    
                    @else
                        <li role="presentation">
                            <a href="#{{$calendarType->name}}" data-toggle="tab" aria-expanded="false">{{ mb_strtoupper(uctrans($calendarType->friendly_name, $module)) }}</a>
                            
                        </li>    
                    @endif
                @endforeach
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                @foreach ($calendarsType as $calendarType)
                @if ($loop->first)
                    <div role="tabpanel" class="tab-pane fade active in" id="{{$calendarType->name}}">
                @else
                    <div role="tabpanel" class="tab-pane fade" id="{{$calendarType->name}}">        
                @endif
                        <b>{{ uctrans('accounts.stored', $module) }} : </b>
                        <br>
                        <ul class="list-group">
                            @forelse ($accounts as $account)
                                @if ($account->service_name == $calendarType->name)
                                    <li class="list-group-item"> {{ $account->username }} 
                                        @if ($calendarType->name != 'tasks')
                                        <a href="{{ ucroute('uccello.calendar.account.remove', $domain, $module, ['id' => $account->id]) }}" title="{{ uctrans('button.delete', $module) }}" class="delete-btn" data-config='{"actionType":"link","confirm":true,"dialog":{"title":"{{ uctrans('button.delete.confirm', $module) }}"}}'><i class="material-icons">delete</i></a>
                                        @endif
                                    </li>   
                                @endif     
                            @empty
                            <div
                                <span class="label label-default">{{ uctrans('none', $module) }}</span>
                                
                            </div>
                            @endforelse
                            
                        </ul>
                        @if ($calendarType->name != 'tasks')
                        <a role="button" class="btn btn-primary waves-effect" 
                            href="{{ route('uccello.calendar.account.signin', ['domain' => $domain->slug, 'type' => $calendarType->name]) }}">
                            <i class="material-icons">add</i>
                            <span>{{ uctrans('add_account', $module) }}</span>
                        </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="defaultModalLabel">{{ uctrans('confirm', $module) }}</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default">ANNULER</button>
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">SUPPRIMER</button>
                </div>
            </div>
        </div>
    </div>