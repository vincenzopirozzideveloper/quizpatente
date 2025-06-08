<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TheoryContentResource\Pages;
use App\Models\TheoryContent;
use App\Models\UserTheoryProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\FontWeight;

class TheoryContentResource extends Resource
{
    protected static ?string $model = TheoryContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Area Studio';

    protected static ?string $navigationLabel = 'Teoria';

    protected static ?string $modelLabel = 'Contenuto Teorico';

    protected static ?string $pluralModelLabel = 'Contenuti Teorici';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                TheoryContent::query()
                    ->published()
                    ->with(['topic', 'currentUserProgress'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Argomento')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Codice')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->description(
                        fn(TheoryContent $record): string =>
                        str($record->content)->limit(100)->toString()
                    ),

                Tables\Columns\TextColumn::make('progress_status')
                    ->label('Stato')
                    ->badge()
                    ->getStateUsing(function (TheoryContent $record): string {
                        return $record->currentUserProgress?->status ?? 'unread';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'read' => 'Completato',
                        'reading' => 'In lettura',
                        default => 'Non letto',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'read' => 'success',
                        'reading' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'read' => 'heroicon-o-check-circle',
                        'reading' => 'heroicon-o-clock',
                        default => 'heroicon-o-book-open',
                    }),

                Tables\Columns\IconColumn::make('has_image')
                    ->label('Immagine')
                    ->getStateUsing(
                        fn(TheoryContent $record): bool =>
                        $record->image_url !== null
                    )
                    ->boolean()
                    ->trueIcon('heroicon-o-photo')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('info')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('currentUserProgress.completed_at')
                    ->label('Completato il')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('topic')
                    ->relationship('topic', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Argomento'),

                Tables\Filters\TernaryFilter::make('progress')
                    ->label('Stato lettura')
                    ->placeholder('Tutti')
                    ->trueLabel('Completati')
                    ->falseLabel('Non completati')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas(
                            'currentUserProgress',
                            fn($q) => $q->where('status', 'read')
                        ),
                        false: fn(Builder $query) => $query->whereDoesntHave(
                            'currentUserProgress',
                            fn($q) => $q->where('status', 'read')
                        ),
                    ),

                Tables\Filters\TernaryFilter::make('has_image')
                    ->label('Con immagine')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('image_url'),
                        false: fn(Builder $query) => $query->whereNull('image_url'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Leggi')
                    ->icon('heroicon-m-book-open')
                    ->after(function (TheoryContent $record) {
                        // Marca come "in lettura" quando viene aperto
                        if (!$record->currentUserProgress || $record->currentUserProgress->status === 'unread') {
                            UserTheoryProgress::markAsReading(auth()->id(), $record->id);
                        }
                    }),
                    
                Tables\Actions\Action::make('toggleComplete')
                    ->label(fn (TheoryContent $record): string => 
                        $record->currentUserProgress?->status === 'read' 
                            ? 'Segna come non letto' 
                            : 'Segna come completato'
                    )
                    ->icon(fn (TheoryContent $record): string => 
                        $record->currentUserProgress?->status === 'read' 
                            ? 'heroicon-o-x-circle' 
                            : 'heroicon-o-check-circle'
                    )
                    ->color(fn (TheoryContent $record): string => 
                        $record->currentUserProgress?->status === 'read' 
                            ? 'gray' 
                            : 'success'
                    )
                    ->requiresConfirmation(false)
                    ->action(function (TheoryContent $record, $livewire) {
                        UserTheoryProgress::toggleReadStatus(auth()->id(), $record->id);
                        
                        // Emetti evento per aggiornare il widget
                        $livewire->dispatch('theory-progress-updated');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markAsRead')
                    ->label('Segna come completati')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records, $livewire) {
                        foreach ($records as $record) {
                            UserTheoryProgress::markAsRead(auth()->id(), $record->id);
                        }
                        
                        // Emetti evento per aggiornare il widget
                        $livewire->dispatch('theory-progress-updated');
                    })
                    ->deselectRecordsAfterCompletion(),
                    
                Tables\Actions\BulkAction::make('markAsUnread')
                    ->label('Segna come non letti')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function ($records, $livewire) {
                        foreach ($records as $record) {
                            UserTheoryProgress::where('user_id', auth()->id())
                                ->where('theory_content_id', $record->id)
                                ->update(['status' => 'unread', 'completed_at' => null]);
                        }
                        
                        // Emetti evento per aggiornare il widget
                        $livewire->dispatch('theory-progress-updated');
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('topic_id')
            ->defaultGroup('topic.name')
            ->groups([
                Tables\Grouping\Group::make('topic.name')
                    ->label('Argomento')
                    ->getTitleFromRecordUsing(
                        fn(TheoryContent $record): string =>
                        "{$record->topic->code} - {$record->topic->name}"
                    )
                    ->collapsible(),
            ])
            ->striped()
            ->poll('60s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->heading('Informazioni')
                    ->description(
                        fn(TheoryContent $record): string =>
                        "Argomento: {$record->topic->name}"
                    )
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('code')
                                    ->label('Codice')
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('progress_status')
                                    ->label('Stato lettura')
                                    ->badge()
                                    ->getStateUsing(function (TheoryContent $record): string {
                                        return $record->currentUserProgress?->status ?? 'unread';
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'read' => 'Completato',
                                        'reading' => 'In lettura',
                                        default => 'Non letto',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'read' => 'success',
                                        'reading' => 'warning',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('currentUserProgress.completed_at')
                                    ->label('Completato il')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Non ancora completato'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Contenuto')
                    ->heading(fn(TheoryContent $record): string => $record->title)
                    ->schema([
                        // Immagine prima del contenuto (se presente e posizionata prima)
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('')
                            ->visible(
                                fn(TheoryContent $record): bool =>
                                $record->image_url !== null && $record->image_position === 'before'
                            )
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('image_caption')
                            ->label('')
                            ->visible(
                                fn(TheoryContent $record): bool =>
                                $record->image_url !== null &&
                                $record->image_position === 'before' &&
                                $record->image_caption !== null
                            )
                            ->columnSpanFull()
                            ->alignCenter()
                            ->color('gray')
                            ->size('sm'),

                        // Contenuto principale
                        Infolists\Components\TextEntry::make('content')
                            ->label('')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull(),

                        // Immagine dopo il contenuto (se presente e posizionata dopo)
                        Infolists\Components\ImageEntry::make('image_url')
                            ->label('')
                            ->visible(
                                fn(TheoryContent $record): bool =>
                                $record->image_url !== null && $record->image_position === 'after'
                            )
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('image_caption')
                            ->label('')
                            ->visible(
                                fn(TheoryContent $record): bool =>
                                $record->image_url !== null &&
                                $record->image_position === 'after' &&
                                $record->image_caption !== null
                            )
                            ->columnSpanFull()
                            ->alignCenter()
                            ->color('gray')
                            ->size('sm'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTheoryContents::route('/'),
            'view' => Pages\ViewTheoryContent::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $total = static::getModel()::published()->count();
        $completed = static::getModel()::published()
            ->whereHas('currentUserProgress', function ($query) {
                $query->where('status', 'read');
            })->count();

        return $completed . '/' . $total;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}