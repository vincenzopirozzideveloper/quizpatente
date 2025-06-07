<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTheoryProgress extends Model
{
    use HasFactory;

    protected $table = 'user_theory_progress';

    protected $fillable = [
        'user_id',
        'theory_content_id',
        'status',
        'started_at',
        'completed_at',
        'time_spent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_spent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theoryContent(): BelongsTo
    {
        return $this->belongsTo(TheoryContent::class);
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeReading($query)
    {
        return $query->where('status', 'reading');
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public static function markAsRead($userId, $contentId)
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'theory_content_id' => $contentId,
            ],
            [
                'status' => 'read',
                'completed_at' => now(),
            ]
        );
    }

    public static function markAsReading($userId, $contentId)
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'theory_content_id' => $contentId,
            ],
            [
                'status' => 'reading',
                'started_at' => now(),
            ]
        );
    }

    public static function toggleReadStatus($userId, $contentId)
    {
        $progress = static::firstOrCreate(
            [
                'user_id' => $userId,
                'theory_content_id' => $contentId,
            ],
            [
                'status' => 'unread',
            ]
        );

        if ($progress->status === 'read') {
            $progress->update([
                'status' => 'unread',
                'completed_at' => null,
            ]);
        } else {
            $progress->update([
                'status' => 'read',
                'completed_at' => now(),
            ]);
        }

        return $progress;
    }
}