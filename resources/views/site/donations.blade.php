@php
    $channels = [
        ['icon' => 'menu_book', 'title' => __('وقف الكتب والمصادر'), 'description' => __('المساهمة في تزويد المكتبة بكتب ومصادر علمية جديدة تخدم طلبة العلم والباحثين.')],
        ['icon' => 'account_balance', 'title' => __('وقف صيانة وتطوير المكتبة'), 'description' => __('دعم صيانة قاعات المكتبة وأرففها وتطوير أنظمتها لتكون في خدمة الرواد دائماً.')],
        ['icon' => 'school', 'title' => __('دعم البرامج العلمية'), 'description' => __('المساهمة في تمويل الدروس والحلقات والبرامج التعليمية التي يقدمها المركز.')],
    ];
@endphp

<x-layouts::library :title="__('الوقف والتبرعات')">
    <section class="mx-auto mb-16 max-w-4xl text-center">
        <h1 class="mb-6 text-headline-xl text-primary">{{ __('الوقف والتبرعات') }}</h1>
        <p class="text-body-lg leading-relaxed text-on-surface-variant">
            {{ __('الوقف سنّة عظيمة وصدقة جارية، ومركز المصطفى يقوم أساساً على جهود الوقف والمتبرعين الكرام الذين يسهمون في استمرار رسالته العلمية والتربوية وخدمة مكتبته العلمية.') }}
        </p>
    </section>

    <div class="grid grid-cols-1 gap-gutter md:grid-cols-3">
        @foreach ($channels as $channel)
            <div class="flex flex-col items-center gap-4 rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 text-center shadow-sm">
                <div class="flex size-16 items-center justify-center rounded-full bg-primary-fixed">
                    <x-icon :name="$channel['icon']" class="text-3xl text-primary" />
                </div>
                <h3 class="text-headline-md text-primary">{{ $channel['title'] }}</h3>
                <p class="text-body-md leading-relaxed text-on-surface-variant">{{ $channel['description'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="mt-16 flex flex-col items-center gap-6 rounded-2xl bg-primary-container p-10 text-center text-on-primary-container">
        <x-icon name="volunteer_activism" class="text-5xl" />
        <h2 class="text-headline-lg">{{ __('كيف يمكنني المساهمة؟') }}</h2>
        <p class="max-w-2xl text-body-lg leading-relaxed opacity-90">
            {{ __('يسعدنا تواصلكم المباشر مع إدارة المركز لمعرفة تفاصيل قنوات الوقف والتبرع المتاحة حالياً، وسبل المساهمة العينية أو المالية في خدمة رسالة المركز ومكتبته العلمية.') }}
        </p>
        <a href="{{ route('contact') }}" wire:navigate>
            <x-button variant="secondary">
                <x-icon name="call" />
                {{ __('تواصل معنا للمساهمة') }}
            </x-button>
        </a>
    </section>
</x-layouts::library>
