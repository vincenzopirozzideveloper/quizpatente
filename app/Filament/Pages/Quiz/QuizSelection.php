<?php

namespace App\Filament\Pages\Quiz;

use App\Models\Topic;
use Filament\Pages\Page;
use App\Models\UserError;
use App\Services\QuizService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class QuizSelection extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Quiz';
    protected static ?string $title = 'Seleziona Quiz';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.quiz.quiz-selection';

    public ?string $selectedTopic = null;
    protected QuizService $quizService;
    public array $ministerialProgress = [];
    public Collection $availableMinisterialQuizzes;
    public int $maxErrors = 3;

    public function boot()
    {
        $this->quizService = app(QuizService::class);
    }

    public function mount(): void
    {
        $this->loadQuizStats();
        $this->loadMinisterialQuizzes();
    }
    /**
     * Mostra la lista dei quiz ministeriali
     */
    public function showMinisterialQuizList(): void
    {
        $this->dispatch('open-modal', id: 'ministerial-quiz-list');
    }

    /**
     * Avvia un quiz ministeriale specifico
     */
    public function startMinisterialQuiz(int $ministerialQuizId): void
    {
        try {
            $quiz = $this->quizService->generateMinisterialQuizSession(Auth::user(), $ministerialQuizId);
            $this->redirect(route('filament.quizpatente.pages.quiz.play', ['session' => $quiz->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function loadMinisterialQuizzes(): void
    {
        $this->availableMinisterialQuizzes = $this->quizService->getAvailableMinisterialQuizzes(Auth::user());
        $this->ministerialProgress = $this->quizService->getMinisterialQuizzesProgress(Auth::user());

        // Prendi il max_errors dal primo quiz disponibile
        if ($this->availableMinisterialQuizzes->isNotEmpty()) {
            $this->maxErrors = $this->availableMinisterialQuizzes->first()['max_errors'];
        }
    }

    public function loadQuizStats(): void
    {
        $user = Auth::user();
        $this->quizStats = $this->quizService->getQuizTypeStats($user);

        // Conta errori da ripassare
        $this->errorsToReview = UserError::where('user_id', $user->id)
            ->notMastered()
            ->count();

        // Carica topics disponibili
        $this->availableTopics = Topic::active()
            ->withCount([
                'questions' => function ($query) {
                    $query->active()->withTheory();
                }
            ])
            ->having('questions_count', '>', 0)
            ->ordered()
            ->get();
    }

    /**
     * Avvia un quiz ministeriale con manuale
     */
    public function startMinisterialQuizWithManual(): void
    {
        $canGenerate = $this->quizService->canGenerateQuiz(QuizService::QUIZ_TYPE_MINISTERIAL_WITH_MANUAL);

        if (!$canGenerate['can_generate']) {
            Notification::make()
                ->title('Impossibile creare il quiz')
                ->body('Non ci sono abbastanza domande disponibili.')
                ->danger()
                ->send();
            return;
        }

        $quiz = $this->quizService->generateMinisterialQuiz(Auth::user(), true);

        $this->redirect(route('filament.quizpatente.pages.quiz.play', ['session' => $quiz->id]));
    }

    /**
     * Mostra il modal per selezionare l'argomento
     */
    public function showTopicSelection(): void
    {
        $this->dispatch('open-modal', id: 'topic-selection');
    }

    /**
     * Avvia un quiz per argomento
     */
    public function startTopicQuiz(): void
    {
        if (!$this->selectedTopic) {
            Notification::make()
                ->title('Seleziona un argomento')
                ->body('Devi selezionare un argomento per continuare.')
                ->warning()
                ->send();
            return;
        }

        $canGenerate = $this->quizService->canGenerateQuiz(
            QuizService::QUIZ_TYPE_TOPIC,
            $this->selectedTopic
        );

        if (!$canGenerate['can_generate']) {
            Notification::make()
                ->title('Impossibile creare il quiz')
                ->body('Non ci sono abbastanza domande per questo argomento.')
                ->danger()
                ->send();
            return;
        }

        if ($canGenerate['message']) {
            Notification::make()
                ->title('Attenzione')
                ->body($canGenerate['message'])
                ->warning()
                ->send();
        }

        $quiz = $this->quizService->generateTopicQuiz(Auth::user(), $this->selectedTopic);

        $this->redirect(route('filament.quizpatente.pages.quiz.play', ['session' => $quiz->id]));
    }

    /**
     * Avvia un quiz di ripasso errori
     */
    public function startErrorsReviewQuiz(): void
    {
        if ($this->errorsToReview === 0) {
            Notification::make()
                ->title('Nessun errore da ripassare')
                ->body('Non hai errori da ripassare. Ottimo lavoro!')
                ->success()
                ->send();
            return;
        }

        $canGenerate = $this->quizService->canGenerateQuiz(QuizService::QUIZ_TYPE_ERRORS_REVIEW);

        if ($canGenerate['message']) {
            Notification::make()
                ->title('Attenzione')
                ->body($canGenerate['message'])
                ->warning()
                ->send();
        }

        $quiz = $this->quizService->generateErrorsReviewQuiz(Auth::user());

        $this->redirect(route('filament.quizpatente.pages.quiz.play', ['session' => $quiz->id]));
    }

    /**
     * Form per la selezione dell'argomento
     */
    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Select::make('selectedTopic')
                        ->label('Seleziona un argomento')
                        ->options(
                            $this->availableTopics->mapWithKeys(function ($topic) {
                                return [
                                    $topic->id => "{$topic->name} ({$topic->questions_count} domande)"
                                ];
                            })
                        )
                        ->required()
                        ->searchable()
                        ->placeholder('Scegli un argomento...')
                        ->helperText('Verranno selezionate 30 domande casuali da questo argomento'),
                ])
        ];
    }
}