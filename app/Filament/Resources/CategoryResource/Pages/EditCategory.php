<?php
// File: app/Filament/Resources/CategoryResource/Pages/ListCategories.php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Elimina categoria')
                ->modalDescription('Sei sicuro di voler eliminare questa categoria? Questa azione non può essere annullata.')
                ->modalSubmitActionLabel('Sì, elimina'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}