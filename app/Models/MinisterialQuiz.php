<?php
// app/Models/MinisterialQuiz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MinisterialQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'order',
        'is_active',
        'max_errors',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'max_errors' => 'integer',
    ];

    /**
     * Le domande del quiz
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'ministerial_quiz_questions')
            ->withPivot('order')
            ->orderBy('ministerial_quiz_questions.order');
    }

    /**
     * Le sessioni di questo quiz
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(QuizSession::class);
    }

    /**
     * Verifica se il quiz è stato completato dall'utente
     */
    public function isCompletedByUser($userId): bool
    {
        return $this->sessions()
            ->where('user_id', $userId)
            ->completed()
            ->exists();
    }

    /**
     * Ottiene il miglior risultato dell'utente
     */
    public function getUserBestScore($userId): ?float
    {
        return $this->sessions()
            ->where('user_id', $userId)
            ->completed()
            ->max('score');
    }

    /**
     * Scope per quiz attivi
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per quiz ordinati
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }

    /**
     * Verifica se il quiz ha esattamente 30 domande
     */
    public function getIsValidAttribute(): bool
    {
        return $this->questions()->count() === 30;
    }
}