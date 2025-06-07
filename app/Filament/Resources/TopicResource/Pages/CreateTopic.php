<?php
// File: app/Filament/Resources/TopicResource/Pages/ListTopics.php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTopic extends CreateRecord
{
    protected static string $resource = TopicResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Argomento creato con successo';
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calcola automaticamente l'ordine se non specificato
        if (!isset($data['order']) || $data['order'] === null) {
            $data['order'] = \App\Models\Topic::max('order') + 1;
        }
        
        return $data;
    }
}