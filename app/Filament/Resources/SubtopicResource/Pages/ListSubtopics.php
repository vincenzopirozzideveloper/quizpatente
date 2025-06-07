<?php
// File: app/Filament/Resources/SubtopicResource/Pages/ListSubtopics.php

namespace App\Filament\Resources\SubtopicResource\Pages;

use App\Filament\Resources\SubtopicResource;
use App\Models\Topic;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubtopics extends ListRecords
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
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make()
                ->label('Nuovo Sottoargomento')
                ->url(fn() => $this->topic
                    ? route('filament.quizpatente.resources.subtopics.create', ['topic' => $this->topic->id])
                    : route('filament.quizpatente.resources.subtopics.create')),
        ];

        if ($this->topic) {
            array_unshift(
                $actions,
                Actions\Action::make('back')
                    ->label('Torna agli argomenti')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->url(route('filament.quizpatente.resources.topics.index'))
            );
        }

        return $actions;
    }

    public function getHeading(): string
    {
        if ($this->topic) {
            return "Sottoargomenti di: {$this->topic->name}";
        }

        return 'Sottoargomenti';
    }
}