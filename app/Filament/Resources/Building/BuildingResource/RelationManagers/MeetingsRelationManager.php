<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Jobs\BeforeMeetingOcjob;
use Parsedown;
use Filament\Forms;
use Filament\Tables;
use App\Models\Meeting;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Jobs\OwnerMeeting;
use Filament\Tables\Table;
use Illuminate\Mail\Markdown;
use App\Models\OwnerCommittee;
use App\Jobs\BeforeOcMeetingjob;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class MeetingsRelationManager extends RelationManager
{
    protected static string $relationship = 'meetings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                MarkdownEditor::make('agenda')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'undo',
                    ])
                    ->disabled()
                    ->required(),
                DateTimePicker::make('date_time')
                    ->rules(['date'])
                    ->displayFormat('d-M-Y h:i A')
                    // ->minDate(now())
                    ->disabled()
                    ->required()
                    ->default(now())
                    ->label('Meeting Date Time'),
                MarkdownEditor::make('meeting_summary')
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'undo',
                    ])
                    ->required()
                    ->live(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agenda')->markdown(),
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
                        MarkdownEditor::make('agenda')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
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
                    ->action(function (array $data): void {
                        $Ownerlist = OwnerCommittee::where('building_id', $data['building_id'])->where('active',true)->pluck('user_id');
                        if ($Ownerlist->count() > 0) {
                            $meeting = Meeting::create([
                                'agenda' => $data['agenda'],
                                'date_time' => $data['date_time'],
                                'building_id' => $data['building_id'],
                            ]);
                            $userslist = User::whereIn('id', $Ownerlist)->get();
                            // Use Parsedown to convert Markdown to HTML
                            $parsedown = new Parsedown();
                            $agendaHtml = $parsedown->text($meeting->agenda);
                            foreach ($userslist as $owner) {
                                BeforeMeetingOcjob::dispatch($owner, $meeting, $agendaHtml);
                            }
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Meeting not created')
                                ->body('There are no OwnerCommittes for this buildings.')
                                ->send();
                        }
                    })
                    ->slideOver()

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (array $data, $record) {
                        // Runs after the form fields are saved to the database.
                        $Ownerlist = OwnerCommittee::where('building_id', $record->building_id)->where('active',true)->pluck('user_id');
                        $userslist = User::whereIn('id', $Ownerlist)->get();
                        // Use Parsedown to convert Markdown to HTML
                        $parsedown = new Parsedown();
                        $meetingSummaryHtml = $parsedown->text($record->meeting_summary);
                        $agendaHtml = $parsedown->text($record->agenda);

                        foreach ($userslist as $owner) {
                            OwnerMeeting::dispatch($owner, $record, $agendaHtml, $meetingSummaryHtml);
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
