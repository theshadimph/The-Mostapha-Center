@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-body-md font-medium text-primary']) }}>
        {{ $status }}
    </div>
@endif
