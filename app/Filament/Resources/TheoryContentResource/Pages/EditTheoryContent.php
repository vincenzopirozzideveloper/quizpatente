<?php
// File: app/Filament/Resources/TheoryContentResource/Pages/ListTheoryContents.php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditTheoryContent extends EditRecord
{
    protected static string $resource = TheoryContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Anteprima')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('Anteprima Contenuto')
                ->modalContent(view('filament.resources.theory-content-preview', ['record' => $this->record]))
                ->modalWidth('7xl')
                ->modalSubmitAction(false),
                
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Elimina contenuto')
                ->modalDescription('Sei sicuro di voler eliminare questo contenuto?')
                ->modalSubmitActionLabel('Sì, elimina')
                ->after(function () {
                    // Elimina l'immagine se presente
                    if ($this->record->image_url && Storage::exists($this->record->image_url)) {
                        Storage::delete($this->record->image_url);
                    }
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return route('filament.quizpatente.resources.theory-contents.index', ['subtopic' => $this->record->subtopic_id]);
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Contenuto aggiornato con successo';
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Se viene rimossa l'immagine, cancella anche caption e position
        if (empty($data['image_url'])) {
            $data['image_caption'] = null;
            $data['image_position'] = 'before';
            
            // Elimina la vecchia immagine se presente
            if ($this->record->image_url && Storage::exists($this->record->image_url)) {
                Storage::delete($this->record->image_url);
            }
        }
        
        return $data;
    }
}