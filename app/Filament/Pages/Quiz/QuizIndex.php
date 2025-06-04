<?php

namespace App\Filament\Pages\Quiz;

use Filament\Pages\Page;

class QuizIndex extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Quiz';
    protected static ?string $title = 'Quiz';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Area Studio';
    protected static string $view = 'filament.pages.quiz.quiz-index';

    protected function getHeaderActions(): array
    {
        return [];
    }
}