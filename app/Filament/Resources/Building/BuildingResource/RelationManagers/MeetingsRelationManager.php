<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Jobs\BeforeMeetingOcjob;
use App\Jobs\OwnerMeeting;
use App\Models\AccountCredentials;
use App\Models\Master\Role;
use App\Models\Meeting;
use App\Models\OwnerAssociation;
use App\Models\OwnerCommittee;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\DB;
use Parsedown;

class MeetingsRelationManager extends RelationManager
{
    protected static string $relationship = 'meetings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextArea::make('agenda')
                    ->minLength(3)
                    ->maxLength(255)
                    ->rows(5)
                    ->disabled()
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('date_time')
                    ->rules(['date'])
                    ->displayFormat('d-M-Y h:i A')
                // ->minDate(now())
                    ->disabled()
                    ->required()
                    ->default(now())
                    ->label('Meeting Date Time')
                    ->columnSpanFull(),
                TextArea::make('meeting_summary')
                    ->minLength(3)
                    ->maxLength(255)
                    ->rows(5)
                    ->columnSpanFull()
                    ->required()
                    ->live(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agenda'),
                TextColumn::make('date_time')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()
                Action::make('New Meeting')
                    ->button()
                    ->form([
                        Textarea::make('agenda')
                            // ->toolbarButtons([
                            //     'bold',
                            //     'bulletList',
                            //     'italic',
                            //     'link',
                            //     'orderedList',
                            //     'redo',
                            //     'undo',
                            // ])
                            ->required(),
                        DateTimePicker::make('date_time')
                            ->rules(['date'])
                            ->displayFormat('d-M-Y h:i A')
                            ->minDate(now())
                            ->required()
                            ->default(now())
                            ->label('Meeting Date Time'),

                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $Ownerlist = OwnerCommittee::where('building_id', $data['building_id'])->where('active', true)->pluck('user_id');
                        if ($Ownerlist->count() > 0) {
                            $meeting = Meeting::create([
                                'agenda'      => $data['agenda'],
                                'date_time'   => $data['date_time'],
                                'building_id' => $data['building_id'],
                            ]);
                            $userslist = User::whereIn('id', $Ownerlist)->get();
                            // Use Parsedown to convert Markdown to HTML
                            $parsedown  = new Parsedown();
                            $agendaHtml = $parsedown->text($meeting->agenda);

                            $tenant = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                            // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');

                            // if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            //     $oa_id            = DB::table('building_owner_association')->where('building_id', $livewire->ownerRecord->id)->where('active', true)->first()?->owner_association_id;
                            //     $emailCredentials = OwnerAssociation::find($oa_id)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                            //     foreach ($userslist as $owner) {
                            //         BeforeMeetingOcjob::dispatch($owner, $meeting, $agendaHtml, $emailCredentials);
                            //     }
                            // } else {
                            //     $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                            // }
                            $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
                            
                            $mailCredentials = [
                                'mail_host' => $credentials->host??env('MAIL_HOST'),
                                'mail_port' => $credentials->port??env('MAIL_PORT'),
                                'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                                'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                                'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                                'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
                            ];
                                foreach ($userslist as $owner) {
                                    BeforeMeetingOcjob::dispatch($owner, $meeting, $agendaHtml, $mailCredentials);
                                }

                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Meeting not created')
                                ->body('There are no OwnerCommittes for this buildings.')
                                ->send();
                        }
                    })
                    ->slideOver(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (array $data, $record) {
                        // Runs after the form fields are saved to the database.
                        $Ownerlist = OwnerCommittee::where('building_id', $record->building_id)->where('active', true)->pluck('user_id');
                        $userslist = User::whereIn('id', $Ownerlist)->get();
                        // Use Parsedown to convert Markdown to HTML
                        $parsedown          = new Parsedown();
                        $meetingSummaryHtml = $parsedown->text($record->meeting_summary);
                        $agendaHtml         = $parsedown->text($record->agenda);
                        $tenant             = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
                        $mailCredentials = [
                            'mail_host' => $credentials->host??env('MAIL_HOST'),
                            'mail_port' => $credentials->port??env('MAIL_PORT'),
                            'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                            'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                            'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                            'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
                        ];
                        // if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                        //     $oa_id            = DB::table('building_owner_association')->where('building_id', $record->building_id)->where('active', true)->first()?->owner_association_id;
                        //     $emailCredentials = OwnerAssociation::find($oa_id)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                        //     foreach ($userslist as $owner) {
                        //         OwnerMeeting::dispatch($owner, $record, $agendaHtml, $meetingSummaryHtml, $emailCredentials);
                        //     }
                        // } else {
                        //     $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
                        // }
                        foreach ($userslist as $owner) {
                            OwnerMeeting::dispatch($owner, $record, $agendaHtml, $meetingSummaryHtml, $mailCredentials);
                        }
                        
                    }),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
}
