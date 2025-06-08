<?php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use App\Models\Topic;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTheoryContents extends ListRecords
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
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make()
                ->label('Nuovo Contenuto')
                ->url(fn() => $this->topic
                    ? TheoryContentResource::getUrl('create', ['topic' => $this->topic->id])
                    : TheoryContentResource::getUrl('create')),
        ];

        if ($this->topic) {
            array_unshift(
                $actions,
                Actions\Action::make('back')
                    ->label('Torna agli argomenti')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->url(\App\Filament\Resources\TopicResource::getUrl('index'))
            );
        }

        return $actions;
    }

    public function getHeading(): string
    {
        if ($this->topic) {
            return "Contenuti di: {$this->topic->name}";
        }

        return 'Contenuti Teoria';
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();

        if ($this->topic) {
            $query->where('topic_id', $this->topic->id);
        }

        return $query;
    }
}