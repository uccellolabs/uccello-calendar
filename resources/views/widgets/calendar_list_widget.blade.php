<?php
    $datatableId = 'calendar-list-widget';
    $relatedModule = ucmodule('calendar');
    $datatableColumns = Uccello::getDatatableColumns($relatedModule, null, 'related-list');
?>
<div class="card" style="margin: 0;">
    <div class="card-content">
        {{-- Title --}}
        <span class="card-title">
            {{-- Icon --}}
            <i class="material-icons left primary-text">{{ $relatedModule->icon }}</i>

            {{-- Label --}}
            {{ uctrans($relatedModule->name, $relatedModule) }}

            @if (auth()->user()->is_admin)
            <div class="right-align right">
                <select id="calendar-user-id">
                    <option value="all">Tous</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" @if($user->id === auth()->id())selected="selected"@endif>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="right-align right">
                <select id="calendar-period">
                    <option value="all">Tout</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="week" selected>Cette semaine</option>
                    <option value="month">Ce mois-ci</option>
                </select>
            </div>
        </span>

        {{-- Table --}}
        @include('uccello::modules.default.detail.relatedlists.table', [ 'datatableId' => $datatableId, 'datatableColumns' => $datatableColumns, 'relatedModule' => $relatedModule ])
    </div>
</div>

@section('script')
{{ Html::script(mix('js/widgets/calendar_list.js', 'vendor/uccello/calendar')) }}
@append