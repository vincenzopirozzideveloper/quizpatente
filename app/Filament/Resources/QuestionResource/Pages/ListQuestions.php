<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuova Domanda'),
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