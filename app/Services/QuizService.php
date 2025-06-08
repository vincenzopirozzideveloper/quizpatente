<?php

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
    const QUIZ_TYPE_TOPIC = 'topic';
    const QUIZ_TYPE_ERRORS_REVIEW = 'errors_review';
    const QUIZ_TYPE_CUSTOM = 'custom';
    
    public function canGenerateQuiz(string $type, ?int $topicId = null): array
    {
        switch ($type) {
            case self::QUIZ_TYPE_MINISTERIAL:
            case 'ministerial_manual':
                $availableQuestions = Question::active()->withTheory()->count();
                return [
                    'can_generate' => $availableQuestions >= self::QUESTIONS_PER_QUIZ,
                    'message' => $availableQuestions < self::QUESTIONS_PER_QUIZ 
                        ? "Servono almeno 30 domande. Disponibili: {$availableQuestions}"
                        : null
                ];
                
            case self::QUIZ_TYPE_TOPIC:
                if (!$topicId) {
                    return ['can_generate' => false, 'message' => 'Devi selezionare un argomento'];
                }
                $topicQuestions = Question::active()->byTopic($topicId)->withTheory()->count();
                return [
                    'can_generate' => $topicQuestions > 0,
                    'message' => $topicQuestions < self::QUESTIONS_PER_QUIZ
                        ? "L'argomento ha solo {$topicQuestions} domande. Verranno aggiunte domande casuali."
                        : null
                ];
                
            case self::QUIZ_TYPE_ERRORS_REVIEW:
                $errors = UserError::where('user_id', auth()->id())->notMastered()->count();
                return [
                    'can_generate' => $errors > 0,
                    'message' => $errors < self::QUESTIONS_PER_QUIZ
                        ? "Hai solo {$errors} errori. Verranno aggiunte domande casuali."
                        : null
                ];
                
            default:
                return ['can_generate' => false, 'message' => 'Tipo di quiz non valido'];
        }
    }
    
    public function generateMinisterialQuizSession(User $user, int $ministerialQuizId): QuizSession
    {
        $ministerialQuiz = MinisterialQuiz::with('questions')->findOrFail($ministerialQuizId);
        
        if (!$ministerialQuiz->is_valid) {
            throw new \Exception('Il quiz ministeriale non è valido. Deve avere esattamente 30 domande.');
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
            ],
            $ministerialQuiz->id
        );
    }
    
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
    
    public function generateMinisterialQuiz(User $user, bool $withManual = false): QuizSession
    {
        $questions = Question::query()
            ->active()
            ->ministerial()
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
            $withManual ? 'ministerial_manual' : 'ministerial',
            $questions,
            null,
            [
                'with_manual' => $withManual,
                'ministerial_name' => 'Quiz Ministeriale Automatico'
            ]
        );
    }
    
    public function generateTopicQuiz(User $user, int $topicId): QuizSession
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
            ['topic_name' => $topic->name]
        );
    }
    
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
            ['error_questions_count' => $errorQuestionIds->count()]
        );
    }
    
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
    
    public function getQuizTypeStats(User $user): array
    {
        $stats = [];
        
        $ministerialStats = QuizSession::where('user_id', $user->id)
            ->where('type', self::QUIZ_TYPE_MINISTERIAL)
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
        
        $stats['ministerial'] = array_merge(
            $stats['ministerial'],
            $this->getMinisterialQuizzesProgress($user)
        );
        
        $topicStats = QuizSession::where('user_id', $user->id)
            ->where('type', self::QUIZ_TYPE_TOPIC)
            ->completed()
            ->with('topic')
            ->get()
            ->groupBy('topic_id');
            
        $stats['topics'] = $topicStats->map(function ($sessions, $topicId) {
            $topic = $sessions->first()->topic;
            return [
                'topic_name' => $topic->name ?? 'N/D',
                'total' => $sessions->count(),
                'passed' => $sessions->where('is_passed', true)->count(),
                'avg_score' => round($sessions->avg('score'), 1),
            ];
        });
        
        $errorReviewStats = QuizSession::where('user_id', $user->id)
            ->where('type', self::QUIZ_TYPE_ERRORS_REVIEW)
            ->completed()
            ->selectRaw('
                COUNT(*) as total,
                AVG(score) as avg_score,
                SUM(correct_answers) as total_corrected
            ')
            ->first();
            
        $stats['errors_review'] = [
            'total' => $errorReviewStats->total ?? 0,
            'avg_score' => round($errorReviewStats->avg_score ?? 0, 1),
            'total_corrected' => $errorReviewStats->total_corrected ?? 0,
        ];
        
        return $stats;
    }
}