<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TheoryContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'subtopic_id',
        'code',
        'content',
        'media',
        'order',
        'is_published',
        'image_url',
        'image_caption',
        'image_position',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'media' => 'array',
        'order' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('code');
    }
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserTheoryProgress::class, 'theory_content_id');
    }

    public function currentUserProgress(): HasOne
    {
        return $this->hasOne(UserTheoryProgress::class, 'theory_content_id')->where('user_id', auth()->id());
    }
}