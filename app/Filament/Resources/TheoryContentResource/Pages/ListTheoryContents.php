<?php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListTheoryContents extends ListRecords
{
    protected static string $resource = TheoryContentResource::class;

    // Ascolta l'evento per aggiornare i tab
    #[On('theory-progress-updated')]
    public function refresh(): void
    {
        // Questo forzerà il re-render della pagina e aggiornerà i badge
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TheoryContentResource\Widgets\TheoryProgressStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tutti')
                ->badge(static::getResource()::getModel()::published()->count())
                ->badgeColor('gray'),
                
            'unread' => Tab::make('Non letti')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereDoesntHave('currentUserProgress', 
                        fn ($q) => $q->where('status', 'read')
                    )
                )
                ->badge(
                    static::getResource()::getModel()::published()
                        ->whereDoesntHave('currentUserProgress', 
                            fn ($q) => $q->where('status', 'read')
                        )
                        ->count()
                )
                ->badgeColor('danger'),
                
            'completed' => Tab::make('Completati')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('currentUserProgress', 
                        fn ($q) => $q->where('status', 'read')
                    )
                )
                ->badge(
                    static::getResource()::getModel()::published()
                        ->whereHas('currentUserProgress', 
                            fn ($q) => $q->where('status', 'read')
                        )
                        ->count()
                )
                ->badgeColor('success'),
        ];
    }
}