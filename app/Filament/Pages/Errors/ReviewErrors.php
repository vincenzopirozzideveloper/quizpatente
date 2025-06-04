<?php

namespace App\Filament\Pages\Errors;

use Filament\Pages\Page;
use App\Models\UserError;

class ReviewErrors extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Ripassa errori';
    protected static ?string $title = 'Ripassa errori';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.errors.review-errors';

    public static function getNavigationBadge(): ?string
    {
        return auth()->check() 
            ? auth()->user()->errors()->notMastered()->count() ?: null
            : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}