<?php
// app/Filament/Resources/MinisterialQuizResource/Pages/CreateMinisterialQuiz.php

namespace App\Filament\Resources\MinisterialQuizResource\Pages;

use App\Filament\Resources\MinisterialQuizResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateMinisterialQuiz extends CreateRecord
{
    protected static string $resource = MinisterialQuizResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Quiz ministeriale creato con successo';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Rimuovi le domande dai dati principali, verranno gestite dopo
        unset($data['questions']);
        
        // Calcola automaticamente l'ordine se non specificato
        if (!isset($data['order']) || $data['order'] === null) {
            $data['order'] = \App\Models\MinisterialQuiz::max('order') + 1 ?? 0;
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Gestisci l'associazione delle domande
        $questions = $this->data['questions'] ?? [];
        
        if (!empty($questions)) {
            $questionsWithOrder = [];
            foreach ($questions as $index => $questionId) {
                $questionsWithOrder[$questionId] = ['order' => $index + 1];
            }
            
            $this->record->questions()->sync($questionsWithOrder);
        }
        
        $questionsCount = count($questions);
        
        if ($questionsCount !== 30) {
            Notification::make()
                ->title('Attenzione')
                ->body("Il quiz ha {$questionsCount} domande invece di 30. Modificalo per renderlo valido.")
                ->warning()
                ->persistent()
                ->send();
        }
    }
}