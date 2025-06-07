<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_theory')
                ->label('Visualizza Teoria')
                ->icon('heroicon-o-book-open')
                ->color('info')
                ->modalHeading('Contenuto Teorico Collegato')
                ->modalContent(function () {
                    $theoryContent = $this->record->theoryContent;
                    if (!$theoryContent) {
                        return view('filament.components.no-theory-content');
                    }
                    return view('filament.resources.theory-content-preview', [
                        'record' => $theoryContent
                    ]);
                })
                ->modalWidth('7xl')
                ->visible(fn () => $this->record->theory_content_id !== null),
                
            Actions\Action::make('test_question')
                ->label('Testa Domanda')
                ->icon('heroicon-o-play')
                ->color('success')
                ->modalHeading('Test Domanda')
                ->modalContent(view('filament.resources.question-test', [
                    'question' => $this->record
                ]))
                ->modalWidth('2xl')
                ->modalSubmitAction(false),
                
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Elimina domanda')
                ->modalDescription('Sei sicuro di voler eliminare questa domanda? Verranno eliminate anche tutte le risposte associate.')
                ->after(function () {
                    // Aggiorna il conteggio delle domande nel topic
                    $topic = $this->record->topic;
                    if ($topic) {
                        $topic->total_questions = $topic->questions()->count();
                        $topic->save();
                    }
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Domanda aggiornata con successo';
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Se viene cambiato il topic, aggiorna i conteggi
        if ($this->record->topic_id !== $data['topic_id']) {
            $this->shouldUpdateTopicCounts = true;
            $this->oldTopicId = $this->record->topic_id;
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // Aggiorna i conteggi se il topic è cambiato
        if (isset($this->shouldUpdateTopicCounts) && $this->shouldUpdateTopicCounts) {
            // Aggiorna il vecchio topic
            if ($oldTopic = \App\Models\Topic::find($this->oldTopicId)) {
                $oldTopic->total_questions = $oldTopic->questions()->count();
                $oldTopic->save();
            }
            
            // Aggiorna il nuovo topic
            if ($this->record->topic) {
                $this->record->topic->total_questions = $this->record->topic->questions()->count();
                $this->record->topic->save();
            }
        }
    }
}