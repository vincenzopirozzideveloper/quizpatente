<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuova Domanda'),
                
            Actions\Action::make('import')
                ->label('Importa da CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Importa domande da CSV')
                ->modalDescription('Carica un file CSV con le domande da importare')
                ->modalContent(view('filament.resources.question-import-form'))
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Importa')
                ->action(function (array $data) {
                    // Logica di importazione CSV
                    // Implementeremo successivamente
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            QuestionResource\Widgets\QuestionStatsOverview::class,
        ];
    }
    
    public function getTitle(): string
    {
        return 'Gestione Domande Quiz';
    }
    
    protected function getTableDescription(): ?string
    {
        return 'Gestisci tutte le domande del quiz. Ogni domanda deve essere collegata a un contenuto teorico specifico.';
    }
}