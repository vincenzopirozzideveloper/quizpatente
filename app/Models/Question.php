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
        'text',
        'correct_answer',
        'explanation',
        'image_url',
        'media',
        'difficulty_level',
        'is_ministerial',
        'is_active',
        'order',
    ];

    protected $casts = [
        'correct_answer' => 'boolean',
        'is_ministerial' => 'boolean',
        'is_active' => 'boolean',
        'media' => 'array',
        'difficulty_level' => 'integer',
        'order' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function userErrors(): HasMany
    {
        return $this->hasMany(UserError::class);
    }

    public function videoExplanation(): HasOne
    {
        return $this->hasOne(VideoExplanation::class);
    }

    public function userError(): HasOne
    {
        return $this->hasOne(UserError::class)->where('user_id', auth()->id());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMinisterial($query)
    {
        return $query->where('is_ministerial', true);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

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
}