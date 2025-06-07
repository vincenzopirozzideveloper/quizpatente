<?php

namespace App\Filament\Resources\TopicResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubtopicsRelationManager extends RelationManager
{
    protected static string $relationship = 'subtopics';
    
    protected static ?string $title = 'Sottoargomenti';
    
    protected static ?string $modelLabel = 'Sottoargomento';
    
    protected static ?string $pluralModelLabel = 'Sottoargomenti';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Codice')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('es: 1.1, 1.2, 2.1...')
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('topic_id', $this->ownerRecord->id);
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
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
                    ->description(fn ($record): string => $record->description ?? ''),
                    
                Tables\Columns\TextColumn::make('theoryContents_count')
                    ->label('Contenuti')
                    ->counts('theoryContents')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Attivo'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Stato')
                    ->placeholder('Tutti')
                    ->trueLabel('Attivi')
                    ->falseLabel('Non attivi'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuovo sottoargomento')
                    ->modalHeading('Crea nuovo sottoargomento'),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_content')
                    ->label('Gestisci contenuti')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn ($record): string => route('filament.quizpatente.resources.theory-contents.index', ['subtopic' => $record->id])),
                    
                Tables\Actions\EditAction::make()
                    ->modalHeading('Modifica sottoargomento'),
                    
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
            ->reorderable('order');
    }
}