<?php

namespace Database\Factories;

use App\Models\LibrarySection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibrarySection>
 */
class LibrarySectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $n = 0;
        $n++;

        return [
            'name' => fake()->randomElement([
                'قاعة العلوم الشرعية', 'قاعة التاريخ والأدب', 'قاعة المراجع العامة', 'قاعة المخطوطات',
            ]),
            'code' => 'F'.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'floor' => fake()->randomElement(['الأرضي', 'الأول', 'الثاني']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
