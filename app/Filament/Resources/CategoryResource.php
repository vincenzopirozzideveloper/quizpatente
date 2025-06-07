<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    
    protected static ?string $navigationGroup = 'Gestione Contenuti';
    
    protected static ?string $navigationLabel = 'Categorie';
    
    protected static ?string $modelLabel = 'Categoria';
    
    protected static ?string $pluralModelLabel = 'Categorie';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('slug', Str::slug($state));
                            }),
                            
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                            
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
                    
                Tables\Columns\IconColumn::make('icon')
                    ->label('Icona')
                    ->icon(fn (string $state): string => $state ?? 'heroicon-o-folder')
                    ->width('60px'),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('topics_count')
                    ->label('Argomenti')
                    ->counts('topics')
                    ->badge()
                    ->color('primary'),
                    
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Elimina categoria')
                    ->modalDescription('Sei sicuro di voler eliminare questa categoria? Questa azione non può essere annullata.')
                    ->modalSubmitActionLabel('Sì, elimina'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Elimina categorie selezionate')
                        ->modalDescription('Sei sicuro di voler eliminare le categorie selezionate? Questa azione non può essere annullata.')
                        ->modalSubmitActionLabel('Sì, elimina'),
                ]),
            ])
            ->defaultSort('order')
            ->reorderable('order');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}