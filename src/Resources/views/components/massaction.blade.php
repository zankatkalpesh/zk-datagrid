<form class="grid-mass-action-form" id="frm-mass-action-{{$uid}}">
    @csrf
</form>
<div class="row">
    <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
        <div class="input-group">
            <select class="form-select mass-action-input">
                <option value="">Select action</option>
                @foreach($massActions as $action)
                    @if(!empty($action['options']))
                        <optgroup label="{{ $action['title'] }}">
                            @foreach($action['options'] as $option)
                            <option data-url="{{ $option['url'] ?? $action['url'] }}"
                                data-method="{{ $option['method'] ?? $action['method'] }}"
                                data-params="{{ json_encode($option['attributes'] ?? $action['attributes'] ?? []) }}"
                                value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </optgroup>
                    @else
                    <option data-url="{{ $action['url'] }}" data-method="{{ $action['method'] }}"
                        data-params="{{ json_encode($action['attributes'] ?? []) }}" value="{{ $action['index'] }}">{{
                        $action['title'] }}</option>
                    @endif
                @endforeach
            </select>
            <button class="btn btn-outline-secondary btn-mass-action" type="button">
                Apply
            </button>
        </div>
    </div>
</div>