@props(['grid'])

@php
    $data = $grid['data'];
@endphp

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
                                <a class="page-link grid-page-link" href="{{ $grid['baseUrl'] }}{{ $page['url'] }}">{!! $page['label'] !!}</a>
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