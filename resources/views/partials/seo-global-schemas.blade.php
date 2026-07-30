@php
    $schemas = $globalSchemas ?? app(\App\Services\Seo\SeoService::class)->globalSchemas();
@endphp
@foreach($schemas as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
