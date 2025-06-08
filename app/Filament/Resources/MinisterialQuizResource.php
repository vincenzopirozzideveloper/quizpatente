<?php
// app/Filament/Resources/MinisterialQuizResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\MinisterialQuizResource\Pages;
use App\Models\MinisterialQuiz;
use App\Models\Question;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class MinisterialQuizResource extends Resource
{
    protected static ?string $model = MinisterialQuiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Gestione Contenuti';

    protected static ?string $navigationLabel = 'Quiz Ministeriali';

    protected static ?string $modelLabel = 'Quiz Ministeriale';

    protected static ?string $pluralModelLabel = 'Quiz Ministeriali';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Quiz')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Quiz')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('es: Quiz Ministeriale #1'),
    
                        Forms\Components\Textarea::make('description')
                            ->label('Descrizione')
                            ->rows(3)
                            ->columnSpanFull(),
    
                        Forms\Components\TextInput::make('order')
                            ->label('Ordine')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
    
                        Forms\Components\TextInput::make('max_errors')
                            ->label('Errori massimi consentiti')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText('Numero massimo di errori per superare il quiz'),
    
                        Forms\Components\Toggle::make('is_active')
                            ->label('Attivo')
                            ->default(true)
                            ->helperText('Solo i quiz attivi sono disponibili agli utenti'),
                    ])
                    ->columns(2),
    
                Forms\Components\Section::make('Selezione Domande')
                    ->description('Seleziona esattamente 30 domande per questo quiz')
                    ->schema([
                        Forms\Components\Placeholder::make('questions_count')
                            ->label('Domande selezionate')
                            ->content(function ($get, $record) {
                                $selectedQuestions = $get('questions') ?? [];
                                $count = is_array($selectedQuestions) ? count($selectedQuestions) : 0;
                                
                                if (!$record) {
                                    $count = is_array($selectedQuestions) ? count($selectedQuestions) : 0;
                                } else {
                                    $count = $record->questions()->count();
                                }
                                
                                $color = $count === 30 ? 'text-green-600' : 'text-red-600';
                                return new \Illuminate\Support\HtmlString(
                                    "<span class='{$color} font-bold text-lg'>{$count} / 30</span>"
                                );
                            }),
    
                        Forms\Components\CheckboxList::make('questions')
                            ->label('Domande disponibili')
                            ->relationship('questions', 'text')
                            ->options(function () {
                                return Question::active()
                                    ->with('topic')
                                    ->get()
                                    ->mapWithKeys(function ($question) {
                                        $label = sprintf(
                                            '[%s] %s',
                                            $question->topic->code,
                                            \Str::limit($question->text, 100)
                                        );
                                        return [$question->id => $label];
                                    });
                            })
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->gridDirection('row')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Forza l'aggiornamento del conteggio
                                $set('questions_count_update', now());
                            })
                            ->rules([
                                'array',
                                'size:30',
                            ])
                            ->validationMessages([
                                'size' => 'Devi selezionare esattamente 30 domande.',
                            ])
                            ->helperText('Usa la ricerca per filtrare le domande. Devi selezionare esattamente 30 domande.')
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

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrizione')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Domande')
                    ->counts('questions')
                    ->badge()
                    ->color(
                        fn(Model $record): string =>
                        $record->questions_count === 30 ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('max_errors')
                    ->label('Errori max')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('Volte giocato')
                    ->counts('sessions')
                    ->badge()
                    ->color('info'),

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

                Tables\Filters\Filter::make('is_valid')
                    ->label('Validità')
                    ->query(fn($query) => $query->has('questions', '=', 30))
                    ->toggle()
                    ->label('Solo quiz validi (30 domande)'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Anteprima')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Anteprima Quiz')
                    ->modalContent(function (MinisterialQuiz $record) {
                        return view('filament.resources.ministerial-quiz-preview', [
                            'quiz' => $record
                        ]);
                    })
                    ->modalWidth('5xl'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplica')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (MinisterialQuiz $record) {
                        $newQuiz = $record->replicate();
                        $newQuiz->name = $record->name . ' (Copia)';
                        $newQuiz->save();

                        // Copia anche le domande
                        $questions = $record->questions()->get();
                        foreach ($questions as $question) {
                            $newQuiz->questions()->attach($question->id, [
                                'order' => $question->pivot->order
                            ]);
                        }

                        Notification::make()
                            ->title('Quiz duplicato con successo')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Attiva selezionati')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Disattiva selezionati')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMinisterialQuizzes::route('/'),
            'create' => Pages\CreateMinisterialQuiz::route('/create'),
            'edit' => Pages\EditMinisterialQuiz::route('/{record}/edit'),
        ];
    }
}