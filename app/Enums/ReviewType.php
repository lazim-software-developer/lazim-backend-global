<?php
namespace App\Enums;

enum ReviewType: int
{
    case FEEDBACK = 1;
   // case BUILDING_REVIEW = 2;

    public function label(): string
    {
        return match ($this) {
            self::FEEDBACK => 'feedback',
           // self::BUILDING_REVIEW => 'building_review',
        };
    }

    // optional: allow reverse lookup
    public static function fromLabel(string $label): ?self
    {
        return match ($label) {
            'feedback' => self::FEEDBACK,
            //'building_review' => self::BUILDING_REVIEW,
            default => 1,
        };
    }
}
