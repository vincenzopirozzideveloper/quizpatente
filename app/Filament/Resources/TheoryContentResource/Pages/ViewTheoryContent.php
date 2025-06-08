<?php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use App\Models\TheoryContent;
use App\Models\UserTheoryProgress;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewTheoryContent extends ViewRecord
{
    protected static string $resource = TheoryContentResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $isCompleted = $record->currentUserProgress?->status === 'read';

        return [
            Actions\Action::make('toggleComplete')
                ->label($isCompleted ? 'Segna come non letto' : 'Segna come completato')
                ->icon($isCompleted ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color($isCompleted ? 'gray' : 'success')
                ->action(function () use ($record) {
                    UserTheoryProgress::toggleReadStatus(auth()->id(), $record->id);

                    // Ricarica il record per aggiornare lo stato
                    $record->refresh();
                    $record->load('currentUserProgress');

                    $newStatus = $record->currentUserProgress?->status === 'read';

                    Notification::make()
                        ->title($newStatus
                            ? 'Contenuto completato!'
                            : 'Contenuto marcato come non letto')
                        ->success()
                        ->send();

                    // Emetti evento per aggiornare il widget
                    $this->dispatch('theory-progress-updated');
                }),

            Actions\Action::make('previous')
                ->label('Precedente')
                ->icon('heroicon-o-chevron-left')
                ->color('gray')
                ->disabled(fn() => !$this->getPreviousRecord())
                ->action(fn() => $this->redirectToPrevious()),

            Actions\Action::make('next')
                ->label('Successivo')
                ->icon('heroicon-o-chevron-right')
                ->iconPosition('after')
                ->color('primary')
                ->disabled(fn() => !$this->getNextRecord())
                ->action(fn() => $this->redirectToNext()),
        ];
    }

    protected function getPreviousRecord(): ?TheoryContent
    {
        return TheoryContent::published()
            ->where('topic_id', $this->record->topic_id)
            ->where('order', '<', $this->record->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    protected function getNextRecord(): ?TheoryContent
    {
        return TheoryContent::published()
            ->where('topic_id', $this->record->topic_id)
            ->where('order', '>', $this->record->order)
            ->orderBy('order', 'asc')
            ->first();
    }

    protected function redirectToPrevious(): void
    {
        if ($previous = $this->getPreviousRecord()) {
            $this->redirect(static::getResource()::getUrl('view', ['record' => $previous]));
        }
    }

    protected function redirectToNext(): void
    {
        // Auto-completa il contenuto corrente prima di passare al successivo
        if ($this->record->currentUserProgress?->status !== 'read') {
            UserTheoryProgress::markAsRead(auth()->id(), $this->record->id);

            // Emetti evento per aggiornare il widget
            $this->dispatch('theory-progress-updated');
        }

        if ($next = $this->getNextRecord()) {
            $this->redirect(static::getResource()::getUrl('view', ['record' => $next]));
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            static::getResource()::getUrl() => 'Teoria',
            $this->record->topic->name,
            $this->record->title,
        ];
    }
}