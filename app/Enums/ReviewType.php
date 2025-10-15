<?php

namespace App\Enums;

enum ReviewType: int
{
    case FEEDBACK = 1;
    case MOVEIN = 2;
    case MOVEOUT = 3;
    case SALE_NOC = 4;
    case FIT_OUT = 5;
    case HOLIDAY_HOME = 6;
    case VISITOR = 7;

    public function label(): string
    {
        return match ($this) {
            self::FEEDBACK => 'feedback',
            self::MOVEIN => 'moveIn',
            self::MOVEOUT => 'moveOut',
            self::SALENOC => 'saleNoc',
            self::FITOUT => 'fitOut',
            self::HOLIDAYHOME => 'holidayHome',
            self::VISITOR => 'visitor',
        };
    }

    // optional: allow reverse lookup
    public static function fromLabel(string $label): ?self
    {
        return match ($label) {
            'feedback' => self::FEEDBACK,
            'moveIn' => self::MOVEIN,
            'moveOut' => self::MOVEOUT,
            'saleNoc' => self::SALENOC,
            'fitOut' => self::FITOUT,
            'holidayHome' => self::HOLIDAYHOME,
            // 'visitor' => self::VISITOR,
            default => 1,
        };
    }
}
