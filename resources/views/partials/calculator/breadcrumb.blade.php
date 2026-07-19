@php($items = $breadcrumbs ?? [])

@if(!empty($items))
    <nav aria-label="breadcrumb" class="custom-breadcrumb mb-4">
        <ol class="breadcrumb mb-0 flex-wrap">
            @foreach($items as $index => $item)
                <li class="breadcrumb-item">
                    @if($index === count($items) - 1)
                        <span class="current">{{ $item['name'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
