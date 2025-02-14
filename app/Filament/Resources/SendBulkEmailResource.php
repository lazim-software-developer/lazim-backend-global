<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SendBulkEmailResource\Pages;
use App\Filament\Resources\SendBulkEmailResource\RelationManagers;
use App\Jobs\SendBulkEmailCSV;
use App\Models\BulkEmailManagement;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Filament\Tables\Actions\Action;

class SendBulkEmailResource extends Resource
{
    protected static ?string $model = BulkEmailManagement::class;
    protected static ?string $modelLabel = "Bulk Emails";
    protected static ?string $navigationGroup = 'Send Bulk Emails';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                        ->required()
                        ->label('Title'),
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload CSV')
                    // ->required()
                    ->disk('s3')
                    ->directory('dev')
                    ->maxSize(2048)
                    ->required()
                    ->rules('file|mimes:csv|max:2048')
                    ->helperText('Upload a valid CSV file containing email data. The file must have an "email" column.'),
                    // ->afterStateUpdated(function ($state) {
                    //     // Handle the CSV after state is updated (i.e., after upload)
                    //     $csvFile = $state;

                    //     // Validate CSV file content for "email" column
                    //     $this->validateCsvFile($csvFile);
                    // })
                Forms\Components\Select::make('email_template_id')
                    ->label('Select Email Template')
                    ->options(EmailTemplate::all()->pluck('name', 'id'))
                    ->required(),
            ]);
    }

    public function validateCsvFile($csvFile)
    {
        // Get the file path of the uploaded CSV
        $filePath = $csvFile->getRealPath();

        // Open the CSV file for reading
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Read the first row (header)
            $headers = fgetcsv($handle);

            // Check if 'email' column exists
            if (!in_array('email', $headers)) {
                // Return error if the email column is missing
                return back()->withErrors(['file_path' => 'CSV file must contain an "email" column']);
            }

            fclose($handle);
        } else {
            // Handle file read error
            return back()->withErrors(['file_path' => 'There was an issue reading the CSV file']);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
                ->defaultSort('updated_at', 'desc')
                ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->colors([
                        'pending' => 'warning',
                        'processing' => 'info',
                        'failed' => 'danger',
                        'success' => 'success',
                    ]),
                Tables\Columns\TextColumn::make('email_template_id')
                    ->label('Email Template')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => \App\Models\EmailTemplate::find($state)?->name ?? 'N/A'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'failed' => 'Failed',
                        'success' => 'Success',
                    ])
                    ->label('Filter by Status')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('sendEmails')
                    ->label('Send Emails')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(fn ($record) => static::submit($record))
                    ->requiresConfirmation()
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function submit($record)
    {
        $csvFile = $record->file_path;
        $templateId = $record->email_template_id;

        if (!$csvFile || !$templateId) {
            Notification::make()
            ->title("CSV file or email template is missing.")
            ->danger()
            ->send();
            return back();
        }

        SendBulkEmailCSV::dispatch($record->id);

        // Show success notification
        Notification::make()
            ->title("Emails are being processed.")
            ->success()
            ->send();

        // Optionally, you can redirect the user to another page or return back
        return back();
    }


    public static function getRelations(): array
    {
        return [
            'emailLogs' => RelationManagers\EmailLogListingRelationManager::class, // Add the relation manager here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSendBulkEmails::route('/'),
            'create' => Pages\CreateSendBulkEmail::route('/create'),
            'edit' => Pages\EditSendBulkEmail::route('/{record}/edit'),
        ];
    }
}
