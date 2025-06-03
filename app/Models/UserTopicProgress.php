<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTopicProgress extends Model
{
    use HasFactory;

    protected $table = 'user_topic_progress';

    protected $fillable = [
        'user_id',
        'topic_id',
        'completed_questions',
        'correct_answers',
        'wrong_answers',
        'accuracy_rate',
        'last_activity',
        'completed_at',
        'completed_question_ids',
    ];

    protected $casts = [
        'completed_question_ids' => 'array',
        'last_activity' => 'datetime',
        'completed_at' => 'datetime',
        'completed_questions' => 'integer',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'accuracy_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function getCompletionPercentageAttribute(): float
    {
        if (!$this->topic || $this->topic->total_questions === 0) return 0;
        
        return round(($this->completed_questions / $this->topic->total_questions) * 100, 1);
    }

    public function markQuestionCompleted($questionId, $isCorrect)
    {
        $completedIds = $this->completed_question_ids ?? [];
        
        if (!in_array($questionId, $completedIds)) {
            $completedIds[] = $questionId;
            $this->completed_question_ids = $completedIds;
            $this->completed_questions = count($completedIds);
        }

        if ($isCorrect) {
            $this->correct_answers++;
        } else {
            $this->wrong_answers++;
        }

        $totalAnswers = $this->correct_answers + $this->wrong_answers;
        $this->accuracy_rate = $totalAnswers > 0 
            ? round(($this->correct_answers / $totalAnswers) * 100, 2)
            : 0;

        $this->last_activity = now();

        if ($this->completed_questions >= $this->topic->total_questions) {
            $this->completed_at = now();
        }

        $this->save();
    }
}