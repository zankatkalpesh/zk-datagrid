@props(['column', 'grid'])

@php
    $attr = $column['headingAttributes'];
    $attr['class'] = $attr['class'] ?? 'align-top';
    $attr['class'] .= ' grid-column';
    $attr['class'] .= $column['filterable'] ? ' grid-filter' : '';
    $attr['data-index'] = $column['index'];
    $attr['data-sortable'] = $column['sortable'] ? 'true' : 'false';
    if($column['column'] == $grid['data']['sort']) {
        $attr['data-order'] = $grid['data']['order'];
    }
@endphp
<th {{ $attributes->merge($attr) }}>
    @if ($column['sortable'])
        <a href="{{ $grid['baseUrl'] }}{{ $column['sortableLink'] }}" class="column-sort-link d-block">
            {{ $column['title'] }}
        </a>
    @else
        {{ $column['title'] }}
    @endif
    @if ($column['filterable'])
        <x-dynamic-component :component="$column['filterComponent']" :column="$column" :grid="$grid"></x-dynamic-component>
    @endif
</th>