<?php

namespace App\Filament\Resources\PatrollingResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\PatrollingResource;
use Filament\Infolists\Components;

class ViewPatrolling extends ViewRecord
{
    protected static string $resource = PatrollingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // dd($this->record,$this->record->user->getFilamentName());
        return $infolist
            ->schema([
                Components\Section::make('Patrolling Details')
                    ->description('Information about the patrolling')
                    ->schema([
                        Components\TextEntry::make('building.name')
                            ->label('Building Name')
                            ->weight('bold'),
                        Components\TextEntry::make('user')
                            ->label('Patrolled By')
                            ->formatStateUsing(function(){
                                return $this->record->user->getFilamentName() ?? 'N/A';
                            })
                            ->icon('heroicon-o-user')
                            ->iconColor('primary'),
                        Components\IconEntry::make('is_completed')
                            ->label('Active')
                            ->boolean()
                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                        Components\TextEntry::make('ended_at')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                0 => 'Pending',
                                1 => 'Approved',
                                2 => 'Rejected',
                                default => 'Unknown',
                            })
                            ->color(fn ($state) => match ($state) {
                                0 => 'warning',
                                1 => 'success',
                                2 => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

}
