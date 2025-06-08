<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'theory_content_id',
        'text',
        'correct_answer',
        'explanation',
        'image_url',
        'media',
        'difficulty_level',
        'is_ministerial',
        'is_active',
        'order',
        'ministerial_number',
    ];

    protected $casts = [
        'correct_answer' => 'boolean',
        'is_ministerial' => 'boolean',
        'is_active' => 'boolean',
        'media' => 'array',
        'difficulty_level' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relazione con il topic
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Relazione con il contenuto teorico
     */
    public function theoryContent(): BelongsTo
    {
        return $this->belongsTo(TheoryContent::class);
    }

    /**
     * Risposte ai quiz per questa domanda
     */
    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Errori degli utenti per questa domanda
     */
    public function userErrors(): HasMany
    {
        return $this->hasMany(UserError::class);
    }

    /**
     * Video spiegazione per questa domanda
     */
    public function videoExplanation(): HasOne
    {
        return $this->hasOne(VideoExplanation::class);
    }

    /**
     * Errore dell'utente corrente per questa domanda
     */
    public function userError(): HasOne
    {
        return $this->hasOne(UserError::class)->where('user_id', auth()->id());
    }

    /**
     * Scope per domande attive
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per domande ministeriali
     */
    public function scopeMinisterial($query)
    {
        return $query->where('is_ministerial', true);
    }

    /**
     * Scope per domande per difficoltà
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope per domande per topic
     */
    public function scopeByTopic($query, $topicId)
    {
        return $query->where('topic_id', $topicId);
    }

    /**
     * Scope per domande con teoria associata
     */
    public function scopeWithTheory($query)
    {
        return $query->whereNotNull('theory_content_id');
    }

    /**
     * Ottiene le statistiche delle risposte dell'utente
     */
    public function getUserAnswerStatsAttribute()
    {
        $answers = $this->quizAnswers()
            ->whereHas('quizSession', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        return [
            'total_attempts' => $answers->count(),
            'correct' => $answers->where('is_correct', true)->count(),
            'incorrect' => $answers->where('is_correct', false)->count(),
            'accuracy' => $answers->count() > 0 
                ? round($answers->where('is_correct', true)->count() / $answers->count() * 100, 1)
                : 0,
        ];
    }

    /**
     * Ottiene il percorso completo della teoria (Topic > Theory)
     */
    public function getTheoryPathAttribute(): string
    {
        $path = [];
        
        if ($this->topic) {
            $path[] = $this->topic->name;
        }
        
        if ($this->theoryContent) {
            $path[] = $this->theoryContent->title;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Verifica se la domanda è stata risposta correttamente dall'utente
     */
    public function isAnsweredCorrectlyByUser(): bool
    {
        $lastAnswer = $this->quizAnswers()
            ->whereHas('quizSession', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->first();
            
        return $lastAnswer ? $lastAnswer->is_correct : false;
    }

    /**
     * Ottiene il numero di volte che l'utente ha sbagliato questa domanda
     */
    public function getUserErrorCountAttribute(): int
    {
        return $this->userError?->error_count ?? 0;
    }
}