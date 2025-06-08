<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use App\Filament\Resources\TheoryContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_contents')
                ->label('Gestisci Contenuti')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn () => TheoryContentResource::getUrl('index', ['topic' => $this->record->id])),
                
            Actions\Action::make('view_questions')
                ->label('Visualizza Domande')
                ->icon('heroicon-o-question-mark-circle')
                ->color('info')
                ->url(fn () => \App\Filament\Resources\QuestionResource::getUrl('index', [
                    'tableFilters' => ['topic_id' => ['value' => $this->record->id]]
                ])),
                
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Elimina argomento')
                ->modalDescription('Sei sicuro di voler eliminare questo argomento? Verranno eliminati anche tutti i contenuti teorici e le domande associate.')
                ->modalSubmitActionLabel('Sì, elimina'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Argomento aggiornato con successo';
    }

    protected function afterSave(): void
    {
        // Aggiorna il conteggio delle domande se necessario
        $this->record->total_questions = $this->record->questions()->count();
        $this->record->saveQuietly();
    }
}