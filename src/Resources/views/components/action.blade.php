@props(['uid', 'baseUrl', 'action', 'row'])

@php
   $actionAttributes = $action['attributes'] ?? [];
   $confirm = null;
   if(isset($actionAttributes['confirm'])) {
      $confirm = $actionAttributes['confirm'];
      unset($actionAttributes['confirm']);
   }
   $attr = $actionAttributes;
   if(!isset($attr['class'])) {
        $attr['class'] = 'btn btn-md btn-' . ($action['method'] === 'DELETE' ? 'danger' : 'primary');
   }
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
                <button type="submit" {{ $attributes->merge($attr) }}>
                    @if($action['icon'])
                        @if(is_string($action['icon']) && preg_match('/<.*?>/', $action['icon']))
                            {!! $action['icon'] !!}
                        @else
                            <i class="{{ $action['icon'] }}"></i>
                        @endif
                    @endif
                    @if ($action['escape'])
                        {{ $action['title'] }}
                    @else
                        {!! $action['title'] !!}
                    @endif
                </button>
            </form>
            @break
        @default
            <a href="{{ $action['url'] }}"
                @if($confirm) onclick="return confirm('{{ $confirm }}');" @endif
                {{ $attributes->merge($attr) }}>
                @if($action['icon'])
                    @if(is_string($action['icon']) && preg_match('/<.*?>/', $action['icon']))
                        {!! $action['icon'] !!}
                    @else
                        <i class="{{ $action['icon'] }}"></i>
                    @endif
                @endif
                @if ($action['escape'])
                    {{ $action['title'] }}
                @else
                    {!! $action['title'] !!}
                @endif
            </a>
    @endswitch
@endif