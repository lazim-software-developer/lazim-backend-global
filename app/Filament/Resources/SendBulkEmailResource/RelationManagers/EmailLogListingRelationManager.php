<?php

namespace App\Filament\Resources\SendBulkEmailResource\RelationManagers;

use App\Models\EmailLog;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmailLogListingRelationManager extends RelationManager
{
    protected static string $relationship = 'emailLog'; // Define the relationship

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient_email')
                    ->label('Recipient Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'queued' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                    ]),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error Message'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ])
                    ->label('Filter by Status'),
            ]);
    }
}
