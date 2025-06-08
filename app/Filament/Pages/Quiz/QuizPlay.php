<?php

namespace App\Filament\Pages\Quiz;

use Filament\Pages\Page;
use App\Models\QuizSession;
use App\Models\QuizAnswer;
use App\Models\UserError;
use App\Models\UserTopicProgress;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class QuizPlay extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-play';
    protected static ?string $title = 'Quiz in Corso';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.quiz.quiz-play';
    protected static ?string $slug = 'quiz/play/{session?}';

    #[Url]
    public ?int $session = null;

    public ?QuizSession $quizSession = null;
    public ?QuizAnswer $currentAnswer = null;
    public int $currentQuestionIndex = 0;
    public ?bool $selectedAnswer = null;
    public int $remainingTime = 1800; // 30 minuti in secondi
    public bool $quizCompleted = false;
    public bool $showResult = false;
    public bool $showTheoryModal = false;

    // Statistiche in tempo reale
    public int $answeredCount = 0;
    public int $correctCount = 0;
    public int $wrongCount = 0;

    public function mount(): void
    {
        if (!$this->session) {
            $this->redirect(QuizSelection::getUrl());
            return;
        }

        $this->quizSession = QuizSession::with([
            'quizAnswers' => function ($query) {
                $query->orderBy('order');
            },
            'quizAnswers.question.topic',
            'quizAnswers.question.subtopic',
            'quizAnswers.question.theoryContent',
        ])->findOrFail($this->session);

        // Verifica che il quiz appartenga all'utente corrente
        if ($this->quizSession->user_id !== Auth::id()) {
            abort(403, 'Non autorizzato');
        }

        // Se il quiz è già completato, vai ai risultati
        if ($this->quizSession->completed_at) {
            $this->redirect(QuizResults::getUrl(['session' => $this->session]));
            return;
        }

        // Carica la prima domanda non risposta
        $this->loadCurrentQuestion();

        // Calcola il tempo rimanente
        $elapsedTime = now()->diffInSeconds($this->quizSession->started_at);
        $this->remainingTime = max(0, 1800 - $elapsedTime);
    }

    /**
     * Carica la domanda corrente
     */
    public function loadCurrentQuestion(): void
    {
        // Trova la prima domanda non risposta
        $unansweredIndex = $this->quizSession->quizAnswers
            ->search(function ($answer) {
                return $answer->user_answer === null;
            });

        if ($unansweredIndex !== false) {
            $this->currentQuestionIndex = $unansweredIndex;
        } else {
            // Se tutte sono risposte, vai alla prima
            $this->currentQuestionIndex = 0;
        }

        $this->currentAnswer = $this->quizSession->quizAnswers[$this->currentQuestionIndex];
        $this->selectedAnswer = $this->currentAnswer->user_answer;

        // Aggiorna statistiche
        $this->updateStats();
    }

    /**
     * Vai alla domanda precedente
     */
    public function previousQuestion(): void
    {
        if ($this->currentQuestionIndex > 0) {
            $this->saveCurrentAnswer();
            $this->currentQuestionIndex--;
            $this->currentAnswer = $this->quizSession->quizAnswers[$this->currentQuestionIndex];
            $this->selectedAnswer = $this->currentAnswer->user_answer;
        }
    }

    /**
     * Vai alla domanda successiva
     */
    public function nextQuestion(): void
    {
        if ($this->currentQuestionIndex < count($this->quizSession->quizAnswers) - 1) {
            $this->saveCurrentAnswer();
            $this->currentQuestionIndex++;
            $this->currentAnswer = $this->quizSession->quizAnswers[$this->currentQuestionIndex];
            $this->selectedAnswer = $this->currentAnswer->user_answer;
        }
    }

    /**
     * Vai a una domanda specifica
     */
    public function goToQuestion(int $index): void
    {
        if ($index >= 0 && $index < count($this->quizSession->quizAnswers)) {
            $this->saveCurrentAnswer();
            $this->currentQuestionIndex = $index;
            $this->currentAnswer = $this->quizSession->quizAnswers[$this->currentQuestionIndex];
            $this->selectedAnswer = $this->currentAnswer->user_answer;
        }
    }

    /**
     * Seleziona una risposta
     */
    public function selectAnswer(bool $answer): void
    {
        $this->selectedAnswer = $answer;
    }

    /**
     * Salva la risposta corrente
     */
    public function saveCurrentAnswer(): void
    {
        if ($this->selectedAnswer !== null) {
            $this->currentAnswer->user_answer = $this->selectedAnswer;
            $this->currentAnswer->is_correct = $this->selectedAnswer === $this->currentAnswer->question->correct_answer;
            $this->currentAnswer->time_spent = now()->diffInSeconds($this->quizSession->started_at);
            $this->currentAnswer->save();

            // Aggiorna le statistiche
            $this->updateStats();
        }
    }

    /**
     * Mostra/Nascondi la teoria (solo per quiz con manuale)
     */
    public function toggleTheory(): void
    {
        if ($this->quizSession->metadata['with_manual'] ?? false) {
            $this->showTheoryModal = !$this->showTheoryModal;
        }
    }

    /**
     * Completa il quiz
     */
    public function completeQuiz(): void
    {
        // Salva l'ultima risposta se presente
        $this->saveCurrentAnswer();

        DB::transaction(function () {
            // Recupera tutte le risposte del quiz
            $answers = $this->quizSession->quizAnswers()->get();

            // Calcola i risultati
            $correct = $answers->where('is_correct', true)->count();
            $wrong = $answers->where('is_correct', false)
                ->whereNotNull('user_answer')
                ->count();
            $unanswered = $answers->whereNull('user_answer')->count();

            // Aggiorna la sessione del quiz
            $this->quizSession->update([
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'unanswered' => $unanswered,
                'completed_at' => now(),
                'time_spent' => now()->diffInSeconds($this->quizSession->started_at),
            ]);

            // Calcola il punteggio e determina se il quiz è superato
            $this->quizSession->calculateScore();
            $this->quizSession->save();

            // Gestisci gli errori dell'utente
            foreach ($answers as $answer) {
                // Registra l'errore SOLO se l'utente ha risposto E ha sbagliato
                if ($answer->user_answer !== null && !$answer->is_correct) {
                    $userError = UserError::firstOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'question_id' => $answer->question_id,
                        ],
                        [
                            'error_count' => 0,
                            'last_error_date' => now(),
                        ]
                    );

                    $userError->incrementError();
                }

                // Se l'utente ha risposto correttamente a una domanda precedentemente sbagliata
                if ($answer->user_answer !== null && $answer->is_correct) {
                    $userError = UserError::where('user_id', Auth::id())
                        ->where('question_id', $answer->question_id)
                        ->first();

                    if ($userError) {
                        $userError->markCorrect();
                    }
                }
            }

            // Aggiorna i progressi per argomento
            $questionsByTopic = $answers->groupBy('question.topic_id');

            foreach ($questionsByTopic as $topicId => $topicAnswers) {
                $progress = UserTopicProgress::firstOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'topic_id' => $topicId,
                    ],
                    [
                        'completed_questions' => 0,
                        'correct_answers' => 0,
                        'wrong_answers' => 0,
                    ]
                );

                foreach ($topicAnswers as $answer) {
                    // Conta solo le domande a cui l'utente ha effettivamente risposto
                    if ($answer->user_answer !== null) {
                        $progress->markQuestionCompleted(
                            $answer->question_id,
                            $answer->is_correct
                        );
                    }
                }
            }

            // Log per debug (rimuovi in produzione)
            logger()->info('Quiz completato:', [
                'session_id' => $this->quizSession->id,
                'correct' => $correct,
                'wrong' => $wrong,
                'unanswered' => $unanswered,
                'total_questions' => $answers->count(),
                'errors_saved_to_db' => $answers->whereNotNull('user_answer')
                    ->where('is_correct', false)
                    ->count()
            ]);
        });

        // Reindirizza alla pagina dei risultati
        $this->redirect(QuizResults::getUrl(['session' => $this->session]));
    }

    /**
     * Aggiorna le statistiche in tempo reale
     */
    protected function updateStats(): void
    {
        $answers = $this->quizSession->quizAnswers;
        $this->answeredCount = $answers->whereNotNull('user_answer')->count();
        $this->correctCount = $answers->where('is_correct', true)->count();
        $this->wrongCount = $answers->where('is_correct', false)->where('user_answer', '!==', null)->count();
    }

    /**
     * Verifica se tutte le domande sono state risposte
     */
    public function getAllAnswered(): bool
    {
        return $this->answeredCount === count($this->quizSession->quizAnswers);
    }

    /**
     * Ottiene il colore per il bottone della domanda
     */
    public function getQuestionButtonColor(int $index): string
    {
        $answer = $this->quizSession->quizAnswers[$index];

        if ($answer->user_answer === null) {
            return 'gray'; // Non risposta
        }

        // Per quiz con manuale, mostra se è corretta o meno
        if ($this->quizSession->metadata['with_manual'] ?? false) {
            return $answer->is_correct ? 'success' : 'danger';
        }

        return 'primary'; // Risposta data
    }

    /**
     * Timer per il quiz ministeriale
     */
    public function decrementTimer(): void
    {
        if ($this->remainingTime > 0 && !$this->quizCompleted) {
            $this->remainingTime--;

            // Se il tempo è scaduto, completa automaticamente
            if ($this->remainingTime === 0 && $this->quizSession->type === 'ministerial') {
                $this->completeQuiz();
            }
        }
    }

    public function getFormattedTime(): string
    {
        $minutes = floor($this->remainingTime / 60);
        $seconds = $this->remainingTime % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}