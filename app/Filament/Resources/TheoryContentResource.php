<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TheoryContentResource\Pages;
use App\Models\TheoryContent;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class TheoryContentResource extends Resource
{
    protected static ?string $model = TheoryContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Gestione Contenuti';
    
    protected static ?string $navigationLabel = 'Contenuti Teoria';
    
    protected static ?string $modelLabel = 'Contenuto';
    
    protected static ?string $pluralModelLabel = 'Contenuti';
    
    protected static ?int $navigationSort = 4;
    
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Base')
                    ->schema([
                        Forms\Components\Select::make('topic_id')
                            ->label('Argomento')
                            ->options(Topic::query()->ordered()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\TextInput::make('code')
                            ->label('Codice')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('es: 1.1, 1.2, 2.1...')
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->where('topic_id', $get('topic_id'));
                            }),
                            
                        Forms\Components\TextInput::make('title')
                            ->label('Titolo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('es: Strada, Carreggiata, Segnali di pericolo...'),
                            
                        Forms\Components\TextInput::make('order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                            
                        Forms\Components\Toggle::make('is_published')
                            ->label('Pubblicato')
                            ->default(true),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Immagine (Opzionale)')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Immagine')
                            ->image()
                            ->directory('theory-images')
                            ->maxSize(5120) // 5MB
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio(null)
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->helperText('L\'immagine verrà visualizzata prima o dopo il contenuto testuale'),
                            
                        Forms\Components\TextInput::make('image_caption')
                            ->label('Didascalia immagine')
                            ->maxLength(255)
                            ->placeholder('Descrizione dell\'immagine (opzionale)'),
                            
                        Forms\Components\Radio::make('image_position')
                            ->label('Posizione immagine')
                            ->options([
                                'before' => 'Prima del testo',
                                'after' => 'Dopo il testo',
                            ])
                            ->default('before')
                            ->inline()
                            ->visible(fn (Forms\Get $get): bool => !empty($get('image_url'))),
                    ])
                    ->columns(1)
                    ->collapsed()
                    ->collapsible(),
                    
                Forms\Components\Section::make('Contenuto')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Contenuto')
                            ->required()
                            ->toolbarButtons([
                                'heading',
                                'bold',
                                'italic',
                                'strike',
                                'link',
                                'orderedList',
                                'unorderedList',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ]),
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
                    
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Argomento')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Titolo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40),
                    
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Immagine')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.jpg'))
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('content')
                    ->label('Contenuto')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (string $state): string => strip_tags($state)),
                    
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Pubblicato'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('topic_id')
                    ->label('Argomento')
                    ->options(Topic::query()->ordered()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Stato pubblicazione')
                    ->placeholder('Tutti')
                    ->trueLabel('Pubblicati')
                    ->falseLabel('Bozze'),
                    
                Tables\Filters\TernaryFilter::make('has_image')
                    ->label('Immagine')
                    ->placeholder('Tutti')
                    ->trueLabel('Con immagine')
                    ->falseLabel('Senza immagine')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('image_url'),
                        false: fn (Builder $query) => $query->whereNull('image_url'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Visualizza contenuto')
                    ->modalWidth('7xl')
                    ->modalContent(function (TheoryContent $record) {
                        return view('filament.resources.theory-content-preview', [
                            'record' => $record
                        ]);
                    }),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplica')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Duplica contenuto')
                    ->modalDescription('Vuoi duplicare questo contenuto? Verrà creata una copia.')
                    ->action(function (TheoryContent $record) {
                        $newContent = $record->replicate();
                        $newContent->code = $record->code . '-copia';
                        $newContent->created_at = now();
                        $newContent->save();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Contenuto duplicato')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Elimina contenuto')
                    ->modalDescription('Sei sicuro di voler eliminare questo contenuto?')
                    ->modalSubmitActionLabel('Sì, elimina')
                    ->after(function (TheoryContent $record) {
                        // Elimina l'immagine se presente
                        if ($record->image_url && Storage::exists($record->image_url)) {
                            Storage::delete($record->image_url);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Elimina contenuti selezionati')
                        ->modalDescription('Sei sicuro di voler eliminare i contenuti selezionati?')
                        ->modalSubmitActionLabel('Sì, elimina'),
                        
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Pubblica')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Metti in bozza')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('topic_id')
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
            'index' => Pages\ListTheoryContents::route('/'),
            'create' => Pages\CreateTheoryContent::route('/create'),
            'edit' => Pages\EditTheoryContent::route('/{record}/edit'),
        ];
    }
}