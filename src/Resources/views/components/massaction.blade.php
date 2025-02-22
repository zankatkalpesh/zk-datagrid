<form class="grid-mass-action-form" id="frm-mass-action-{{$uid}}">
    @csrf
</form>
@php
// dd($massActions);
@endphp
<div class="row">
    <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
        <div class="input-group">
            <select class="form-select mass-action-input">
                <option value="">{{ $massActionTitle ?? 'Select action' }}</option>
                @foreach($massActions as $action)
                    @if(!empty($action['options']))
                        <optgroup label="{{ $action['title'] }}" {!! $action['attributesString'] ?? '' !!}>
                            @foreach($action['options'] as $index => $option)
                            <option data-url="{{ $option['url'] ?? $action['url'] }}"
                                data-method="{{ $option['method'] ?? $action['method'] }}"
                                data-params="{{ json_encode($option['params'] ?? $action['params'] ?? []) }}"
                                value="{{ $option['value'] ?? $index }}" {!! $option['attributesString'] ?? '' !!}>{{ $option['label'] }}</option>
                            @endforeach
                        </optgroup>
                    @else
                    <option data-url="{{ $action['url'] }}" data-method="{{ $action['method'] }}"
                        data-params="{{ json_encode($action['params'] ?? []) }}" value="{{ $action['value'] ?? $action['index'] }}"
                        {!! $action['attributesString'] ?? '' !!}>{{$action['title'] }}</option>
                    @endif
                @endforeach
            </select>
            <button class="btn btn-outline-primary btn-mass-action" type="button">
                Apply
            </button>
        </div>
    </div>
</div>