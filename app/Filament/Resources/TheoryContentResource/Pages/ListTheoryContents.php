<?php
// File: app/Filament/Resources/TheoryContentResource/Pages/ListTheoryContents.php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use App\Models\Subtopic;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTheoryContents extends ListRecords
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
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make()
                ->label('Nuovo Contenuto')
                ->url(fn() => $this->subtopic
                    ? route('filament.quizpatente.resources.theory-contents.create', ['subtopic' => $this->subtopic->id])
                    : route('filament.quizpatente.resources.theory-contents.create')),
        ];

        if ($this->subtopic) {
            array_unshift(
                $actions,
                Actions\Action::make('back')
                    ->label('Torna ai sottoargomenti')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->url(route('filament.quizpatente.resources.subtopics.index', ['topic' => $this->subtopic->topic_id]))
            );
        }

        return $actions;
    }

    public function getHeading(): string
    {
        if ($this->subtopic) {
            return "Contenuti di: {$this->subtopic->topic->name} - {$this->subtopic->title}";
        }

        return 'Contenuti Teoria';
    }
}