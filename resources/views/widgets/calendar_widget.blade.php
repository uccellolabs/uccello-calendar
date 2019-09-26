<div class="card" style="margin: 0;">
    <div class="card-content">
        <div id="calendar"></div>
    </div>
</div>

@section('extra-meta')
<meta name="calendar-events-url" content="{{ ucroute('calendar.events.all', $domain, $module) }}">
@append

@section('css')
{{ Html::style(mix('css/app.css', 'vendor/uccello/calendar')) }}
<style>
    #calendar h2 {
        font-size: 16px;
        font-weight: bold;
    }
</style>
@append

@section('script')
{{ Html::script(mix('js/widgets/calendar.js', 'vendor/uccello/calendar')) }}
@append