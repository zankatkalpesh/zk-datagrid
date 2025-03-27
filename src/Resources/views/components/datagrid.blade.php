@props(['grid'])

@php
    $columns = $grid['columns'];
    $massActions = $grid['massActions'];
    $actions = $grid['actions'];
    $data = $grid['data'];
@endphp

<div id="grid-{{ $grid['uid'] }}" class="zk-datagrid row">
    <form class="grid-form" id="frm-{{ $grid['uid'] }}" method="GET" action="{{ $grid['baseUrl'] }}">
        @php
        // Recursive function to generate hidden input fields for nested arrays
        function renderHiddenInputs($data, $parentKey = '') {
            foreach ($data as $key => $value) {
                if(in_array($key, ['page','limit', 'search', 'filters', 'adv'])) {
                    continue;
                }
                $inputName = $parentKey ? "{$parentKey}[{$key}]" : $key;
                if (is_array($value)) {
                    renderHiddenInputs($value, $inputName);
                } else {
                    echo '<input type="hidden" name="' . e($inputName) . '" value="' . e($value) . '">';
                }
            }
        }
        // Call the function to render the hidden fields for all query parameters
        renderHiddenInputs(request()->query());
        @endphp
    </form>
    @if($grid['advancedSearch']) {!! $grid['advancedSearch'] !!} @endif
    <div class="col-12 mb-2 grid-input">
        <div class="row">
            <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
                <div class="input-group">
                    <label for="grid-limit-{{ $grid['uid'] }}" class="input-group-text">Display</label>
                    <select class="form-select grid-change" name="limit" id="grid-limit-{{ $grid['uid'] }}">
                        @foreach($data['perPageOptions'] as $option)
                            <option value="{{ $option }}" @if($option == $data['limit']) selected @endif>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <label for="grid-limit-{{ $grid['uid'] }}" class="input-group-text">results</label>
                </div>
            </div>
            <div class="col-sm-12 col-md-7 offset-lg-4 col-lg-5">
                @if($data['hasSearch'])
                    <div class="input-group">
                        <input type="text" placeholder="Search" class="form-control" name="search"
                            value="{{ $data['search'] }}">
                        <button class="btn btn-outline-primary btn-grid-search" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg>
                        </button>
                        @if($data['search'] != '')
                        <button class="btn btn-outline-secondary btn-grid-search-clear" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if(count($massActions))
        <x-dynamic-component :component="$grid['massActionComponent']" :massActions="$massActions" :grid="$grid"></x-dynamic-component>
    @endif
    <div class="col-12 mb-2">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        @if(count($massActions))
                            <th class="mass-action align-middle">
                                <div class="form-check form-check-inline m-0 px-2">
                                    <input style="width: 18px;" class="form-check select-all-input" type="checkbox">
                                </div>
                            </th>
                        @endif
                        @foreach($columns as $column)
                            <x-dynamic-component :component="$column['component']" :column="$column" :grid="$grid"></x-dynamic-component>
                        @endforeach
                        @if(count($actions))
                            <th class="row-action align-top">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="grid-items">
                    @php $index = $data['start'] ?? 1; @endphp
                    @foreach($data['items'] as $item)
                        <x-dynamic-component :component="$grid['itemComponent']" :item="$item" :grid="$grid" :index="$index"></x-dynamic-component>
                        @php $index++; @endphp
                    @endforeach
                    <tr class="grid-empty-data" {!! (count($data['items'])==0) ? '' : 'style="display: none;"' !!}>
                        <td colspan="{{ count($columns) + (count($massActions) ? 1 : 0) + (count($actions) ? 1 : 0) }}">
                            <div class="zk-datagrid-empty">{!! $data['emptyText'] !!}</div>
                        </td>
                    </tr>
                    <tr class="grid-data-loader" style="display: none;">
                        <td colspan="{{ count($columns) + (count($massActions) ? 1 : 0) + (count($actions) ? 1 : 0) }}">
                            <div class="zk-datagrid-loader">{!! $data['loadingText'] !!}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <x-dynamic-component :component="$grid['paginationComponent']" :grid="$grid"></x-dynamic-component>
</div>