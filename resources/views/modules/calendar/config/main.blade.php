@extends('uccello::modules.default.index.main')

@section('content')

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="card">
        <div class="header">
            <h2>
                {{uctrans('calendar.config', $module)}} 
                <small>{{uctrans('calendar.rules', $module)}}</small>
            </h2>
            
        </div>
        <form method="POST" action=" {{ route('uccello.calendar.config.save', ['domain' => $domain->slug]) }} ">
            <input type="hidden" name="rules_nb" value="{{ $rules_nb }}">
            <div class="body">
                <div class="irs-demo">
                    <b>{{uctrans('calendar.config.cron_delay', $module)}}</b>
                    <input type="text" class="js-range-slider" name="cron_delay" value="{{ $cron_delay }}" />
                </div>
                @for ($i = 0; $i < $rules_nb; $i++)
                <h2 class="card-inside-title">{{uctrans('calendar.rule', $module)}} #{{ $i+1 }}</h2>
                <div class="row clearfix">
                    <div class="col-sm-6">
                        {{-- <select id="module" name="module" class="form-control">
                            @foreach($modules as $module)
                                <option value={{$module->id}}>{{ $module->name }}</option>
                            @endforeach
                        </select> --}}
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="{{ uctrans('module', $module) }} id" name="module_{{ $i }}" value="{{ $rules['module_'.$i] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        {{-- <select id="field" name="field" class="form-control">
                            @foreach($fields as $field)
                                <option value={{$field->id}} data-module={{$field->module_id}}>{{ $field->name }}</option>
                            @endforeach
                        </select> --}}
                        <div class="form-group">
                            <div class="form-line">
                                <input type="text" class="form-control" placeholder="{{ uctrans('field', $module) }}" name="field_{{ $i }}" value="{{ $rules['field_'.$i] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
                <button type="submit" class="btn btn-block btn-lg btn-success waves-effect">
                        <i class="material-icons">save</i>
                        {{ uctrans('save', $module) }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('extra-script')
    {{ Html::script(ucasset('js/config.js', 'uccello/calendar')) }}
    {{ Html::style(ucasset('css/rangeslider.css', 'uccello/calendar')) }}
@endsection