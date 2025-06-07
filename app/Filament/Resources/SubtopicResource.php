<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubtopicResource\Pages;
use App\Models\Subtopic;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubtopicResource extends Resource
{
    protected static ?string $model = Subtopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    
    protected static ?string $navigationGroup = 'Gestione Contenuti';
    
    protected static ?string $navigationLabel = 'Sottoargomenti';
    
    protected static ?string $modelLabel = 'Sottoargomento';
    
    protected static ?string $pluralModelLabel = 'Sottoargomenti';
    
    protected static ?int $navigationSort = 3;
    
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('topic_id')
                            ->label('Argomento')
                            ->options(Topic::query()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                            
                        Forms\Components\TextInput::make('code')
                            ->label('Codice')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('es: 1.1, 1.2, 2.1...')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->where('topic_id', $get('topic_id'));
                            }),
                            
                        Forms\Components\TextInput::make('title')
                            ->label('Titolo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('es: Strada, Carreggiata...'),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descrizione')
                            ->rows(3)
                            ->columnSpanFull(),
                            
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
                    ->width('80px'),
                    
                Tables\Columns\TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Subtopic $record): string => $record->description ?? ''),
                    
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Argomento')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('theoryContents_count')
                    ->label('Contenuti')
                    ->counts('theoryContents')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Attivo'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('topic_id')
                    ->label('Argomento')
                    ->options(Topic::query()->pluck('name', 'id'))
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
                    ->url(fn (Subtopic $record): string => route('filament.quizpatente.resources.theory-contents.index', ['subtopic' => $record->id])),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Elimina sottoargomento')
                    ->modalDescription('Sei sicuro di voler eliminare questo sottoargomento? Verranno eliminati anche tutti i contenuti associati.')
                    ->modalSubmitActionLabel('Sì, elimina'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Elimina sottoargomenti selezionati')
                        ->modalDescription('Sei sicuro di voler eliminare i sottoargomenti selezionati?')
                        ->modalSubmitActionLabel('Sì, elimina'),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->modifyQueryUsing(fn (Builder $query) => $query->when(
                request()->has('topic'),
                fn ($q) => $q->where('topic_id', request('topic'))
            ));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubtopics::route('/'),
            'create' => Pages\CreateSubtopic::route('/create'),
            'edit' => Pages\EditSubtopic::route('/{record}/edit'),
        ];
    }
}