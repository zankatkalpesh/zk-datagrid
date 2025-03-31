@props(['item', 'grid', 'index'])

@php
    $key = $grid['data']['key'];
    $columns = $grid['columns'];
    $actions = $grid['actions'];
@endphp

<tr class="grid-row">
    @if(count($grid['massActions']))
        <td class="mass-action align-middle">
            <div class="form-check form-check-inline m-0 px-2">
                <input style="width: 16px;" class="form-check mass-row-input" type="checkbox" name="selected[]" value="{{ $item[$key] }}">
            </div>
        </td>
    @endif
    @foreach($columns as $column)
        <td data-index="{{ $column['index'] }}" {{ $attributes->merge($column['itemAttributes']) }}>
            @if ($column['type'] === 'serial-no')
                {{ $index }}
            @elseif ($column['escape'])
                {{ $item[$column['alias']] }}
            @else
                {!! $item[$column['alias']] !!}
            @endif
        </td>
    @endforeach
    @if($item['actions'] && count($item['actions']))
        <td class="row-action">
            @foreach($item['actions'] as $action)
                <x-dynamic-component :component="$action['component']" :uid="$grid['uid']" :baseUrl="$grid['baseUrl']" :action="$action" :row="$item"></x-dynamic-component>
            @endforeach
        </td>
    @endif
</tr>