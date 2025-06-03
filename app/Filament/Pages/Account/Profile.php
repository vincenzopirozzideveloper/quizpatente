<?php

namespace App\Filament\Pages\Account;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Profilo';
    protected static ?string $title = 'Profilo';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Account';
    protected static string $view = 'filament.pages.account.profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informazioni Personali')
                    ->description('Aggiorna le tue informazioni personali')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),
            ])
            ->statePath('data')
            ->model(auth()->user());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva modifiche')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        auth()->user()->update($data);
        
        Notification::make()
            ->title('Profilo aggiornato')
            ->success()
            ->send();
    }
}