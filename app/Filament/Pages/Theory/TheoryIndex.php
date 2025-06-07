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
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.theory.theory-index';

    public Collection $topics;
    public array $statistics = [];

    public function mount(): void
    {
        $this->loadTopics();
        $this->loadStatistics();
    }

    protected function loadTopics(): void
    {
        $this->topics = Topic::with([
            'userProgress' => function ($query) {
                $query->where('user_id', auth()->id());
            },
            'subtopics' => function ($query) {
                $query->active()->ordered();
            }
        ])
        ->active()
        ->ordered()
        ->get()
        ->map(function ($topic) {
            $progress = $topic->userProgress;
            $subtopicsCount = $topic->subtopics->count();
            
            return [
                'id' => $topic->id,
                'code' => $topic->code,
                'name' => $topic->name,
                'description' => $topic->description,
                'icon' => $topic->icon ?? 'heroicon-o-book-open',
                'total_questions' => $topic->total_questions,
                'subtopics_count' => $subtopicsCount,
                'completed_questions' => $progress?->completed_questions ?? 0,
                'percentage' => $topic->completion_percentage,
                'accuracy_rate' => $progress?->accuracy_rate ?? 0,
                'last_activity' => $progress?->last_activity,
                'is_completed' => $progress?->completed_at !== null,
            ];
        });
    }

    protected function loadStatistics(): void
    {
        $userId = auth()->id();
        
        $totalTopics = $this->topics->count();
        $completedTopics = $this->topics->where('is_completed', true)->count();
        $totalProgress = $totalTopics > 0 
            ? round(($completedTopics / $totalTopics) * 100) 
            : 0;
        
        $totalQuestions = $this->topics->sum('total_questions');
        $completedQuestions = $this->topics->sum('completed_questions');
        
        $this->statistics = [
            'total_topics' => $totalTopics,
            'completed_topics' => $completedTopics,
            'total_progress' => $totalProgress,
            'total_questions' => $totalQuestions,
            'completed_questions' => $completedQuestions,
        ];
    }

    public function startTopic(int $topicId): void
    {
        $this->redirect(TheoryView::getUrl(['topicId' => $topicId]));
    }
}