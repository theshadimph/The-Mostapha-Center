<?php

namespace App\Enums;

/**
 * The Book (and location) fields an admin can map an Excel column onto
 * during the import wizard's column-mapping step. Deliberately a fixed,
 * known-safe target list rather than free-form field names.
 */
enum ImportTargetField: string
{
    case Title = 'title';
    case Author = 'author';
    case Editors = 'editors';
    case Commentators = 'commentators';
    case Annotators = 'annotators';
    case Curators = 'curators';
    case Category = 'category';
    case Publisher = 'publisher';
    case Isbn = 'isbn';
    case PublicationYear = 'publication_year';
    case Edition = 'edition';
    case Pages = 'pages';
    case Language = 'language';
    case Description = 'description';
    case Barcode = 'barcode';
    case RoomCode = 'room_code';
    case WallCode = 'wall_code';
    case SectionCode = 'section_code';
    case ShelfCode = 'shelf_code';
    case Position = 'position';

    public function label(): string
    {
        return match ($this) {
            self::Title => __('العنوان'),
            self::Author => __('المؤلف'),
            self::Editors => __('المحققون'),
            self::Commentators => __('الشارحون'),
            self::Annotators => __('المحشّون'),
            self::Curators => __('من اعتنى بهم'),
            self::Category => __('التصنيف'),
            self::Publisher => __('الناشر'),
            self::Isbn => __('رقم الردمك (ISBN)'),
            self::PublicationYear => __('سنة النشر'),
            self::Edition => __('الطبعة'),
            self::Pages => __('عدد الصفحات'),
            self::Language => __('اللغة'),
            self::Description => __('الوصف'),
            self::Barcode => __('الباركود / رقم التصنيف'),
            self::RoomCode => __('رقم الغرفة'),
            self::WallCode => __('رقم الحائط'),
            self::SectionCode => __('رمز القاعة'),
            self::ShelfCode => __('رمز الرف'),
            self::Position => __('الموقع على الرف'),
        };
    }

    /**
     * @return array<int, self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
