<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * The center's real subject taxonomy — safe to run in production.
     */
    public function run(): void
    {
        $categories = [
            'الفقه الإسلامي' => 'دراسات وكتب الفقه على المذاهب الإسلامية',
            'الحديث الشريف' => 'كتب الحديث النبوي وعلومه',
            'التفسير وعلوم القرآن' => 'كتب تفسير القرآن الكريم وعلومه',
            'العقيدة' => 'كتب العقيدة الإسلامية',
            'اللغة العربية' => 'النحو والصرف والبلاغة والأدب اللغوي',
            'التاريخ الإسلامي' => 'تاريخ الأمة الإسلامية والحضارة الإسلامية',
            'السيرة النبوية' => 'كتب السيرة النبوية الشريفة',
            'الأدب' => 'الشعر والنثر والأدب العربي',
            'أصول الفقه' => 'قواعد وأصول استنباط الأحكام الشرعية',
            'الفتاوى' => 'مجموعات الفتاوى الشرعية',
        ];

        foreach ($categories as $name => $description) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($name, '-', null)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
