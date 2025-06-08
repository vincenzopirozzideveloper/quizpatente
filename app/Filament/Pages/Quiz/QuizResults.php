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
    protected static ?string $slug = 'quiz/results/{session?}';
    
    #[Url]
    public ?int $session = null;
    
    // Modelli
    public ?QuizSession $quizSession = null;
    
    // Statistiche
    public array $statistics = [];
    public array $errorsByTopic = [];
    
    /**
     * Mount del componente
     */
    public function mount(): void
    {
        // Verifica sessione
        if (!$this->session) {
            $this->redirect(QuizSelection::getUrl());
            return;
        }
        
        // Carica quiz session con relazioni
        $this->quizSession = QuizSession::with([
            'quizAnswers' => function ($query) {
                $query->orderBy('order')
                    ->with([
                        'question' => function ($q) {
                            $q->with(['topic', 'subtopic']);
                        }
                    ]);
            },
            'topic',
            'ministerialQuiz',
        ])->findOrFail($this->session);
        
        // Verifica autorizzazione
        if ($this->quizSession->user_id !== Auth::id()) {
            abort(403, 'Non autorizzato');
        }
        
        // Se non completato, torna al quiz
        if (!$this->quizSession->completed_at) {
            $this->redirect(QuizPlay::getUrl(['session' => $this->session]));
            return;
        }
        
        // Calcola statistiche
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
            'wrong' => $answers->where('is_correct', false)->whereNotNull('user_answer')->count(),
            'unanswered' => $answers->whereNull('user_answer')->count(),
            'score' => $this->quizSession->score,
            'passed' => $this->quizSession->is_passed,
            'time_spent' => $this->quizSession->duration,
            'max_errors' => $this->quizSession->metadata['max_errors'] ?? 3,
        ];
        
        // Calcola percentuali
        $total = $this->statistics['total_questions'];
        $this->statistics['correct_percentage'] = $total > 0 
            ? round(($this->statistics['correct'] / $total) * 100, 1) 
            : 0;
        $this->statistics['wrong_percentage'] = $total > 0 
            ? round(($this->statistics['wrong'] / $total) * 100, 1) 
            : 0;
        $this->statistics['unanswered_percentage'] = $total > 0 
            ? round(($this->statistics['unanswered'] / $total) * 100, 1) 
            : 0;
        
        // Errori per argomento - IMPORTANTE: converti a array
        $wrongAnswers = $answers->where('is_correct', false)->whereNotNull('user_answer');
        
        $this->errorsByTopic = $wrongAnswers->groupBy('question.topic.id')
            ->map(function ($topicAnswers, $topicId) {
                $topic = $topicAnswers->first()->question->topic;
                
                return [
                    'id' => $topicId,
                    'name' => $topic->name,
                    'icon' => $topic->icon ?? 'heroicon-o-folder',
                    'errors' => $topicAnswers->count(),
                    'questions' => $topicAnswers->map(function ($answer) {
                        return [
                            'id' => $answer->question_id,
                            'text' => $answer->question->text,
                            'correct_answer' => $answer->question->correct_answer,
                            'user_answer' => $answer->user_answer,
                            'explanation' => $answer->question->explanation,
                        ];
                    })->values()->toArray(), // Converti anche le questions in array
                ];
            })
            ->sortByDesc('errors')
            ->values()
            ->toArray(); // IMPORTANTE: converti la Collection finale in array
    }
    
    /**
     * Torna alla selezione quiz
     */
    public function backToSelection(): void
    {
        $this->redirect(QuizSelection::getUrl());
    }
    
    /**
     * Riprova lo stesso tipo di quiz
     */
    public function retryQuiz(): void
    {
        $quizService = app(\App\Services\QuizService::class);
        
        try {
            switch ($this->quizSession->type) {
                case 'ministerial':
                    if ($this->quizSession->ministerial_quiz_id) {
                        $newQuiz = $quizService->generateMinisterialQuizSession(
                            Auth::user(), 
                            $this->quizSession->ministerial_quiz_id
                        );
                    } else {
                        $this->backToSelection();
                        return;
                    }
                    break;
                    
                case 'ministerial_manual':
                    $newQuiz = $quizService->generateMinisterialQuizWithManual(Auth::user());
                    break;
                    
                case 'topic':
                    if ($this->quizSession->topic_id) {
                        $newQuiz = $quizService->generateTopicQuiz(
                            Auth::user(), 
                            $this->quizSession->topic_id,
                            true // con manuale
                        );
                    } else {
                        $this->backToSelection();
                        return;
                    }
                    break;
                    
                case 'errors_review':
                    $newQuiz = $quizService->generateErrorsReviewQuiz(Auth::user());
                    break;
                    
                default:
                    $this->backToSelection();
                    return;
            }
            
            $this->redirect(QuizPlay::getUrl(['session' => $newQuiz->id]));
            
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Errore')
                ->body('Impossibile creare un nuovo quiz: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Mostra il dettaglio di una domanda errata
     */
    public function showQuestionDetail(int $questionId): void
    {
        $this->dispatch('open-modal', id: 'question-detail-' . $questionId);
    }
    
    /**
     * Ottiene il tipo di quiz formattato
     */
    public function getQuizTypeLabel(): string
    {
        return match($this->quizSession->type) {
            'ministerial' => 'Quiz Ministeriale',
            'ministerial_manual' => 'Quiz Ministeriale con Manuale',
            'topic' => 'Quiz per Argomento',
            'errors_review' => 'Ripasso Errori',
            default => 'Quiz'
        };
    }
    
    /**
     * Ottiene il messaggio di completamento
     */
    public function getCompletionMessage(): string
    {
        if ($this->statistics['passed']) {
            return match($this->quizSession->type) {
                'ministerial', 'ministerial_manual' => 
                    "Complimenti! Hai superato il quiz ministeriale con {$this->statistics['wrong']} errori su {$this->statistics['max_errors']} consentiti.",
                'topic' => 
                    "Ottimo lavoro! Hai completato il quiz sull'argomento con {$this->statistics['correct']} risposte corrette su {$this->statistics['total_questions']}.",
                'errors_review' => 
                    "Bene! Hai ripassato i tuoi errori con {$this->statistics['correct']} risposte corrette.",
                default => 
                    "Quiz completato con successo!"
            };
        } else {
            return match($this->quizSession->type) {
                'ministerial', 'ministerial_manual' => 
                    "Non hai superato il quiz. Hai fatto {$this->statistics['wrong']} errori, ma ne sono consentiti solo {$this->statistics['max_errors']}. Riprova!",
                default => 
                    "Quiz completato. Continua a esercitarti per migliorare!"
            };
        }
    }
    
    /**
     * Verifica se mostrare il badge "Superato"
     */
    public function shouldShowPassedBadge(): bool
    {
        return in_array($this->quizSession->type, ['ministerial', 'ministerial_manual']);
    }
}