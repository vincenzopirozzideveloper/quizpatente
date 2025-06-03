<?php

namespace App\Filament\Pages\Account;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\UserSetting;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Impostazioni';
    protected static ?string $title = 'Impostazioni';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Account';
    protected static string $view = 'filament.pages.account.settings';

    public ?array $data = [];
    public UserSetting $settings;

    public function mount(): void
    {
        $this->settings = auth()->user()->settings;
        
        $this->form->fill($this->settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Notifiche')
                    ->schema([
                        Toggle::make('notifications_enabled')
                            ->label('Abilita notifiche')
                            ->helperText('Ricevi notifiche sui tuoi progressi'),
                        Toggle::make('email_notifications')
                            ->label('Notifiche email')
                            ->visible(fn ($get) => $get('notifications_enabled')),
                        Toggle::make('daily_reminder')
                            ->label('Promemoria giornaliero')
                            ->reactive(),
                        TimePicker::make('reminder_time')
                            ->label('Orario promemoria')
                            ->visible(fn ($get) => $get('daily_reminder')),
                    ]),
                    
                Section::make('Preferenze Quiz')
                    ->schema([
                        Select::make('difficulty_preference')
                            ->label('Difficoltà preferita')
                            ->options([
                                'easy' => 'Facile',
                                'medium' => 'Medio',
                                'hard' => 'Difficile',
                                'mixed' => 'Misto',
                            ]),
                        Toggle::make('show_explanations')
                            ->label('Mostra spiegazioni')
                            ->helperText('Visualizza le spiegazioni dopo ogni risposta'),
                        Toggle::make('show_timer')
                            ->label('Mostra timer')
                            ->helperText('Visualizza il tempo durante i quiz'),
                        Toggle::make('sound_effects')
                            ->label('Effetti sonori'),
                    ]),
                    
                Section::make('Aspetto')
                    ->schema([
                        Select::make('theme')
                            ->label('Tema')
                            ->options([
                                'light' => 'Chiaro',
                                'dark' => 'Scuro',
                                'auto' => 'Automatico',
                            ]),
                    ]),
            ])
            ->statePath('data')
            ->model($this->settings);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva impostazioni')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $this->settings->update($this->form->getState());
        
        Notification::make()
            ->title('Impostazioni salvate')
            ->success()
            ->send();
    }
}