<?php
// File: app/Filament/Resources/TopicResource/Pages/ListTopics.php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_subtopics')
                ->label('Gestisci Sottoargomenti')
                ->icon('heroicon-o-folder-open')
                ->color('primary')
                ->url(fn () => route('filament.quizpatente.resources.subtopics.index', ['topic' => $this->record->id])),
                
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Elimina argomento')
                ->modalDescription('Sei sicuro di voler eliminare questo argomento? Verranno eliminati anche tutti i sottoargomenti e contenuti associati.')
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
}