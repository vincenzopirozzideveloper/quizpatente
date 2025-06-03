<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'icon',
        'total_questions',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'total_questions' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function theoryContents(): HasMany
    {
        return $this->hasMany(TheoryContent::class)->orderBy('order');
    }

    public function userProgress(): HasOne
    {
        return $this->hasOne(UserTopicProgress::class)->where('user_id', auth()->id());
    }

    public function allUserProgress(): HasMany
    {
        return $this->hasMany(UserTopicProgress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('code');
    }

    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_questions === 0) return 0;
        
        $completed = $this->userProgress?->completed_questions ?? 0;
        return round(($completed / $this->total_questions) * 100, 1);
    }
}