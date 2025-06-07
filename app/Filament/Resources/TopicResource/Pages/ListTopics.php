<?php
// File: app/Filament/Resources/TopicResource/Pages/ListTopics.php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Resources\TopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopics extends ListRecords
{
    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuovo Argomento'),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            TopicResource\Widgets\TopicStatsOverview::class,
        ];
    }
}