<?php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use App\Models\Topic;
use Filament\Resources\Pages\CreateRecord;

class CreateTheoryContent extends CreateRecord
{
    protected static string $resource = TheoryContentResource::class;

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
            return TheoryContentResource::getUrl('index', ['topic' => $this->topic->id]);
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
            $data['order'] = \App\Models\TheoryContent::where('topic_id', $data['topic_id'])->max('order') + 1 ?? 0;
        }

        // Se non c'è un codice, generalo automaticamente
        if (!isset($data['code']) || empty($data['code'])) {
            $topic = \App\Models\Topic::find($data['topic_id']);
            $existingCodes = \App\Models\TheoryContent::where('topic_id', $data['topic_id'])
                ->pluck('code')
                ->toArray();
            
            // Genera un codice progressivo per il topic
            $nextNumber = 1;
            do {
                $proposedCode = $topic->code . '.' . $nextNumber;
                $nextNumber++;
            } while (in_array($proposedCode, $existingCodes));
            
            $data['code'] = $proposedCode;
        }

        return $data;
    }
}