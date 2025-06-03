<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoryContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'title',
        'content',
        'media',
        'order',
        'is_published',
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

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('title');
    }

    public function getPreviousAttribute()
    {
        return $this->topic->theoryContents()
            ->published()
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    public function getNextAttribute()
    {
        return $this->topic->theoryContents()
            ->published()
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
}