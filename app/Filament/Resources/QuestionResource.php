<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\TheoryContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    
    protected static ?string $navigationGroup = 'Gestione Contenuti';
    
    protected static ?string $navigationLabel = 'Domande Quiz';
    
    protected static ?string $modelLabel = 'Domanda';
    
    protected static ?string $pluralModelLabel = 'Domande';
    
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Collegamento Teoria')
                    ->description('Collega la domanda al contenuto teorico corrispondente')
                    ->schema([
                        Forms\Components\Select::make('topic_id')
                            ->label('Argomento')
                            ->options(Topic::query()->ordered()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('subtopic_id', null);
                                $set('theory_content_id', null);
                            }),
                            
                        Forms\Components\Select::make('subtopic_id')
                            ->label('Sottoargomento')
                            ->options(function (Forms\Get $get) {
                                return Subtopic::query()
                                    ->where('topic_id', $get('topic_id'))
                                    ->ordered()
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('theory_content_id', null))
                            ->disabled(fn (Forms\Get $get): bool => !$get('topic_id'))
                            ->helperText('Seleziona prima un argomento'),
                            
                        Forms\Components\Select::make('theory_content_id')
                            ->label('Contenuto Teorico')
                            ->options(function (Forms\Get $get) {
                                return TheoryContent::query()
                                    ->where('subtopic_id', $get('subtopic_id'))
                                    ->ordered()
                                    ->get()
                                    ->mapWithKeys(function ($content) {
                                        return [$content->id => $content->code . ' - ' . Str::limit(strip_tags($content->content), 50)];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (Forms\Get $get): bool => !$get('subtopic_id'))
                            ->helperText('Seleziona prima un sottoargomento'),
                    ])
                    ->columns(3),
                    
                Forms\Components\Section::make('Contenuto Domanda')
                    ->schema([
                        Forms\Components\Textarea::make('text')
                            ->label('Testo della domanda')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Scrivi la domanda in modo chiaro e non ambiguo'),
                            
                        Forms\Components\Radio::make('correct_answer')
                            ->label('Risposta corretta')
                            ->options([
                                '1' => 'Vero',
                                '0' => 'Falso',
                            ])
                            ->required()
                            ->inline()
                            ->helperText('Indica se l\'affermazione è vera o falsa'),
                            
                        Forms\Components\Textarea::make('explanation')
                            ->label('Spiegazione')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Spiega perché la risposta è vera o falsa. Questa spiegazione verrà mostrata dopo la risposta.'),
                            
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Immagine (opzionale)')
                            ->image()
                            ->directory('question-images')
                            ->maxSize(2048)
                            ->imageResizeMode('contain')
                            ->imageCropAspectRatio(null)
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('600')
                            ->helperText('Carica un\'immagine se necessaria per la domanda (max 2MB)'),
                    ]),
                    
                Forms\Components\Section::make('Proprietà')
                    ->schema([
                        Forms\Components\Select::make('difficulty_level')
                            ->label('Difficoltà')
                            ->options([
                                1 => 'Facile',
                                2 => 'Medio',
                                3 => 'Difficile',
                            ])
                            ->default(1)
                            ->required(),
                            
                        Forms\Components\TextInput::make('ministerial_number')
                            ->label('Numero Ministeriale')
                            ->placeholder('es: D001, D002...')
                            ->maxLength(20)
                            ->helperText('Codice identificativo ministeriale (se applicabile)'),
                            
                        Forms\Components\Toggle::make('is_ministerial')
                            ->label('Domanda Ministeriale')
                            ->default(true)
                            ->helperText('Indica se la domanda fa parte del database ministeriale'),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Attiva')
                            ->default(true)
                            ->helperText('Solo le domande attive verranno incluse nei quiz'),
                            
                        Forms\Components\TextInput::make('order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ministerial_number')
                    ->label('N° Min.')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('topic.name')
                    ->label('Argomento')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('subtopic.title')
                    ->label('Sottoargomento')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('theoryContent.code')
                    ->label('Teoria')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('text')
                    ->label('Domanda')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Question $record): string {
                        return $record->text;
                    }),
                    
                Tables\Columns\IconColumn::make('correct_answer')
                    ->label('Risposta')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Img')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.jpg'))
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Difficoltà')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match($state) {
                        1 => 'Facile',
                        2 => 'Medio',
                        3 => 'Difficile',
                        default => 'N/D'
                    })
                    ->color(fn (int $state): string => match($state) {
                        1 => 'success',
                        2 => 'warning',
                        3 => 'danger',
                        default => 'gray'
                    }),
                    
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Attiva'),
                    
                Tables\Columns\TextColumn::make('userErrors_count')
                    ->label('Errori')
                    ->counts('userErrors')
                    ->badge()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('topic_id')
                    ->label('Argomento')
                    ->options(Topic::query()->ordered()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('subtopic_id')
                    ->label('Sottoargomento')
                    ->options(function () {
                        return Subtopic::query()
                            ->with('topic')
                            ->ordered()
                            ->get()
                            ->mapWithKeys(function ($subtopic) {
                                return [$subtopic->id => $subtopic->topic->name . ' - ' . $subtopic->title];
                            });
                    })
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Difficoltà')
                    ->options([
                        1 => 'Facile',
                        2 => 'Medio',
                        3 => 'Difficile',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_ministerial')
                    ->label('Ministeriale')
                    ->placeholder('Tutte')
                    ->trueLabel('Solo ministeriali')
                    ->falseLabel('Non ministeriali'),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Stato')
                    ->placeholder('Tutte')
                    ->trueLabel('Attive')
                    ->falseLabel('Non attive'),
                    
                Tables\Filters\TernaryFilter::make('has_image')
                    ->label('Immagine')
                    ->placeholder('Tutte')
                    ->trueLabel('Con immagine')
                    ->falseLabel('Senza immagine')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('image_url'),
                        false: fn (Builder $query) => $query->whereNull('image_url'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Anteprima')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Anteprima Domanda')
                    ->modalContent(fn (Question $record): HtmlString => new HtmlString(
                        view('filament.resources.question-preview', [
                            'question' => $record
                        ])->render()
                    ))
                    ->modalWidth('2xl'),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplica')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Duplica domanda')
                    ->modalDescription('Vuoi duplicare questa domanda? Verrà creata una copia esatta.')
                    ->action(function (Question $record) {
                        $newQuestion = $record->replicate();
                        $newQuestion->ministerial_number = null;
                        $newQuestion->created_at = now();
                        $newQuestion->save();
                        
                        Notification::make()
                            ->title('Domanda duplicata')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Attiva selezionate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Disattiva selezionate')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['text', 'ministerial_number', 'explanation'];
    }
}