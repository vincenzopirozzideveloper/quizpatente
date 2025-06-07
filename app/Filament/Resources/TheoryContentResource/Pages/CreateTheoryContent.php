<?php
// File: app/Filament/Resources/TheoryContentResource/Pages/ListTheoryContents.php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use App\Models\Subtopic;
use Filament\Resources\Pages\CreateRecord;

class CreateTheoryContent extends CreateRecord
{
    protected static string $resource = TheoryContentResource::class;

    public ?Subtopic $subtopic = null;

    public function mount(): void
    {
        parent::mount();

        if (request()->has('subtopic')) {
            /** @var Subtopic|null $subtopic */
            $subtopic = Subtopic::with('topic')->find(request('subtopic'));
            $this->subtopic = $subtopic;
            $this->form->fill([
                'subtopic_id' => $this->subtopic->id,
                'topic_id' => $this->subtopic->topic_id,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        if ($this->subtopic) {
            return route('filament.quizpatente.resources.theory-contents.index', ['subtopic' => $this->subtopic->id]);
        }

        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Contenuto creato con successo';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calcola automaticamente l'ordine se non specificato
        if (!isset($data['order']) || $data['order'] === null) {
            $data['order'] = \App\Models\TheoryContent::where('subtopic_id', $data['subtopic_id'])->max('order') + 1;
        }

        // Se non c'è un codice, generalo automaticamente
        if (!isset($data['code']) || empty($data['code'])) {
            $subtopic = \App\Models\Subtopic::find($data['subtopic_id']);
            $nextNumber = \App\Models\TheoryContent::where('subtopic_id', $data['subtopic_id'])->count() + 1;
            $data['code'] = $subtopic->code . '.' . $nextNumber;
        }

        return $data;
    }
}