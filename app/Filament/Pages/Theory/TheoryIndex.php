<?php

namespace App\Filament\Pages\Theory;

use Filament\Pages\Page;
use App\Models\Topic;
use Illuminate\Support\Collection;

class TheoryIndex extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Teoria';
    protected static ?string $title = 'Teoria';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.theory.theory-index';

    public Collection $topics;

    public function mount(): void
    {
        $this->loadTopics();
    }

    public function loadTopics(): void
    {
        $this->topics = Topic::with(['userProgress' => function ($query) {
            $query->where('user_id', auth()->id());
        }])
        ->active()
        ->ordered()
        ->get()
        ->map(function ($topic) {
            $progress = $topic->userProgress;
            return [
                'id' => $topic->id,
                'code' => $topic->code,
                'name' => $topic->name,
                'icon' => $topic->icon,
                'total_questions' => $topic->total_questions,
                'completed_questions' => $progress?->completed_questions ?? 0,
                'percentage' => $topic->completion_percentage,
            ];
        });
    }
}