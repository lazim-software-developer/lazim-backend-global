<?php

namespace App\Filament\Resources\Visitor\FlatVisitorResource\Pages;

use App\Filament\Resources\Visitor\FlatVisitorResource;
use App\Models\Visitor\FlatVisitor;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFlatVisitor extends CreateRecord
{
    protected static string $resource = FlatVisitorResource::class;
  
}
