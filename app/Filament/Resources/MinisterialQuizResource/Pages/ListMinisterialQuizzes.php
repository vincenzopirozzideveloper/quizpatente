<?php
// app/Filament/Resources/MinisterialQuizResource/Pages/ListMinisterialQuizzes.php

namespace App\Filament\Resources\MinisterialQuizResource\Pages;

use App\Filament\Resources\MinisterialQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMinisterialQuizzes extends ListRecords
{
    protected static string $resource = MinisterialQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuovo Quiz Ministeriale'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MinisterialQuizResource\Widgets\MinisterialQuizStatsOverview::class,
        ];
    }
}