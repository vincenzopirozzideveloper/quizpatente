<?php
// File: app/Filament/Resources/SubtopicResource/Pages/ListSubtopics.php

namespace App\Filament\Resources\SubtopicResource\Pages;

use App\Filament\Resources\SubtopicResource;
use App\Models\Topic;
use Filament\Resources\Pages\CreateRecord;

class CreateSubtopic extends CreateRecord
{
    protected static string $resource = SubtopicResource::class;

    public ?Topic $topic = null;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('topic')) {
            /** @var Topic|null $topic */
            $topic = Topic::find(request('topic'));
            $this->topic = $topic;
            $this->form->fill([
                'topic_id' => $this->topic->id,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        if ($this->topic) {
            return route('filament.quizpatente.resources.subtopics.index', ['topic' => $this->topic->id]);
        }

        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Sottoargomento creato con successo';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calcola automaticamente l'ordine se non specificato
        if (!isset($data['order']) || $data['order'] === null) {
            $data['order'] = \App\Models\Subtopic::where('topic_id', $data['topic_id'])->max('order') + 1;
        }

        return $data;
    }
}