<?php

namespace App\Filament\Resources\TheoryContentResource\Widgets;

use App\Models\TheoryContent;
use App\Models\Topic;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class TheoryProgressStats extends BaseWidget
{
    // Ascolta l'evento per aggiornare le statistiche
    #[On('theory-progress-updated')]
    public function refresh(): void
    {
        // Questo forzerà il re-render del widget
    }
    
    protected function getStats(): array
    {        
        // Contenuti totali pubblicati
        $totalContents = TheoryContent::published()->count();
        
        // Contenuti completati (letti)
        $completedContents = TheoryContent::published()
            ->whereHas('currentUserProgress', function ($query) {
                $query->where('status', 'read');
            })->count();
            
        // Contenuti in lettura
        $inProgressContents = TheoryContent::published()
            ->whereHas('currentUserProgress', function ($query) {
                $query->where('status', 'reading');
            })->count();
            
        // Progresso percentuale
        $progress = $totalContents > 0 
            ? round(($completedContents / $totalContents) * 100, 1) 
            : 0;
            
        // Calcola gli argomenti (topics) completati correttamente
        $totalTopics = Topic::active()->count();
        
        // Un topic è completato quando TUTTI i suoi contenuti sono stati letti
        $completedTopics = 0;
        $topics = Topic::active()->with(['theoryContents' => function ($query) {
            $query->published();
        }])->get();
        
        foreach ($topics as $topic) {
            $topicContentsCount = $topic->theoryContents->count();
            
            if ($topicContentsCount === 0) {
                continue; // Skip topics without contents
            }
            
            $readContentsCount = TheoryContent::where('topic_id', $topic->id)
                ->published()
                ->whereHas('currentUserProgress', function ($query) {
                    $query->where('status', 'read');
                })
                ->count();
                
            if ($readContentsCount === $topicContentsCount) {
                $completedTopics++;
            }
        }

        return [
            Stat::make('Progresso totale', $progress . '%')
                ->description("{$completedContents} di {$totalContents} contenuti")
                ->descriptionIcon('heroicon-m-book-open')
                ->chart([
                    $totalContents - $completedContents - $inProgressContents, // Non letti
                    $inProgressContents, // In lettura
                    $completedContents // Completati
                ])
                ->color('success'),
                
            Stat::make('Completati', $completedContents)
                ->description('Contenuti letti')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('In lettura', $inProgressContents)
                ->description('Contenuti iniziati')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Argomenti completati', $completedTopics)
                ->description("Su {$totalTopics} totali")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
        ];
    }
}