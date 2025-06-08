{{-- resources/views/filament/resources/ministerial-quiz-preview.blade.php --}}
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-primary-50 dark:bg-primary-900/20 rounded-lg p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            {{ $quiz->name }}
        </h3>
        @if($quiz->description)
            <p class="text-gray-700 dark:text-gray-300">{{ $quiz->description }}</p>
        @endif
        
        <div class="flex items-center gap-6 mt-4 text-sm">
            <div class="flex items-center gap-2">
                <x-heroicon-o-question-mark-circle class="w-5 h-5 text-gray-500" />
                <span>{{ $quiz->questions_count }} domande</span>
            </div>
            <div class="flex items-center gap-2">
                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                <span>Max {{ $quiz->max_errors }} errori</span>
            </div>
            <div class="flex items-center gap-2">
                <x-heroicon-o-play class="w-5 h-5 text-green-500" />
                <span>Giocato {{ $quiz->sessions_count }} volte</span>
            </div>
        </div>
    </div>

    {{-- Validità --}}
    @if($quiz->questions_count !== 30)
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                <div>
                    <h4 class="font-medium text-red-900 dark:text-red-100">Quiz non valido</h4>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        Questo quiz ha {{ $quiz->questions_count }} domande invece di 30. 
                        Deve avere esattamente 30 domande per essere utilizzabile.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Lista domande per argomento --}}
    <div>
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Domande per argomento
        </h4>
        
        @php
            $questionsByTopic = $quiz->questions->groupBy('topic_id');
        @endphp
        
        <div class="space-y-4">
            @foreach($questionsByTopic as $topicId => $questions)
                @php
                    $topic = $questions->first()->topic;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">
                        {{ $topic->code }} - {{ $topic->name }}
                        <span class="text-sm text-gray-500 ml-2">({{ $questions->count() }} domande)</span>
                    </h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                        @foreach($questions as $question)
                            <div class="flex items-start gap-2">
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                    #{{ $loop->iteration }}
                                </span>
                                <span class="line-clamp-1">{{ $question->text }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Statistiche utenti --}}
    @if($quiz->sessions_count > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Statistiche utilizzo
            </h4>
            
            @php
                $stats = $quiz->sessions()
                    ->completed()
                    ->selectRaw('
                        COUNT(*) as total,
                        AVG(score) as avg_score,
                        MAX(score) as max_score,
                        MIN(score) as min_score,
                        SUM(is_passed) as passed_count
                    ')
                    ->first();
            @endphp
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats->avg_score, 1) }}%
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Media voti</p>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $stats->max_score }}%
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Voto massimo</p>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ $stats->min_score }}%
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Voto minimo</p>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        {{ round(($stats->passed_count / $stats->total) * 100) }}%
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tasso successo</p>
                </div>
            </div>
        </div>
    @endif
</div>