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
                @foreach ($accounts as $account)
                    @foreach ($calendars[$loop->index] as $calendar)
                        @if(!$calendar->disabled)
                        <span style="background-color: {{ $calendar->color }}" class="badge">{{ $calendar->name }}</span>
                        @endif
                    @endforeach
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
                <h4 class="modal-title" id="defaultModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sodales orci ante, sed ornare eros vestibulum ut. Ut accumsan
                vitae eros sit amet tristique. Nullam scelerisque nunc enim, non dignissim nibh faucibus ullamcorper.
                Fusce pulvinar libero vel ligula iaculis ullamcorper. Integer dapibus, mi ac tempor varius, purus
                nibh mattis erat, vitae porta nunc nisi non tellus. Vivamus mollis ante non massa egestas fringilla.
                Vestibulum egestas consectetur nunc at ultricies. Morbi quis consectetur nunc.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link waves-effect">SAVE CHANGES</button>
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