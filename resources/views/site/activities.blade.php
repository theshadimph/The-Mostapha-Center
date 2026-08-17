@php
    $programs = [
        ['icon' => 'menu_book', 'title' => __('حلقات تحفيظ القرآن الكريم'), 'description' => __('برامج مستمرة لتحفيظ القرآن الكريم وتجويده لمختلف الأعمار والمستويات.')],
        ['icon' => 'school', 'title' => __('دروس العلم الشرعي'), 'description' => __('دروس منتظمة في الفقه والحديث والعقيدة والسيرة، يشرف عليها نخبة من المدرسين.')],
        ['icon' => 'groups', 'title' => __('ملتقيات ومحاضرات علمية'), 'description' => __('لقاءات ومحاضرات دورية تستضيف علماء وباحثين لمناقشة قضايا علمية ومعاصرة.')],
        ['icon' => 'diversity_3', 'title' => __('برامج المجتمع والدعوة'), 'description' => __('أنشطة توعوية واجتماعية تخدم روّاد المركز والمجتمع المحيط به.')],
    ];
@endphp

<x-layouts::library :title="__('الأنشطة والفعاليات')">
    <section class="mx-auto mb-16 max-w-4xl text-center">
        <h1 class="mb-6 text-headline-xl text-primary">{{ __('الأنشطة والفعاليات') }}</h1>
        <p class="text-body-lg leading-relaxed text-on-surface-variant">
            {{ __('يحرص مركز المصطفى على إثراء الحياة العلمية والاجتماعية لرواده من خلال برامج وأنشطة متنوعة، تجمع بين طلب العلم والتفاعل المجتمعي.') }}
        </p>
    </section>

    <div class="grid grid-cols-1 gap-gutter md:grid-cols-2">
        @foreach ($programs as $program)
            <div class="flex gap-5 rounded-2xl border border-outline-variant bg-surface-container-lowest p-6 shadow-sm">
                <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-fixed">
                    <x-icon :name="$program['icon']" class="text-2xl text-primary" />
                </div>
                <div>
                    <h3 class="mb-2 text-headline-md text-primary">{{ $program['title'] }}</h3>
                    <p class="text-body-md leading-relaxed text-on-surface-variant">{{ $program['description'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <section class="mt-16 flex flex-col items-center gap-6 rounded-2xl border-e-8 border-secondary bg-surface-container-low p-10 text-center">
        <x-icon name="event_available" class="text-5xl text-secondary" />
        <h2 class="text-headline-md text-primary">{{ __('تعرف على مواعيد البرامج القادمة') }}</h2>
        <p class="max-w-2xl text-body-md text-on-surface-variant">
            {{ __('لمعرفة مواعيد الدروس والفعاليات القادمة أو للتسجيل في أحد البرامج، يمكنكم التواصل مباشرة مع إدارة المركز.') }}
        </p>
        <a href="{{ route('contact') }}" wire:navigate>
            <x-button variant="primary">
                <x-icon name="call" />
                {{ __('تواصل معنا') }}
            </x-button>
        </a>
    </section>
</x-layouts::library>
