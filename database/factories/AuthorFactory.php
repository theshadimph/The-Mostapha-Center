<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'ابن حجر العسقلاني', 'ابن القيم الجوزية', 'ابن خلدون', 'أبو حامد الغزالي',
            'الإمام الشافعي', 'الإمام النووي', 'ابن كثير', 'الطبري',
            'ابن تيمية', 'الجاحظ', 'أبو الفرج الأصفهاني', 'الذهبي',
            'السيوطي', 'ابن الأثير', 'القرطبي', 'ابن رشد',
            'الرازي', 'ابن سينا', 'الفارابي', 'ابن الجوزي',
        ]);

        return [
            // slug omitted deliberately — see the comment in CategoryFactory.
            'name' => $name,
            'bio' => fake()->optional()->paragraph(),
        ];
    }
}
