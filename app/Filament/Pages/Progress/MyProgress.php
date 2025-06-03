<?php

namespace App\Filament\Pages\Progress;

use Filament\Pages\Page;

class MyProgress extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'I miei progressi';
    protected static ?string $title = 'I miei progressi';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.progress.my-progress';

    public function mount(): void
    {
        // Carica statistiche utente
    }
}