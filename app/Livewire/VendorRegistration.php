<?php

namespace App\Livewire;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
class VendorRegistration extends Component implements HasForms
{
     use InteractsWithForms;
    // public ?array $data = [];

    // public function mount(): void
    // {
    //     $this->form->fill();
    // }

    protected function getForms(): array
    {
        return [
            'editPostForm',
            'createCommentForm',
        ];
    }

    public function editPostForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                MarkdownEditor::make('content'),
                // ...
            ])
            ->statePath('postData')
            ->model($this->post);
    }

    public function createCommentForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                MarkdownEditor::make('content')
                    ->required(),
                // ...
            ])
            ->statePath('commentData')
            ->model(Comment::class);
    }
    public function render()
    {
        return view('livewire.vendor-registration');
    }
}
