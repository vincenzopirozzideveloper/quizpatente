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
            'theoryContents' => function ($query) {
                $query->published()->ordered();
            }
        ])
        ->withCount(['theoryContents' => function ($query) {
            $query->published();
        }])
        ->active()
        ->ordered()
        ->get()
        ->map(function ($topic) {
            $progress = $topic->userProgress;
            
            // Calcola il progresso della teoria
            $theoryProgress = $this->calculateTheoryProgress($topic);
            
            return [
                'id' => $topic->id,
                'code' => $topic->code,
                'name' => $topic->name,
                'description' => $topic->description,
                'icon' => $topic->icon ?? 'heroicon-o-book-open',
                'total_questions' => $topic->total_questions,
                'theory_contents_count' => $topic->theory_contents_count,
                'theory_contents_read' => $theoryProgress['read'],
                'theory_progress_percentage' => $theoryProgress['percentage'],
                'completed_questions' => $progress?->completed_questions ?? 0,
                'percentage' => $topic->completion_percentage,
                'accuracy_rate' => $progress?->accuracy_rate ?? 0,
                'last_activity' => $progress?->last_activity,
                'is_completed' => $progress?->completed_at !== null,
            ];
        });
    }

    protected function calculateTheoryProgress($topic): array
    {
        $totalContents = $topic->theory_contents_count;
        $readContents = 0;
        
        if ($totalContents > 0) {
            // Conta i contenuti letti dall'utente
            $readContents = $topic->theoryContents()
                ->whereHas('userProgress', function ($query) {
                    $query->where('user_id', auth()->id())
                          ->where('status', 'read');
                })
                ->count();
        }
        
        return [
            'read' => $readContents,
            'total' => $totalContents,
            'percentage' => $totalContents > 0 
                ? round(($readContents / $totalContents) * 100, 1) 
                : 0
        ];
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
        
        // Aggiungi statistiche sui contenuti teorici
        $totalTheoryContents = $this->topics->sum('theory_contents_count');
        $readTheoryContents = $this->topics->sum('theory_contents_read');
        
        $this->statistics = [
            'total_topics' => $totalTopics,
            'completed_topics' => $completedTopics,
            'total_progress' => $totalProgress,
            'total_questions' => $totalQuestions,
            'completed_questions' => $completedQuestions,
            'total_theory_contents' => $totalTheoryContents,
            'read_theory_contents' => $readTheoryContents,
            'theory_progress' => $totalTheoryContents > 0 
                ? round(($readTheoryContents / $totalTheoryContents) * 100) 
                : 0,
        ];
    }

    public function startTopic(int $topicId): void
    {
        $this->redirect(TheoryView::getUrl(['topicId' => $topicId]));
    }
}