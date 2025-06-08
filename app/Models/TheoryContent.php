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
        'code',
        'title',
        'content',
        'media',
        'order',
        'is_published',
        'image_url',
        'image_caption',
        'image_position',
        'metadata',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'media' => 'array',
        'metadata' => 'array',
        'order' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function theoryContents(): HasMany
    {
        return $this->hasMany(TheoryContent::class)->orderBy('order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Tutti i progressi degli utenti (per admin)
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserTheoryProgress::class);
    }

    /**
     * Relazione con il progresso dell'utente corrente
     */
    public function currentUserProgress(): HasOne
    {
        return $this->hasOne(UserTheoryProgress::class)
            ->where('user_id', auth()->id());
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('code');
    }

    /**
     * Genera automaticamente il codice basato sull'ordine
     */
    public static function generateCode($topicId): string
    {
        $topic = Topic::find($topicId);
        $lastContent = self::where('topic_id', $topicId)
            ->orderBy('order', 'desc')
            ->first();
            
        $nextNumber = $lastContent ? (intval(substr($lastContent->code, strpos($lastContent->code, '.') + 1)) + 1) : 1;
        
        return $topic->code . '.' . $nextNumber;
    }
}