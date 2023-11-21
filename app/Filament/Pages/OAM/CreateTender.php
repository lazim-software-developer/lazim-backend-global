<?php

namespace App\Filament\Pages\OAM;

use Filament\Pages\Page;

class CreateTender extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.oam.create-tender';

    protected static ?string $slug = '{budget}/tender/create';
}
