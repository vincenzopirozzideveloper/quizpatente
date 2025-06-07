<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Domanda creata con successo';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Genera automaticamente un numero ministeriale se non specificato
        if (empty($data['ministerial_number']) && $data['is_ministerial']) {
            $lastNumber = \App\Models\Question::where('is_ministerial', true)
                ->whereNotNull('ministerial_number')
                ->orderBy('ministerial_number', 'desc')
                ->value('ministerial_number');
            
            if ($lastNumber) {
                $number = intval(substr($lastNumber, 1)) + 1;
                $data['ministerial_number'] = 'D' . str_pad($number, 3, '0', STR_PAD_LEFT);
            } else {
                $data['ministerial_number'] = 'D001';
            }
        }
        
        // Calcola automaticamente l'ordine se non specificato
        if (!isset($data['order']) || $data['order'] === null) {
            $data['order'] = \App\Models\Question::where('topic_id', $data['topic_id'])
                ->max('order') + 1;
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Aggiorna il conteggio delle domande nel topic
        $topic = $this->record->topic;
        if ($topic) {
            $topic->total_questions = $topic->questions()->count();
            $topic->save();
        }
        
        Notification::make()
            ->title('Suggerimento')
            ->body('Puoi continuare ad aggiungere domande o tornare alla lista.')
            ->info()
            ->persistent()
            ->send();
    }
}