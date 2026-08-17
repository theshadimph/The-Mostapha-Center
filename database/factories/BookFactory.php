<?php

namespace Database\Factories;

use App\Enums\BookStatus;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * A pool of real book titles so seeded/demo data reads like an actual
     * Islamic library catalog rather than gibberish placeholder text.
     *
     * @var list<string>
     */
    protected static array $titles = [
        'فتح الباري بشرح صحيح البخاري', 'زاد المعاد في هدي خير العباد', 'المقدمة',
        'إحياء علوم الدين', 'البيان والتبيين', 'تهافت الفلاسفة', 'كتاب الأغاني',
        'الرسالة', 'الموافقات في أصول الشريعة', 'صحيح البخاري', 'صحيح مسلم',
        'سنن الترمذي', 'تفسير الطبري', 'تفسير القرطبي', 'البداية والنهاية',
        'سيرة ابن هشام', 'الأم', 'المغني', 'شرح النووي على مسلم', 'الرحيق المختوم',
        'مدارج السالكين', 'الفوائد', 'روضة الطالبين', 'المستصفى', 'الاعتصام',
        'جامع بيان العلم وفضله', 'تاريخ الطبري', 'الكامل في التاريخ', 'وفيات الأعيان',
        'معجم الأدباء', 'العقد الفريد', 'مقامات الحريري', 'ديوان المتنبي',
        'نهج البلاغة', 'الرسالة القشيرية', 'قوت القلوب', 'التبصرة', 'بداية المجتهد',
        'أحكام القرآن', 'الجامع لأحكام القرآن',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasIsbn = fake()->boolean(60);

        return [
            'title' => fake()->randomElement(self::$titles),
            'author_id' => Author::factory(),
            'category_id' => Category::factory(),
            'description' => fake()->paragraphs(2, true),
            'publisher' => fake()->randomElement([
                'دار المعرفة - بيروت', 'دار الكتب العلمية', 'مؤسسة الرسالة',
                'دار ابن كثير', 'دار الفكر', 'دار الشروق', 'مكتبة الرشد',
            ]),
            'publication_year' => fake()->numberBetween(1960, 2024),
            'edition' => fake()->randomElement(['الأولى', 'الثانية', 'الثالثة', null]),
            'isbn' => $hasIsbn ? fake()->unique()->isbn13() : null,
            'pages' => fake()->numberBetween(80, 900),
            'language' => 'ar',
            'cover_image' => null,
            'status' => fake()->randomElement([
                BookStatus::Available, BookStatus::Available, BookStatus::Available,
                BookStatus::Borrowed, BookStatus::ReferenceOnly,
            ]),
            'views_count' => fake()->numberBetween(0, 500),
        ];
    }

    public function shelved(): static
    {
        return $this->state(function () {
            static $position = 0;
            $position++;

            return [
                'shelf_id' => Shelf::factory(),
                'position' => (string) $position,
            ];
        });
    }

    public function onShelf(Shelf $shelf, string $position): static
    {
        return $this->state(fn () => [
            'shelf_id' => $shelf->id,
            'position' => $position,
        ]);
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => BookStatus::Available]);
    }
}
