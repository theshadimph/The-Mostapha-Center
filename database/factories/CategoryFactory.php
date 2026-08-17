<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'الفقه الإسلامي', 'الحديث الشريف', 'التفسير وعلوم القرآن', 'العقيدة',
            'اللغة العربية', 'التاريخ الإسلامي', 'السيرة النبوية', 'الأدب',
            'الفلسفة', 'أصول الفقه', 'الفتاوى', 'التربية والتزكية',
        ]);

        return [
            // slug is intentionally omitted — Category::booted() generates it
            // from `name` at actual insert time. Resolving it here instead
            // would run the uniqueness check before any row in a batched
            // create() is persisted, letting duplicate names collide.
            'name' => $name,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
