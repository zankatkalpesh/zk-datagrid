@props(['column', 'grid'])

@php
    $filterOptions = $column['options'];
    $filterOptions['type'] = $filterOptions['type'] ?? 'text';
    $attr = $filterOptions['attributes'] ?? [];
    if(!isset($attr['class'])) {
        match ($filterOptions['type']) {
            'select', 'multiselect' => $attr['class'] = 'form-select',
            'radio', 'checkbox' => $attr['class'] = 'form-check-input',
            default => $attr['class'] = 'form-control'
        };
    }
    $attr['class'] .= ' grid-filter-input';
    $attr['name'] = 'filters['.$column['index'].']';
    if($filterOptions['type'] === 'multiselect') {
        $attr['multiple'] = 'multiple';
        $attr['name'] .= '[]';
    }
    if($filterOptions['type'] === 'checkbox') {
        $attr['name'] .= (count($filterOptions['options']) > 1) ? '[]' : '';
    }
    $filterValue = $grid['data']['filters'][$column['index']] ?? '';
@endphp

<div class="mt-2 input-group input-{{ $filterOptions['type'] }}">
    @switch($filterOptions['type'])
        @case('select')
        @case('multiselect')
            <select {{ $attributes->merge($attr) }}>
                @foreach($filterOptions['options'] as $option)
                    <option value="{{ $option['value'] }}" @if(in_array($option['value'], (array) $filterValue)) selected @endif>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        @break
        @case('radio')
            @foreach($filterOptions['options'] as $option)
                <div class="form-check form-check-inline">
                    <input type="radio" {{ $attributes->merge($attr) }} value="{{ $option['value'] }}" @if($option['value'] == $filterValue) checked @endif />
                    <label class="form-check-label">{{ $option['label'] }}</label>
                </div>
            @endforeach
        @break
        @case('checkbox')
            @foreach($filterOptions['options'] as $option)
                <div class="form-check form-check-inline">
                    <input type="checkbox" {{ $attributes->merge($attr) }} value="{{ $option['value'] }}" @if(in_array($option['value'], (array) $filterValue)) checked @endif/>
                    <label class="form-check-label">{{ $option['label'] }}</label>
                </div>
            @endforeach
        @break
        @default
            <input type="{{ $filterOptions['type'] }}" {{ $attributes->merge($attr) }} value="{{ $filterValue }}">
    @endswitch
    <button class="btn btn-outline-primary btn-grid-filter" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
        </svg>
    </button>
    @if($filterValue != '')
        <button class="btn btn-outline-secondary btn-grid-filter-clear" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
            </svg>
        </button>
    @endif
</div>
