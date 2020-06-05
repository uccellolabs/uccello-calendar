<?php
    $datatableId = 'calendar-list-widget';
    $relatedModule = ucmodule('calendar');
    $datatableColumns = Uccello::getDatatableColumns($relatedModule, null, 'related-list');
    $datatableContentUrl = ucroute('uccello.list.content', $domain, $relatedModule, ['id' => $recordId, 'src_module' => $module->id])
?>

@if ($relatedModule && $relatedModule->isActiveOnDomain($domain))
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
                        <option value="all" selected>{{ trans('calendar::calendar.period.all') }}</option>
                        <option value="today">{{ trans('calendar::calendar.period.today') }}</option>
                        <option value="week">{{ trans('calendar::calendar.period.week') }}</option>
                        <option value="month">{{ trans('calendar::calendar.period.month') }}</option>
                        <option value="quarter">{{ trans('calendar::calendar.period.quarter') }}</option>
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
@endif