@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-1 text-center">
    <x-heading size="lg">{{ $title }}</x-heading>
    <x-subheading>{{ $description }}</x-subheading>
</div>
