<?php

namespace App\Filament\Resources\TopicResource\Widgets;

use App\Models\Topic;
use App\Models\TheoryContent;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopicStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalTopics = Topic::count();
        $activeTopics = Topic::where('is_active', true)->count();
        $totalContents = TheoryContent::count();
        $publishedContents = TheoryContent::where('is_published', true)->count();
        $contentsWithImages = TheoryContent::whereNotNull('image_url')->count();
        $totalQuestions = Question::count();
        $activeQuestions = Question::where('is_active', true)->count();
        
        return [
            Stat::make('Argomenti Totali', $totalTopics)
                ->description("{$activeTopics} attivi")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('Contenuti Teoria', $totalContents)
                ->description("{$publishedContents} pubblicati")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
                
            Stat::make('Domande Quiz', $totalQuestions)
                ->description("{$activeQuestions} attive")
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('success'),
                
            Stat::make('Contenuti con Immagini', $contentsWithImages)
                ->description(number_format(($totalContents > 0 ? ($contentsWithImages / $totalContents * 100) : 0), 0) . '% del totale')
                ->descriptionIcon('heroicon-m-photo')
                ->color('warning'),
        ];
    }
}