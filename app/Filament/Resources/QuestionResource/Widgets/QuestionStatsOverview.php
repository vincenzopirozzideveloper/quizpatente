<?php

namespace App\Filament\Resources\QuestionResource\Widgets;

use App\Models\Question;
use App\Models\Topic;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuestionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalQuestions = Question::count();
        $activeQuestions = Question::where('is_active', true)->count();
        $ministerialQuestions = Question::where('is_ministerial', true)->count();
        $questionsWithTheory = Question::whereNotNull('theory_content_id')->count();
        $questionsWithImages = Question::whereNotNull('image_url')->count();
        
        // Domande per difficoltà
        $easyQuestions = Question::where('difficulty_level', 1)->count();
        $mediumQuestions = Question::where('difficulty_level', 2)->count();
        $hardQuestions = Question::where('difficulty_level', 3)->count();
        
        return [
            Stat::make('Domande Totali', $totalQuestions)
                ->description("{$activeQuestions} attive")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('Domande Ministeriali', $ministerialQuestions)
                ->description(number_format(($totalQuestions > 0 ? ($ministerialQuestions / $totalQuestions * 100) : 0), 0) . '% del totale')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
                
            Stat::make('Con Teoria Collegata', $questionsWithTheory)
                ->description(number_format(($totalQuestions > 0 ? ($questionsWithTheory / $totalQuestions * 100) : 0), 0) . '% collegate')
                ->descriptionIcon('heroicon-m-link')
                ->color('info'),
                
            Stat::make('Difficoltà', "{$easyQuestions}/{$mediumQuestions}/{$hardQuestions}")
                ->description('Facile/Medio/Difficile')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning')
                ->chart([$easyQuestions, $mediumQuestions, $hardQuestions]),
        ];
    }
    
    protected static ?string $pollingInterval = '30s';
}