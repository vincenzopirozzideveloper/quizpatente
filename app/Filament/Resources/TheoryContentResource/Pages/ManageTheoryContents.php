<?php

namespace App\Filament\Resources\TheoryContentResource\Pages;

use App\Filament\Resources\TheoryContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTheoryContents extends ManageRecords
{
    protected static string $resource = TheoryContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
