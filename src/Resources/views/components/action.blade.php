@php
   $actionAttributes = $action['attributes'] ?? [];
   $confirm = $actionAttributes['confirm'] ?? null;
   $class = 'btn btn-md btn-' . ($action['method'] === 'DELETE' ? 'danger' : 'primary');
   $class = $actionAttributes['class'] ?? $class;
@endphp
@if($action && $action['formatter'] !== false)
    {!! $action['formatter'] !!}
@elseif ($action && $action['url'])
    @switch ($action['method'])
        @case ('POST')
        @case ('PUT')
        @case ('PATCH')
        @case ('DELETE')
            <form action="{{ $action['url'] }}" method="POST" style="display: inline;"
                @if($confirm) onsubmit="return confirm('{{ $confirm }}');" @endif>
                @csrf
                @if (!in_array($action['method'], ['POST', 'GET']))
                    @method($action['method'])
                @endif
                <button type="submit" class="{{ $class }}"
                    {!! $action['attributesString'] ?? '' !!}>
                    @if($action['icon'])
                        @if($action['formatIcon'])
                            {!! $action['icon'] !!}
                        @else
                            <i class="{{ $action['icon'] }}"></i>
                        @endif
                    @endif
                    {{ $action['title'] }}
                </button>
            </form>
            @break
        @default
            <a href="{{ $action['url'] }}" class="{{ $class }}"
            @if($confirm) onclick="return confirm('{{ $confirm }}');" @endif
            {!! $action['attributesString'] ?? '' !!}>
                @if($action['icon'])
                    @if($action['formatIcon'])
                        {!! $action['icon'] !!}
                    @else
                        <i class="{{ $action['icon'] }}"></i>
                    @endif
                @endif
                {{ $action['title'] }}
            </a>
    @endswitch
@endif