<x-layouts::library :title="__('عن المركز')">
    <section class="mx-auto mb-16 max-w-4xl text-center">
        <h1 class="mb-6 text-headline-xl text-primary">{{ __('عن مركز المصطفى') }}</h1>
        <p class="text-body-lg leading-relaxed text-on-surface-variant">
            {{ __('مركز المصطفى هو صرح علمي وتربوي يسعى إلى خدمة طلبة العلم الشرعي والباحثين والدارسين، من خلال توفير بيئة علمية أصيلة تجمع بين التعليم الشرعي المتين والبحث العلمي الرصين، وفي قلب هذا الصرح تقع مكتبته العلمية التي تُعد بوابة رقمية وواقعية لآلاف الكتب والمراجع.') }}
        </p>
    </section>

    <div class="grid grid-cols-1 gap-gutter md:grid-cols-2">
        <section class="rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-primary-fixed">
                <x-icon name="flag" class="text-2xl text-primary" />
            </div>
            <h2 class="mb-3 text-headline-md text-primary">{{ __('رسالتنا') }}</h2>
            <p class="text-body-md leading-relaxed text-on-surface-variant">
                {{ __('تقديم علم شرعي أصيل ومنهجية بحثية رصينة، وإتاحة مصادر المعرفة الإسلامية لكل راغب في العلم، عبر مكتبة علمية غنية ومنظمة تخدم طلاب العلم والباحثين على حد سواء.') }}
            </p>
        </section>

        <section class="rounded-2xl border border-outline-variant bg-surface-container-lowest p-8 shadow-sm">
            <div class="mb-4 flex size-14 items-center justify-center rounded-full bg-secondary-container">
                <x-icon name="visibility" class="text-2xl text-on-secondary-container" />
            </div>
            <h2 class="mb-3 text-headline-md text-primary">{{ __('رؤيتنا') }}</h2>
            <p class="text-body-md leading-relaxed text-on-surface-variant">
                {{ __('أن نكون مرجعاً علمياً وتربوياً رائداً، ومنارةً يُقصد إليها في طلب العلم الشرعي والبحث الأكاديمي، وأن تكون مكتبتنا نموذجاً في تيسير الوصول إلى المعرفة.') }}
            </p>
        </section>
    </div>

    <section class="my-16 rounded-2xl bg-primary-container p-10 text-on-primary-container">
        <div class="mx-auto flex max-w-3xl flex-col items-center gap-6 text-center">
            <x-icon name="account_balance" class="text-5xl" />
            <h2 class="text-headline-lg">{{ __('المكتبة العلمية') }}</h2>
            <p class="text-body-lg leading-relaxed opacity-90">
                {{ __('تضم مكتبة المركز آلاف الكتب والمراجع في الفقه والحديث والتفسير والعقيدة والتاريخ والأدب واللغة العربية، منظمة في قاعات وأرفف مخصصة يسهل الوصول إليها، مع نظام رقمي يتيح للزائر البحث عن أي كتاب ومعرفة موقعه بدقة داخل المكتبة.') }}
            </p>
            <a href="{{ route('search') }}" wire:navigate>
                <x-button variant="secondary">
                    <x-icon name="search" />
                    {{ __('تصفح فهرس المكتبة') }}
                </x-button>
            </a>
        </div>
    </section>

    <section class="mx-auto max-w-3xl text-center">
        <h2 class="mb-4 text-headline-md text-primary">{{ __('القائمون على المركز') }}</h2>
        <p class="text-body-md leading-relaxed text-on-surface-variant">
            {{ __('يشرف على مركز المصطفى الشيخ فادي موسى، إلى جانب نخبة من المدرسين والباحثين وأمناء المكتبة الذين يتولون رعاية البرامج العلمية والإشراف على تنظيم المكتبة وخدمة روادها.') }}
        </p>
    </section>
</x-layouts::library>
