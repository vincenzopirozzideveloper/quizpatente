<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserError extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'error_count',
        'last_error_date',
        'last_correct_date',
        'is_mastered',
    ];

    protected $casts = [
        'is_mastered' => 'boolean',
        'error_count' => 'integer',
        'last_error_date' => 'datetime',
        'last_correct_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeNotMastered($query)
    {
        return $query->where('is_mastered', false);
    }

    public function scopeByErrorCount($query, $order = 'desc')
    {
        return $query->orderBy('error_count', $order);
    }

    public function incrementError()
    {
        $this->increment('error_count');
        $this->last_error_date = now();
        $this->is_mastered = false;
        $this->save();
    }

    public function markCorrect()
    {
        $this->last_correct_date = now();
        
        // Consider mastered if answered correctly 3 times after errors
        if ($this->error_count > 0) {
            $correctAfterError = QuizAnswer::where('question_id', $this->question_id)
                ->whereHas('quizSession', function ($query) {
                    $query->where('user_id', $this->user_id)
                        ->where('created_at', '>', $this->last_error_date);
                })
                ->where('is_correct', true)
                ->count();

            if ($correctAfterError >= 3) {
                $this->is_mastered = true;
            }
        }
        
        $this->save();
    }
}