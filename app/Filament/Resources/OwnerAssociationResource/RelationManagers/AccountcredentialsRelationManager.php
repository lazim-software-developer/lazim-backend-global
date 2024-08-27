<?php

namespace App\Filament\Resources\OwnerAssociationResource\RelationManagers;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use App\Jobs\TestMailJob;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use App\Models\AccountCredentials;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action as ActionsAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;

class AccountcredentialsRelationManager extends RelationManager
{
    protected static string $relationship = 'mailCredentials';

    protected static ?string $title = 'Mail Configuration';

    protected static ?string $label = '';

    public function canCreate(): bool
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name !== 'Admin') {

            $isActiveExists = AccountCredentials::Where('oa_id', Filament::getTenant()->id)->exists();
            return !$isActiveExists;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->required()
                    ->rules([
                        'regex:/^[a-zA-Z0-9]+$/',
                    ])
                    ->placeholder('MAIL_USERNAME'),
                TextInput::make('email')
                    ->required()
                    ->minLength(6)
                    ->maxLength(30)
                    ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                    ->placeholder('MAIL_FROM_ADDRESS'),
                TextInput::make('password')
                    ->required()
                    ->minLength(8)
                    ->placeholder('MAIL_PASSWORD'),
                TextInput::make('mailer')
                    ->required()
                    ->string()
                    ->minLength(3)
                    ->maxLength(30)
                    ->placeholder('MAIL_MAILER'),
                TextInput::make('host')
                    ->required()
                    ->string()
                    ->minLength(10)
                    ->maxLength(35)
                    ->rules([
                        'regex:/^[a-z0-9.]+$/'
                    ])
                    ->placeholder('MAIL_HOST'),
                Select::make('port')
                    ->options([
                        '2525' => '2525',
                        '25' => '25',
                        '465' => '465',
                        '587' => '587'
                    ])
                    ->required()
                // ->integer()
                // ->maxValue(9999)
                // ->placeholder('MAIL_PORT')
                ,
                Select::make('encryption')
                    ->options([
                        'tls' => 'tls',
                        'ssl' => 'ssl'
                    ])
                    ->required()
                // ->string()
                // ->minLength(3)
                // ->maxLength(30)
                // ->placeholder('MAIL_ENCRYPTION')
                ,
                // Toggle::make('active')
                //     ->rules(['boolean', function (?Model $record) {
                //         return function (string $attribute, $value, Closure $fail) use ($record) {
                //             if (AccountCredentials::where('oa_id', Filament::getTenant()->id)->where('active', true)->whereNotIn('id', [$record?->id])->exists() && $record != null && $value) {
                //                 $fail('A Active Security already exists for this building.');
                //             }
                //             if (AccountCredentials::where('oa_id', Filament::getTenant()->id)->where('active', true)->exists() && $record == null && $value) {
                //                 $fail('A Active Security already exists for this building.');
                //             }
                //         };
                //     }])
                //     ->reactive(),

                Hidden::make('oa_id')
                    ->default(Filament::getTenant()?->id ?? auth()->user()?->owner_association_id),
                Hidden::make('created_by')
                    ->default(auth()->user()->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('host'),
                Tables\Columns\TextColumn::make('mailer'),
                // Tables\Columns\IconColumn::make('active')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-check-badge')
                //     ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->headerActions([

                Action::make('sendTestEmail')
                    ->visible(function(){
                        $roleName = Role::where('id', auth()->user()->role_id)->first()->name;
                        $credentialsExist = AccountCredentials::where('oa_id', auth()->user()->owner_association_id)->exists();
                        
                        return $roleName !== 'Admin' && $credentialsExist;
                        
                    })
                    ->label('Test Mail')
                    ->form([
                        TextInput::make('email')
                            ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                            ->label('Email Address')
                            ->required()
                            ->email(),
                    ])
                    ->action(function (array $data) {
                        $email = $data['email'];

                        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;

                        $credentials = AccountCredentials::where('oa_id', $tenant)->first();
                        $mailCredentials = [
                            'mail_host' => $credentials->host ,
                            'mail_port' => $credentials->port ,
                            'mail_username' => $credentials->username ,
                            'mail_password' => $credentials->password ,
                            'mail_encryption' => $credentials->encryption ,
                            'mail_from_address' => $credentials->email ,
                        ];

                        $OaName = Filament::getTenant()?->name ?? 'Admin';

                        TestMailJob::dispatch($email, $mailCredentials, $OaName);

                        Notification::make()
                            ->title('Email Sent')
                            ->body('If you haven’t received the mail, please check your Mail Credentials')
                            ->success()
                            ->duration(5000)
                            ->send();
                    }),

                Tables\Actions\CreateAction::make()
                    ->label('Create')
                    ->visible(fn() => Role::where('id', auth()->user()->role_id)->first()->name !== 'Admin'),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn() => Role::where('id', auth()->user()->role_id)->first()->name !== 'Admin'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Mail Configurations')
            ->emptyStateDescription('');;
    }
    // public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    // {
    //     return auth()->user()->role === 'OA';
    // }
}
