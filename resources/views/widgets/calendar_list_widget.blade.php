<?php
    $datatableId = 'calendar-list-widget';
    $relatedModule = ucmodule('calendar');
    $datatableColumns = Uccello::getDatatableColumns($relatedModule, null, 'related-list');
    $datatableContentUrl = ucroute('uccello.list.content', $domain, $relatedModule, ['id' => $recordId, 'src_module' => $module->id])
?>
<div class="card" style="margin: 0;">
    <div class="card-content">
        {{-- Title --}}
        <span class="card-title">
            {{-- Icon --}}
            <i class="material-icons left primary-text">{{ $relatedModule->icon }}</i>

            {{-- Label --}}
            {{ uctrans($relatedModule->name, $relatedModule) }}

            <div class="right-align right">
                <select id="calendar-period">
                    <option value="all" selected>{{ uctrans('period.all', $module) }}</option>
                    <option value="today">{{ uctrans('period.today', $module) }}</option>
                    <option value="week">{{ uctrans('period.week', $module) }}</option>
                    <option value="month">{{ uctrans('period.month', $module) }}</option>
                    <option value="quarter">{{ uctrans('period.quarter', $module) }}</option>
                </select>
            </div>
        </span>

        {{-- Table --}}
        @include('uccello::modules.default.detail.relatedlists.table', [ 'datatableId' => $datatableId, 'datatableColumns' => $datatableColumns, 'relatedModule' => $relatedModule, 'datatableContentUrl' => $datatableContentUrl ])
    </div>
</div>

@section('script')
{{ Html::script(mix('js/widgets/calendar_list.js', 'vendor/uccello/calendar')) }}
@append