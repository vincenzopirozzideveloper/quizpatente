<?php
// app/Filament/Pages/Quiz/QuizSelection.php

namespace App\Filament\Pages\Quiz;

use App\Models\Topic;
use Filament\Pages\Page;
use App\Models\UserError;
use App\Services\QuizService;
use App\Models\MinisterialQuiz;
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
    protected static ?string $title = 'Centro Quiz';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.quiz.quiz-selection';

    public ?string $selectedTopic = null;
    public int $errorsToReview = 0;
    public array $quizStats = [];
    public Collection $availableTopics;
    public array $ministerialProgress = [];
    public Collection $availableMinisterialQuizzes;
    public int $maxErrors = 3;

    protected QuizService $quizService;

    public function boot()
    {
        $this->quizService = app(QuizService::class);
    }

    public function mount(): void
    {
        $this->loadQuizStats();
        $this->loadMinisterialQuizzes();
        $this->loadAvailableTopics();
        $this->loadErrorsToReview();
    }

    protected function loadErrorsToReview(): void
    {
        $this->errorsToReview = UserError::where('user_id', Auth::id())
            ->notMastered()
            ->count();
    }

    protected function loadAvailableTopics(): void
    {
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

    protected function loadMinisterialQuizzes(): void
    {
        $this->availableMinisterialQuizzes = $this->quizService->getAvailableMinisterialQuizzes(Auth::user());
        $this->ministerialProgress = $this->quizService->getMinisterialQuizzesProgress(Auth::user());

        if ($this->availableMinisterialQuizzes->isNotEmpty()) {
            $this->maxErrors = $this->availableMinisterialQuizzes->first()['max_errors'];
        }
    }

    protected function loadQuizStats(): void
    {
        $user = Auth::user();
        $this->quizStats = $this->quizService->getQuizTypeStats($user);
    }

    // Quiz Ministeriale Ufficiale
    public function showMinisterialQuizList(): void
    {
        $this->dispatch('open-modal', id: 'ministerial-quiz-list');
    }

    public function startMinisterialQuiz(int $ministerialQuizId): void
    {
        try {
            $quiz = $this->quizService->generateMinisterialQuizSession(Auth::user(), $ministerialQuizId);
            $this->redirect(QuizPlay::getUrl(['session' => $quiz->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Quiz Ministeriale con Manuale
    public function startMinisterialQuizWithManual(): void
    {
        try {
            $quiz = $this->quizService->generateMinisterialQuizWithManual(Auth::user());
            $this->redirect(QuizPlay::getUrl(['session' => $quiz->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Quiz per Argomento
    public function showTopicSelection(): void
    {
        $this->dispatch('open-modal', id: 'topic-selection');
    }

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

        try {
            $quiz = $this->quizService->generateTopicQuiz(Auth::user(), $this->selectedTopic, true);
            $this->redirect(QuizPlay::getUrl(['session' => $quiz->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Quiz Ripasso Errori
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

        try {
            $quiz = $this->quizService->generateErrorsReviewQuiz(Auth::user());
            $this->redirect(QuizPlay::getUrl(['session' => $quiz->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Errore')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

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
                        ->helperText('Il quiz includerà domande casuali da questo argomento con accesso al manuale'),
                ])
        ];
    }
}