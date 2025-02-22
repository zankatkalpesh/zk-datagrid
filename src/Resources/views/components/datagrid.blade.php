<div id="grid-{{ $uid }}" class="zk-datagrid row">
    <form class="grid-form" id="frm-{{ $uid }}" method="GET" action="{{ $baseUrl }}">
        @php
        // Recursive function to generate hidden input fields for nested arrays
        function renderHiddenInputs($data, $parentKey = '') {
            foreach ($data as $key => $value) {
                if(in_array($key, ['page','limit', 'search', 'filters'])) {
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
    <div class="col-12 mb-2 grid-input">
        <div class="row">
            <div class="col-sm-12 col-md-5 col-lg-3 mb-2 mb-md-0">
                <div class="input-group">
                    <label for="grid-limit-{{ $uid }}" class="input-group-text">Display</label>
                    <select class="form-select grid-change" name="limit" id="grid-limit-{{ $uid }}">
                        @foreach($data['perPageOptions'] as $option)
                            <option value="{{ $option }}" @if($option==$data['limit']) selected @endif>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <label for="grid-limit-{{ $uid }}" class="input-group-text">results</label>
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
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if(count($massActions))
        <div class="col-12 mb-2">
            <x-datagrid::massaction :uid="$uid" :baseUrl="$baseUrl" :massActions="$massActions" :massActionTitle="$massActionTitle ?? ''"></x-datagrid::massaction>
        </div>
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
                            @php
                                $class = $column['headingAttributes']['class'] ?? 'align-top';
                                $class .= ' grid-column';
                                $class .= $column['filterable'] ? ' grid-filter' : '';
                            @endphp
                            <th class="{{ $class }}" data-index="{{ $column['index'] }}"
                                data-sortable="{{ $column['sortable'] ? 'true' : 'false' }}"
                                @if ($data['sort']==$column['column']) data-order="{{ $data['order'] }}" @endif
                                {!! $column['headingAttributesString'] ?? '' !!}>
                                @if ($column['sortable'])
                                    <a href="{{ $baseUrl }}{{ $column['sortableLink'] }}" class="column-sort-link d-block">{{ $column['title'] }}</a>
                                @else
                                    {{ $column['title'] }}
                                @endif
                                @if ($column['filterable'])
                                    <x-dynamic-component :component="$column['filterComponent']" :uid="$uid" :baseUrl="$baseUrl" :column="$column" :data="$data"></x-dynamic-component>
                                @endif
                            </th>
                        @endforeach
                        @if(count($actions))
                            <th class="row-action align-top">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="grid-items">
                    @php $serialNo = $data['start'] ?? 1; @endphp
                    @foreach($data['items'] as $row)
                        <tr class="grid-row">
                            @if(count($massActions))
                                <td class="mass-action align-middle">
                                    <div class="form-check form-check-inline m-0 px-2">
                                        <input style="width: 16px;" class="form-check mass-row-input" type="checkbox" name="selected[]" value="{{ $row[$data['key']] }}">
                                    </div>
                                </td>
                            @endif
                            @foreach($columns as $column)
                                <td data-index="{{ $column['index'] }}" {!! $column['itemAttributesString'] ?? '' !!}>
                                    @if ($column['type'] === 'serial-no')
                                        {{ $serialNo++ }}
                                    @elseif ($column['escape'])
                                        {{ $row[$column['column']] }}
                                    @else
                                        {!! $row[$column['column']] !!}
                                    @endif
                                </td>
                            @endforeach
                            @if($row['actions'] && count($row['actions']))
                                <td class="row-action">
                                    @foreach($row['actions'] as $action)
                                        <x-dynamic-component :component="$action['component']" :uid="$uid" :baseUrl="$baseUrl" :action="$action" :row="$row"></x-dynamic-component>
                                    @endforeach
                                </td>
                            @endif
                        </tr>
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
    @if($data['hasPages'])
        <div class="col-12 mb-2 grid-pagination">
            <div class="row align-items-center">
                <div class="col-sm-12 col-md-5 mb-2 mb-md-0">
                    Showing {{ $data['start'] ?? 0 }} to {{ $data['end'] ?? 0 }} of {{ $data['total'] }} entries
                </div>
                <div class="col-sm-12 col-md-7">
                    <ul class="pagination flex-wrap justify-content-end mb-0">
                        @foreach($data['links'] as $page)
                            <li class="page-item @if(!$page['url']) disabled @endif @if($page['active']) active @endif">
                                @if($page['url'] && !$page['active'])
                                    <a class="page-link grid-page-link" href="{{ $baseUrl }}{{ $page['url'] }}">{!! $page['label'] !!}</a>
                                @else
                                    <span class="page-link">{!! $page['label'] !!}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
