<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notifications_enabled',
        'email_notifications',
        'daily_reminder',
        'reminder_time',
        'theme',
        'difficulty_preference',
        'show_explanations',
        'show_timer',
        'sound_effects',
        'questions_per_session',
        'preferences',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'daily_reminder' => 'boolean',
        'show_explanations' => 'boolean',
        'show_timer' => 'boolean',
        'sound_effects' => 'boolean',
        'questions_per_session' => 'integer',
        'preferences' => 'array',
        'reminder_time' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getForUser($userId)
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'notifications_enabled' => true,
                'email_notifications' => false,
                'daily_reminder' => false,
                'theme' => 'light',
                'difficulty_preference' => 'mixed',
                'show_explanations' => true,
                'show_timer' => true,
                'sound_effects' => true,
                'questions_per_session' => 40,
            ]
        );
    }
}