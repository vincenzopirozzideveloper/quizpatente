<?php
// File: app/Filament/Resources/TopicResource/Widgets/TopicStatsOverview.php

namespace App\Filament\Resources\TopicResource\Widgets;

use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\TheoryContent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopicStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTopics = Topic::count();
        $activeTopics = Topic::where('is_active', true)->count();
        $totalSubtopics = Subtopic::count();
        $totalContents = TheoryContent::count();
        $publishedContents = TheoryContent::where('is_published', true)->count();
        $contentsWithImages = TheoryContent::whereNotNull('image_url')->count();
        
        return [
            Stat::make('Argomenti Totali', $totalTopics)
                ->description("{$activeTopics} attivi")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('Sottoargomenti', $totalSubtopics)
                ->description('Suddivisi per argomento')
                ->descriptionIcon('heroicon-m-folder')
                ->color('success'),
                
            Stat::make('Contenuti Teoria', $totalContents)
                ->description("{$publishedContents} pubblicati")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
                
            Stat::make('Contenuti con Immagini', $contentsWithImages)
                ->description(number_format(($totalContents > 0 ? ($contentsWithImages / $totalContents * 100) : 0), 0) . '% del totale')
                ->descriptionIcon('heroicon-m-photo')
                ->color('warning'),
        ];
    }
}