@php
    $column['options']['options'] = $column['options']['options'] ?? [];
    $attributes = $column['options']['attributes'] ?? [];
    $class = 'form-control'; // Default class
    // Use switch to set the class based on the input type
    switch ($column['options']['type'] ?? null) {
        case 'select':
        case 'multiselect':
            $class = 'form-select';
        break;
        case 'radio':
        case 'checkbox':
            $class = 'form-check-input';
        break;
    }
    $attributes['class'] = $attributes['class'] ?? $class;
    if($column['options']['type'] === 'multiselect') {
        $attributes['multiple'] = 'multiple';
    }
    $formatAttributes = (function ($attributes) {
        return collect($attributes)->map(function ($value, $key) {
            if($key == 'class') {
                $value .= ' grid-filter-input'; 
            }
            return "{$key}=\"{$value}\"";
        })->implode(' ');
    })($attributes);
    $filterValue = $data['filters'][$column['index']] ?? '';
@endphp
<div class="mt-2 input-group input-{{ $column['options']['type'] }}">
    @switch($column['options']['type'])
        @case('select')
        @case('multiselect')
            <select {!! $formatAttributes !!}
                name="filters[{{ $column['index'] }}]{{ $column['options']['type'] === 'multiselect' ? '[]' : '' }}">
                @foreach($column['options']['options'] as $option)
                    <option value="{{ $option['value'] }}" @if(in_array($option['value'], (array) $filterValue)) selected @endif>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
        @break
        @case('radio')
            @foreach($column['options']['options'] as $option)
                <div class="form-check form-check-inline">
                    <input {!! $formatAttributes !!} type="radio" name="filters[{{ $column['index'] }}]"
                        value="{{ $option['value'] }}" @if($option['value']==$filterValue) checked @endif>
                    <label class="form-check-label">{{ $option['label'] }}</label>
                </div>
            @endforeach
        @break
        @case('checkbox')
            @foreach($column['options']['options'] as $option)
                <div class="form-check form-check-inline">
                    <input {!! $formatAttributes !!} type="checkbox" name="filters[{{ $column['index'] }}]{{ count($column['options']['options']) > 1 ? '[]' : '' }}"
                        value="{{ $option['value'] }}" @if(in_array($option['value'], (array) $filterValue)) checked @endif>
                    <label class="form-check-label">{{ $option['label'] }}</label>
                </div>
            @endforeach
        @break
        @default
            <input {!! $formatAttributes !!} type="{{ $column['options']['type'] }}" name="filters[{{ $column['index'] }}]"
            value="{{ $filterValue }}">
    @endswitch
    <button class="btn btn-outline-secondary btn-grid-filter" type="button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
        </svg>
    </button>
</div>
