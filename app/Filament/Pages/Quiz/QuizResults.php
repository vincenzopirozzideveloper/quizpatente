<?php

namespace App\Filament\Pages\Quiz;

use Filament\Pages\Page;
use App\Models\QuizSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class QuizResults extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Risultati Quiz';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.quiz.quiz-results';
    protected static ?string $slug = 'quiz/results/{session}';
    
    #[Url]
    public ?int $session = null;
    
    public ?QuizSession $quizSession = null;
    public array $statistics = [];
    public array $errorsByTopic = [];
    
    public function mount(): void
    {
        if (!$this->session) {
            $this->redirect(route('filament.quizpatente.pages.quiz.selection'));
            return;
        }
        
        $this->quizSession = QuizSession::with([
            'quizAnswers' => function ($query) {
                $query->orderBy('order')
                    ->with(['question.topic', 'question.subtopic']);
            },
            'topic',
        ])->findOrFail($this->session);
        
        // Verifica che il quiz appartenga all'utente corrente
        if ($this->quizSession->user_id !== Auth::id()) {
            abort(403, 'Non autorizzato');
        }
        
        // Se il quiz non è completato, torna al quiz
        if (!$this->quizSession->completed_at) {
            $this->redirect(route('filament.quizpatente.pages.quiz.play', ['session' => $this->session]));
            return;
        }
        
        $this->calculateStatistics();
    }
    
    /**
     * Calcola le statistiche del quiz
     */
    protected function calculateStatistics(): void
    {
        $answers = $this->quizSession->quizAnswers;
        
        // Statistiche generali
        $this->statistics = [
            'total_questions' => $answers->count(),
            'correct' => $answers->where('is_correct', true)->count(),
            'wrong' => $answers->where('is_correct', false)->where('user_answer', '!==', null)->count(),
            'unanswered' => $answers->whereNull('user_answer')->count(),
            'score' => $this->quizSession->score,
            'passed' => $this->quizSession->is_passed,
            'time_spent' => $this->quizSession->duration,
        ];
        
        // Errori per argomento
        $wrongAnswers = $answers->where('is_correct', false)->where('user_answer', '!==', null);
        $this->errorsByTopic = $wrongAnswers->groupBy('question.topic.id')
            ->map(function ($topicAnswers, $topicId) {
                $topic = $topicAnswers->first()->question->topic;
                return [
                    'name' => $topic->name,
                    'icon' => $topic->icon,
                    'errors' => $topicAnswers->count(),
                    'questions' => $topicAnswers->map(function ($answer) {
                        return [
                            'id' => $answer->question_id,
                            'text' => $answer->question->text,
                            'correct_answer' => $answer->question->correct_answer,
                            'user_answer' => $answer->user_answer,
                            'explanation' => $answer->question->explanation,
                        ];
                    }),
                ];
            })
            ->sortByDesc('errors');
    }
    
    /**
     * Torna alla selezione quiz
     */
    public function backToSelection(): void
    {
        $this->redirect(route('filament.quizpatente.pages.quiz.selection'));
    }
    
    /**
     * Avvia un nuovo quiz dello stesso tipo
     */
    public function retryQuiz(): void
    {
        $quizService = app(\App\Services\QuizService::class);
        
        switch ($this->quizSession->type) {
            case 'ministerial':
                $newQuiz = $quizService->generateMinisterialQuiz(Auth::user(), false);
                break;
                
            case 'ministerial_manual':
                $newQuiz = $quizService->generateMinisterialQuiz(Auth::user(), true);
                break;
                
            case 'topic':
                $newQuiz = $quizService->generateTopicQuiz(Auth::user(), $this->quizSession->topic_id);
                break;
                
            case 'errors_review':
                $newQuiz = $quizService->generateErrorsReviewQuiz(Auth::user());
                break;
                
            default:
                $this->backToSelection();
                return;
        }
        
        $this->redirect(route('filament.quizpatente.pages.quiz.play', ['session' => $newQuiz->id]));
    }
    
    /**
     * Mostra il dettaglio di una domanda errata
     */
    public function showQuestionDetail(int $questionId): void
    {
        $this->dispatch('open-modal', id: 'question-detail-' . $questionId);
    }
}