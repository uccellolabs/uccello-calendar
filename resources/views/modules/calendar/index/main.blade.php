@extends('uccello::modules.default.index.main')

@section('page', 'calendar')

@section('content')

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">
                <a role="button" class="btn btn-primary waves-effect" href="{{ route('uccello.calendar.manage', ['domain' => 'default']) }}">
                    <i class="material-icons">settings</i>
                    <span>Manage calendars</span>
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
                <h4 class="modal-title" id="defaultModalLabel">Ajouter un événement</h4>
            </div>
            <div class="modal-body">
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Sujet" id="subject">
                            </div>
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Please choose a date..." id ="start_date">
                            </div>
                            <div class="form-line" id="bs_datepicker_container">
                                <input type="text" class="form-control" placeholder="Please choose a date..." id="end_date">
                            </div>
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="Emplacement" id="location">
                            </div>
                            <div class="form-line">
                                <input type="text" class="timepicker form-control" placeholder="Début" id="start_time">
                            </div>
                            <div class="form-line">
                                <input type="text" class="timepicker form-control" placeholder="Fin" id="end_time">
                            </div>
                            
                        </div>
                        <div class="fd">
                            @foreach ($calendars as $calendar)
                                @if(!$calendar->disabled)
                                    <input name="calendars" type="radio" id="{{ $calendar->id }}" value="{{ $calendar->id }}" class="radio-col-blue" data-calendar-type="{{ $calendar->service }}">
                                    <label for="{{ $calendar->id }}">{{ $calendar->name }}</label>
                                @endif
                            @endforeach
                        </div>
                    </div>    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link waves-effect save">SAVE CHANGES</button>
                <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
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