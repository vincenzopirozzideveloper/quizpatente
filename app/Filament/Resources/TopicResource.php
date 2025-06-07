<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Filament\Resources\TopicResource\RelationManagers;
use App\Models\Topic;
use App\Models\Category;
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
                        Forms\Components\Select::make('category_id')
                            ->label('Categoria')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                            
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
                            ])
                            ->searchable(),
                            
                        Forms\Components\TextInput::make('total_questions')
                            ->label('Totale domande')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->disabled(),
                            
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
                    ->description(fn (Topic $record): string => $record->description ?? ''),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('subtopics_count')
                    ->label('Sottoargomenti')
                    ->counts('subtopics')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('total_questions')
                    ->label('Domande')
                    ->badge()
                    ->color('warning'),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Attivo'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Stato')
                    ->placeholder('Tutti')
                    ->trueLabel('Attivi')
                    ->falseLabel('Non attivi'),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_content')
                    ->label('Gestisci contenuti')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn (Topic $record): string => route('filament.quizpatente.resources.subtopics.index', ['topic' => $record->id])),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Elimina argomento')
                    ->modalDescription('Sei sicuro di voler eliminare questo argomento? Verranno eliminati anche tutti i sottoargomenti e contenuti associati.')
                    ->modalSubmitActionLabel('Sì, elimina'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            RelationManagers\SubtopicsRelationManager::class,
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