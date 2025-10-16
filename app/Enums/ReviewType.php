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
            self::SALE_NOC => 'saleNoc',
            self::FIT_OUT => 'fitOut',
            self::HOLIDAY_HOME => 'holidayHome',
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
            'saleNoc' => self::SALE_NOC,
            'fitOut' => self::FIT_OUT,
            'holidayHome' => self::HOLIDAY_HOME,
            // 'visitor' => self::VISITOR,
            default => 1,
        };
    }
}
