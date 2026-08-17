<x-layouts::app.sidebar :title="$title ?? null">
    <main class="min-h-screen pt-16 lg:pr-64">
        <div class="px-margin-mobile py-8 md:px-margin-desktop">
            {{ $slot }}
        </div>
    </main>
</x-layouts::app.sidebar>
