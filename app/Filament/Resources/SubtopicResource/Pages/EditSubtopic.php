<?php
// File: app/Filament/Resources/SubtopicResource/Pages/ListSubtopics.php

namespace App\Filament\Resources\SubtopicResource\Pages;

use App\Filament\Resources\SubtopicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubtopic extends EditRecord
{
    protected static string $resource = SubtopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_contents')
                ->label('Gestisci Contenuti')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn () => route('filament.quizpatente.resources.theory-contents.index', ['subtopic' => $this->record->id])),
                
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Elimina sottoargomento')
                ->modalDescription('Sei sicuro di voler eliminare questo sottoargomento? Verranno eliminati anche tutti i contenuti associati.')
                ->modalSubmitActionLabel('Sì, elimina'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return route('filament.quizpatente.resources.subtopics.index', ['topic' => $this->record->topic_id]);
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Sottoargomento aggiornato con successo';
    }
}