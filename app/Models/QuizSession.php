<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'topic_id',
        'ministerial_quiz_id',
        'total_questions',
        'correct_answers',
        'wrong_answers',
        'unanswered',
        'score',
        'is_passed',
        'time_spent',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'unanswered' => 'integer',
        'time_spent' => 'integer',
        'score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }

    public function ministerialQuiz(): BelongsTo
    {
        return $this->belongsTo(MinisterialQuiz::class);
    }
    
    // Aggiornare il metodo calculateScore per usare max_errors dal quiz ministeriale
    public function calculateScore()
    {
        $this->score = $this->total_questions > 0 
            ? round(($this->correct_answers / $this->total_questions) * 100, 2)
            : 0;
        
        // Usa il max_errors del quiz ministeriale se presente, altrimenti usa il default
        $maxErrors = $this->metadata['max_errors'] ?? 3;
        
        $this->is_passed = $this->wrong_answers <= $maxErrors;
    }

    public function getDurationAttribute()
    {
        if (!$this->time_spent) return null;
        
        $minutes = floor($this->time_spent / 60);
        $seconds = $this->time_spent % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}