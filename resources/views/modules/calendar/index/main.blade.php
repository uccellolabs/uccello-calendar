@extends('uccello::modules.default.index.main')

@section('page', 'calendar')

@section('content')

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">
                <a role="button" class="btn btn-primary waves-effect" href="{{ route('uccello.calendar.manage', ['domain' => 'default']) }}">
                    <i class="material-icons">settings</i>
                    <span>{{ uctrans('calendars.manage', $module) }}</span>
                </a>
                @foreach ($calendars as $calendar)
                    @if(!$calendar->disabled)
                    <span style="background-color: {{ $calendar->color }}" class="badge">{{ $calendar->name }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">
                <div id="calendar">
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-content')
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel">{{ uctrans('event.add', $module) }}</h4>
            </div>
            <input type="hidden" id="id">
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                    <input type="text" class="form-control" placeholder="Sujet" id="subject">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                    <input type="text" class="form-control" placeholder="Please choose a date..." id ="start_date">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Please choose a date..." id ="end_date">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <input type="checkbox" id="all_day" >
                            <label for="all_day">{{ uctrans('event.allday', $module) }}</label>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                    <input type="text" class="form-control" placeholder="Emplacement" id="location">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                    <textarea rows="4" class="form-control" placeholder="Description" id="description"></textarea>
                                    {{-- <div class="form-control" id="description"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Module slug" id="entityType">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Id" id="entityId">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="fd">
                            @foreach ($calendars as $calendar)
                                @if(!$calendar->disabled)
                                    <input name="calendars" type="radio" id='{!! $calendar->id !!}' value='{!! $calendar->id !!}' 
                                        class="radio-col-blue" data-calendar-type="{{ $calendar->service }}" data-account-id="{{ $calendar->accountId }}"
                                        @if($calendar->read_only)
                                        readonly="true" disabled="disabled"
                                        @endif
                                        >
                                    <label for="{{ $calendar->id }}">{{ $calendar->name }}</label>
                                @endif
                            @endforeach
                        </div>
                    </div>    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-red waves-effect delete"><i class="material-icons">delete</i></button>
                <button type="button" class="btn btn-primary waves-effect save" data-dismiss="modal">{{ uctrans('event.save', $module) }}</button>
                <button type="button" class="btn btn-link waves-effect cancel" data-dismiss="modal">{{ uctrans('cancel', $module) }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-css')
    {{ Html::style(ucasset('css/app.css', 'uccello/calendar')) }}
@show

@section('autoloader-script') @endsection

@section('extra-script')
    {{ Html::script(ucasset('js/app.js', 'uccello/calendar')) }}
    {{ Html::script(ucasset('js/fr.js', 'uccello/calendar')) }}
@endsection