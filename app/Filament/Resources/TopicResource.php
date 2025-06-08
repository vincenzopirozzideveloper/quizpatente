<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Gestione Contenuti';
    
    protected static ?string $navigationLabel = 'Argomenti';
    
    protected static ?string $modelLabel = 'Argomento';
    
    protected static ?string $pluralModelLabel = 'Argomenti';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([                            
                        Forms\Components\TextInput::make('code')
                            ->label('Codice')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->placeholder('es: 1, 2, 3...'),
                            
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('es: Definizioni generali e doveri nell\'uso della strada'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descrizione')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('icon')
                            ->label('Icona')
                            ->options([
                                'heroicon-o-academic-cap' => 'Libro/Studio',
                                'heroicon-o-exclamation-triangle' => 'Pericolo',
                                'heroicon-o-no-symbol' => 'Divieto',
                                'heroicon-o-check-circle' => 'Obbligo',
                                'heroicon-o-arrow-right-circle' => 'Indicazione',
                                'heroicon-o-information-circle' => 'Informazione',
                                'heroicon-o-light-bulb' => 'Segnali luminosi',
                                'heroicon-o-user-group' => 'Agenti del traffico',
                                'heroicon-o-truck' => 'Veicoli',
                                'heroicon-o-shield-check' => 'Sicurezza',
                                'heroicon-o-scale' => 'Norme legali',
                                'heroicon-o-globe-alt' => 'Ambiente',
                                'heroicon-o-cog-6-tooth' => 'Manutenzione',
                            ])
                            ->searchable()
                            ->default('heroicon-o-book-open'),
                            
                        Forms\Components\TextInput::make('total_questions')
                            ->label('Totale domande')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->disabled()
                            ->helperText('Calcolato automaticamente in base alle domande associate'),
                            
                        Forms\Components\TextInput::make('order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Attivo')
                            ->default(true),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordine')
                    ->sortable()
                    ->toggleable()
                    ->width('80px'),
                    
                Tables\Columns\TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\IconColumn::make('icon')
                    ->label('Icona')
                    ->icon(fn (string $state): string => $state ?? 'heroicon-o-folder')
                    ->width('60px'),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Topic $record): string => $record->description ?? '')
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('theoryContents_count')
                    ->label('Contenuti')
                    ->counts(['theoryContents' => function ($query) {
                        $query->published();
                    }])
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('total_questions')
                    ->label('Domande')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state ?: '0'),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Attivo'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Stato')
                    ->placeholder('Tutti')
                    ->trueLabel('Attivi')
                    ->falseLabel('Non attivi'),
                    
                Tables\Filters\TernaryFilter::make('has_content')
                    ->label('Contenuti')
                    ->placeholder('Tutti')
                    ->trueLabel('Con contenuti')
                    ->falseLabel('Senza contenuti')
                    ->queries(
                        true: fn ($query) => $query->has('theoryContents'),
                        false: fn ($query) => $query->doesntHave('theoryContents'),
                    ),
                    
                Tables\Filters\TernaryFilter::make('has_questions')
                    ->label('Domande')
                    ->placeholder('Tutti')
                    ->trueLabel('Con domande')
                    ->falseLabel('Senza domande')
                    ->queries(
                        true: fn ($query) => $query->where('total_questions', '>', 0),
                        false: fn ($query) => $query->where('total_questions', 0),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_content')
                    ->label('Gestisci contenuti')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn (Topic $record): string => TheoryContentResource::getUrl('index', ['topic' => $record->id])),
                    
                Tables\Actions\Action::make('view_questions')
                    ->label('Vedi domande')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('info')
                    ->url(fn (Topic $record): string => QuestionResource::getUrl('index', ['tableFilters' => ['topic_id' => ['value' => $record->id]]])),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Elimina argomento')
                    ->modalDescription('Sei sicuro di voler eliminare questo argomento? Verranno eliminati anche tutti i contenuti teorici e le domande associate.')
                    ->modalSubmitActionLabel('Sì, elimina'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Attiva selezionati')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Disattiva selezionati')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Elimina argomenti selezionati')
                        ->modalDescription('Sei sicuro di voler eliminare gli argomenti selezionati? Questa azione non può essere annullata.')
                        ->modalSubmitActionLabel('Sì, elimina'),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order');
    }

    public static function getRelations(): array
    {
        return [
            // Relazioni rimosse
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),
            'edit' => Pages\EditTopic::route('/{record}/edit'),
        ];
    }
}