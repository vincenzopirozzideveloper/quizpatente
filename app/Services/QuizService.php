<?php
// app/Services/QuizService.php

namespace App\Services;

use App\Models\Question;
use App\Models\QuizSession;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserError;
use App\Models\MinisterialQuiz;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuizService
{
    const QUESTIONS_PER_QUIZ = 30;
    const MAX_ERRORS_IN_QUIZ = 3;
    
    const QUIZ_TYPE_MINISTERIAL = 'ministerial';
    const QUIZ_TYPE_MINISTERIAL_MANUAL = 'ministerial_manual';
    const QUIZ_TYPE_TOPIC = 'topic';
    const QUIZ_TYPE_ERRORS_REVIEW = 'errors_review';
    
    /**
     * Genera un quiz ministeriale predefinito (senza manuale)
     */
    public function generateMinisterialQuizSession(User $user, int $ministerialQuizId): QuizSession
    {
        $ministerialQuiz = MinisterialQuiz::with('questions')->findOrFail($ministerialQuizId);
        
        if (!$ministerialQuiz->is_valid) {
            throw new \Exception('Il quiz ministeriale deve avere esattamente 30 domande.');
        }
        
        if (!$ministerialQuiz->is_active) {
            throw new \Exception('Il quiz ministeriale non è attivo.');
        }
        
        return $this->createQuizSession(
            $user,
            self::QUIZ_TYPE_MINISTERIAL,
            $ministerialQuiz->questions,
            null,
            [
                'ministerial_quiz_id' => $ministerialQuiz->id,
                'ministerial_quiz_name' => $ministerialQuiz->name,
                'max_errors' => $ministerialQuiz->max_errors,
                'with_manual' => false,
            ],
            $ministerialQuiz->id
        );
    }
    
    /**
     * Genera un quiz ministeriale con manuale
     */
    public function generateMinisterialQuizWithManual(User $user): QuizSession
    {
        // Prendi un quiz ministeriale random valido
        $ministerialQuiz = MinisterialQuiz::active()
            ->has('questions', '=', 30)
            ->inRandomOrder()
            ->first();
            
        if (!$ministerialQuiz) {
            // Se non ci sono quiz ministeriali, crea un quiz con domande casuali
            $questions = Question::active()
                ->withTheory()
                ->inRandomOrder()
                ->limit(self::QUESTIONS_PER_QUIZ)
                ->get();
                
            return $this->createQuizSession(
                $user,
                self::QUIZ_TYPE_MINISTERIAL_MANUAL,
                $questions,
                null,
                [
                    'with_manual' => true,
                    'max_errors' => self::MAX_ERRORS_IN_QUIZ,
                ]
            );
        }
        
        return $this->createQuizSession(
            $user,
            self::QUIZ_TYPE_MINISTERIAL_MANUAL,
            $ministerialQuiz->questions,
            null,
            [
                'ministerial_quiz_id' => $ministerialQuiz->id,
                'ministerial_quiz_name' => $ministerialQuiz->name,
                'max_errors' => $ministerialQuiz->max_errors,
                'with_manual' => true,
            ],
            $ministerialQuiz->id
        );
    }
    
    /**
     * Genera un quiz per argomento (con manuale)
     */
    public function generateTopicQuiz(User $user, int $topicId, bool $withManual = true): QuizSession
    {
        $topic = Topic::findOrFail($topicId);
        
        $questions = Question::query()
            ->active()
            ->byTopic($topicId)
            ->withTheory()
            ->inRandomOrder()
            ->limit(self::QUESTIONS_PER_QUIZ)
            ->get();
            
        if ($questions->count() < self::QUESTIONS_PER_QUIZ) {
            $remainingCount = self::QUESTIONS_PER_QUIZ - $questions->count();
            $additionalQuestions = Question::query()
                ->active()
                ->withTheory()
                ->whereNotIn('id', $questions->pluck('id'))
                ->inRandomOrder()
                ->limit($remainingCount)
                ->get();
                
            $questions = $questions->concat($additionalQuestions);
        }
        
        return $this->createQuizSession(
            $user,
            self::QUIZ_TYPE_TOPIC,
            $questions,
            $topicId,
            [
                'topic_name' => $topic->name,
                'with_manual' => $withManual,
            ]
        );
    }
    
    /**
     * Genera un quiz basato sugli errori dell'utente
     */
    public function generateErrorsReviewQuiz(User $user): QuizSession
    {
        $errorQuestionIds = UserError::query()
            ->where('user_id', $user->id)
            ->notMastered()
            ->orderBy('error_count', 'desc')
            ->limit(self::QUESTIONS_PER_QUIZ)
            ->pluck('question_id');
            
        $questions = Question::query()
            ->active()
            ->withTheory()
            ->whereIn('id', $errorQuestionIds)
            ->get();
            
        if ($questions->count() < self::QUESTIONS_PER_QUIZ) {
            $remainingCount = self::QUESTIONS_PER_QUIZ - $questions->count();
            $topicIds = $questions->pluck('topic_id')->unique();
            
            $additionalQuestions = Question::query()
                ->active()
                ->withTheory()
                ->whereIn('topic_id', $topicIds)
                ->whereNotIn('id', $questions->pluck('id'))
                ->inRandomOrder()
                ->limit($remainingCount)
                ->get();
                
            if ($questions->count() + $additionalQuestions->count() < self::QUESTIONS_PER_QUIZ) {
                $stillNeeded = self::QUESTIONS_PER_QUIZ - $questions->count() - $additionalQuestions->count();
                $randomQuestions = Question::query()
                    ->active()
                    ->withTheory()
                    ->whereNotIn('id', $questions->pluck('id')->concat($additionalQuestions->pluck('id')))
                    ->inRandomOrder()
                    ->limit($stillNeeded)
                    ->get();
                    
                $additionalQuestions = $additionalQuestions->concat($randomQuestions);
            }
            
            $questions = $questions->concat($additionalQuestions);
        }
        
        return $this->createQuizSession(
            $user,
            self::QUIZ_TYPE_ERRORS_REVIEW,
            $questions,
            null,
            [
                'error_questions_count' => $errorQuestionIds->count(),
                'with_manual' => true,
            ]
        );
    }
    
    /**
     * Crea una nuova sessione quiz
     */
    protected function createQuizSession(
        User $user, 
        string $type, 
        Collection $questions, 
        ?int $topicId = null,
        array $metadata = [],
        ?int $ministerialQuizId = null
    ): QuizSession {
        return DB::transaction(function () use ($user, $type, $questions, $topicId, $metadata, $ministerialQuizId) {
            $session = QuizSession::create([
                'user_id' => $user->id,
                'type' => $type,
                'topic_id' => $topicId,
                'ministerial_quiz_id' => $ministerialQuizId,
                'total_questions' => $questions->count(),
                'correct_answers' => 0,
                'wrong_answers' => 0,
                'unanswered' => $questions->count(),
                'started_at' => now(),
                'metadata' => $metadata,
            ]);
            
            $questions->each(function ($question, $index) use ($session) {
                $session->quizAnswers()->create([
                    'question_id' => $question->id,
                    'order' => $index + 1,
                    'is_correct' => false,
                ]);
            });
            
            return $session->load('quizAnswers.question.theoryContent');
        });
    }
    
    /**
     * Ottiene i quiz ministeriali disponibili
     */
    public function getAvailableMinisterialQuizzes(User $user): Collection
    {
        return MinisterialQuiz::active()
            ->ordered()
            ->withCount('questions')
            ->having('questions_count', '=', 30)
            ->with(['sessions' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->completed()
                    ->latest();
            }])
            ->get()
            ->map(function ($quiz) use ($user) {
                $completed = $quiz->isCompletedByUser($user->id);
                $bestScore = $quiz->getUserBestScore($user->id);
                
                return [
                    'id' => $quiz->id,
                    'name' => $quiz->name,
                    'description' => $quiz->description,
                    'max_errors' => $quiz->max_errors,
                    'is_completed' => $completed,
                    'best_score' => $bestScore,
                    'attempts' => $quiz->sessions->count(),
                ];
            });
    }
    
    /**
     * Ottiene la progressione dei quiz ministeriali
     */
    public function getMinisterialQuizzesProgress(User $user): array
    {
        $totalQuizzes = MinisterialQuiz::active()
            ->has('questions', '=', 30)
            ->count();
            
        $completedQuizzes = MinisterialQuiz::active()
            ->has('questions', '=', 30)
            ->whereHas('sessions', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->completed();
            })
            ->count();
            
        $passedQuizzes = MinisterialQuiz::active()
            ->has('questions', '=', 30)
            ->whereHas('sessions', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->completed()
                    ->where('is_passed', true);
            })
            ->count();
            
        return [
            'total' => $totalQuizzes,
            'completed' => $completedQuizzes,
            'passed' => $passedQuizzes,
            'remaining' => $totalQuizzes - $completedQuizzes,
            'completion_percentage' => $totalQuizzes > 0 
                ? round(($completedQuizzes / $totalQuizzes) * 100, 1) 
                : 0,
            'success_percentage' => $completedQuizzes > 0 
                ? round(($passedQuizzes / $completedQuizzes) * 100, 1) 
                : 0,
        ];
    }
    
    /**
     * Ottiene le statistiche per tipo di quiz
     */
    public function getQuizTypeStats(User $user): array
    {
        $stats = [];
        
        // Statistiche quiz ministeriali
        $ministerialStats = QuizSession::where('user_id', $user->id)
            ->whereIn('type', [self::QUIZ_TYPE_MINISTERIAL, self::QUIZ_TYPE_MINISTERIAL_MANUAL])
            ->completed()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed,
                AVG(score) as avg_score,
                MAX(score) as best_score
            ')
            ->first();
            
        $stats['ministerial'] = [
            'total' => $ministerialStats->total ?? 0,
            'passed' => $ministerialStats->passed ?? 0,
            'avg_score' => round($ministerialStats->avg_score ?? 0, 1),
            'best_score' => round($ministerialStats->best_score ?? 0, 1),
        ];
        
        return $stats;
    }
}