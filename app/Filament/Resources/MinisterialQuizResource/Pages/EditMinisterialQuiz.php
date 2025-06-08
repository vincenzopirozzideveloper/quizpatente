<?php
// app/Filament/Resources/MinisterialQuizResource/Pages/EditMinisterialQuiz.php

namespace App\Filament\Resources\MinisterialQuizResource\Pages;

use App\Filament\Resources\MinisterialQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditMinisterialQuiz extends EditRecord
{
    protected static string $resource = MinisterialQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Anteprima')
                ->icon('heroicon-o-eye')
                ->modalHeading('Anteprima Quiz')
                ->modalContent(view('filament.resources.ministerial-quiz-preview', [
                    'quiz' => $this->record
                ]))
                ->modalWidth('5xl'),
                
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Quiz ministeriale aggiornato con successo';
    }

    protected function afterSave(): void
    {
        $questionsCount = $this->record->questions()->count();
        
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