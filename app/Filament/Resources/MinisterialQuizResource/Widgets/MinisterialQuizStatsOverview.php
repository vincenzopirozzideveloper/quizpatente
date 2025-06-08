<?php
// app/Filament/Resources/MinisterialQuizResource/Widgets/MinisterialQuizStatsOverview.php

namespace App\Filament\Resources\MinisterialQuizResource\Widgets;

use App\Models\MinisterialQuiz;
use App\Models\QuizSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MinisterialQuizStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalQuizzes = MinisterialQuiz::count();
        $activeQuizzes = MinisterialQuiz::where('is_active', true)->count();
        $validQuizzes = MinisterialQuiz::has('questions', '=', 30)->count();
        
        $totalSessions = QuizSession::where('type', 'ministerial')->count();
        $passedSessions = QuizSession::where('type', 'ministerial')
            ->where('is_passed', true)
            ->count();
        
        $avgScore = QuizSession::where('type', 'ministerial')
            ->completed()
            ->avg('score') ?? 0;

        return [
            Stat::make('Quiz Totali', $totalQuizzes)
                ->description("{$activeQuizzes} attivi, {$validQuizzes} validi")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
                
            Stat::make('Sessioni Totali', $totalSessions)
                ->description('Quiz giocati dagli utenti')
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),
                
            Stat::make('Tasso Successo', $totalSessions > 0 ? round(($passedSessions / $totalSessions) * 100) . '%' : '0%')
                ->description("{$passedSessions} quiz superati")
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
                
            Stat::make('Media Punteggi', round($avgScore, 1) . '%')
                ->description('Punteggio medio ottenuto')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}