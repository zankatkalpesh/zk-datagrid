@props(['grid', 'massActions'])

<div class="col-12 mb-2">
    <form class="grid-mass-action-form" id="frm-mass-action-{{ $grid['uid'] }}">
        @csrf
    </form>
    <div class="row">
        <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
            <div class="input-group">
                <select class="form-select mass-action-input">
                    <option value="">{{ $grid['massActionTitle'] ?? 'Select action' }}</option>
                    @foreach($massActions as $action)
                        @if(!empty($action['options']))
                            <optgroup label="{{ $action['title'] }}" {{ $attributes->merge($action['attributes']) }}>
                                @foreach($action['options'] as $index => $option)
                                    <option {{ $attributes->merge([
                                        ...($option['attributes'] ?? []),
                                        'data-url'=> $option['url'] ?? $action['url'],
                                        'data-method'=> $option['method'] ?? $action['method'],
                                        'data-params'=> json_encode($option['params'] ?? $action['params'] ?? []),
                                        'value'=> $option['value'] ?? $index
                                    ]) }}>{{ $option['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @else
                            <option {{ $attributes->merge([
                                ...($action['attributes'] ?? []),
                                'data-url'=> $action['url'],
                                'data-method'=> $action['method'],
                                'data-params'=> json_encode($action['params'] ?? []),
                                'value'=> $action['value'] ?? $action['index']
                                ]) }}>{{$action['title'] }}</option>
                        @endif
                    @endforeach
                </select>
                <button class="btn btn-outline-primary btn-mass-action" type="button">
                    Apply
                </button>
            </div>
        </div>
    </div>            
</div>