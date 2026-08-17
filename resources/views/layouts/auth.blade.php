<x-layouts::auth.simple :title="$title ?? null">
    @isset($sidePanel)
        <x-slot:sidePanel>{{ $sidePanel }}</x-slot:sidePanel>
    @endisset

    {{ $slot }}
</x-layouts::auth.simple>
