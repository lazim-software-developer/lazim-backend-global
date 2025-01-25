<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Custom Section'; // Navigation group

    protected static string $view = 'filament.pages.custom-page';

    public static function route(): string
    {
        return '/custom-page';
    }
}
