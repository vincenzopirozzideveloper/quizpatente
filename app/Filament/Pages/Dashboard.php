<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\QuizSession;
use App\Models\UserError;
use App\Models\Topic;
use App\Models\UserTopicProgress;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    protected static string $view = 'filament.pages.dashboard';

    public array $stats = [];
    public Collection $recentTopics;
    public Collection $recentErrors;
    public array $weeklyProgress = [];
    public int $streakDays = 0;

    public function mount(): void
    {
        $this->loadStats();
        $this->loadRecentTopics();
        $this->loadRecentErrors();
        $this->loadWeeklyProgress();
        $this->calculateStreak();
    }

    protected function loadStats(): void
    {
        $userId = auth()->id();
        
        $totalQuizzes = QuizSession::where('user_id', $userId)
            ->completed()
            ->count();
            
        $passedQuizzes = QuizSession::where('user_id', $userId)
            ->completed()
            ->where('is_passed', true)
            ->count();
            
        $totalErrors = UserError::where('user_id', $userId)
            ->notMastered()
            ->count();
            
        $totalQuestions = QuizSession::where('user_id', $userId)
            ->completed()
            ->sum('total_questions');
            
        $correctAnswers = QuizSession::where('user_id', $userId)
            ->completed()
            ->sum('correct_answers');
            
        $this->stats = [
            'total_quizzes' => $totalQuizzes,
            'success_rate' => $totalQuizzes > 0 ? round(($passedQuizzes / $totalQuizzes) * 100) : 0,
            'errors_to_review' => $totalErrors,
            'accuracy_rate' => $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0,
        ];
    }

    protected function loadRecentTopics(): void
    {
        $this->recentTopics = Topic::with(['userProgress' => function ($query) {
            $query->where('user_id', auth()->id());
        }])
        ->active()
        ->limit(5)
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
                'last_activity' => $progress?->last_activity,
            ];
        })
        ->sortByDesc('last_activity')
        ->take(3);
    }

    protected function loadRecentErrors(): void
    {
        $this->recentErrors = UserError::where('user_id', auth()->id())
            ->notMastered()
            ->with('question.topic')
            ->orderBy('error_count', 'desc')
            ->limit(5)
            ->get();
    }

    protected function loadWeeklyProgress(): void
    {
        $startDate = now()->subDays(6)->startOfDay();
        
        $sessions = QuizSession::where('user_id', auth()->id())
            ->where('created_at', '>=', $startDate)
            ->completed()
            ->get()
            ->groupBy(function ($session) {
                return $session->created_at->format('Y-m-d');
            });

        $this->weeklyProgress = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dayName = $date->locale('it')->dayName;
            
            $daySessions = $sessions->get($dateKey, collect());
            
            $this->weeklyProgress[] = [
                'day' => ucfirst($dayName),
                'date' => $date->format('d/m'),
                'quizzes' => $daySessions->count(),
                'questions' => $daySessions->sum('total_questions'),
                'correct' => $daySessions->sum('correct_answers'),
            ];
        }
    }

    protected function calculateStreak(): void
    {
        $userId = auth()->id();
        $currentDate = now()->startOfDay();
        $streak = 0;
        
        while (true) {
            $hasActivity = QuizSession::where('user_id', $userId)
                ->whereDate('created_at', $currentDate)
                ->exists();
                
            if (!$hasActivity && $currentDate->isToday()) {
                $currentDate = $currentDate->subDay();
                continue;
            }
            
            if (!$hasActivity) {
                break;
            }
            
            $streak++;
            $currentDate = $currentDate->subDay();
        }
        
        $this->streakDays = $streak;
    }

    public function startQuiz(): void
    {
        $this->redirect(route('filament.pages.quiz.index'));
    }

    public function reviewErrors(): void
    {
        $this->redirect(route('filament.pages.errors.review'));
    }
}