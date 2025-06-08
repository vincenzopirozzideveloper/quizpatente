{{-- resources/views/filament/pages/quiz/quiz-results.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Header Risultati --}}
        <div class="text-center">
            @if($statistics['passed'])
                <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 dark:bg-green-900/20 rounded-full mb-6">
                    <x-heroicon-o-check-circle class="w-16 h-16 text-green-600 dark:text-green-400" />
                </div>
                <h1 class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">Quiz Superato!</h1>
            @else
                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 dark:bg-red-900/20 rounded-full mb-6">
                    <x-heroicon-o-x-circle class="w-16 h-16 text-red-600 dark:text-red-400" />
                </div>
                <h1 class="text-3xl font-bold text-red-600 dark:text-red-400 mb-2">Quiz Non Superato</h1>
            @endif
            
            <p class="text-lg text-gray-600 dark:text-gray-400">
                {{ $this->getCompletionMessage() }}
            </p>
        </div>

        {{-- Statistiche Principali --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $statistics['score'] }}%
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Punteggio</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">
                    {{ $statistics['correct'] }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Risposte Corrette</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-red-600 dark:text-red-400 mb-2">
                    {{ $statistics['wrong'] }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Risposte Errate</p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
                <div class="text-4xl font-bold text-gray-600 dark:text-gray-400 mb-2">
                    {{ $statistics['time_spent'] ?? '--:--' }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Tempo Impiegato</p>
            </div>
        </div>

        {{-- Grafico Risultati --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Riepilogo Risposte</h3>
            
            <div class="relative h-8 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="absolute top-0 left-0 h-full bg-green-500 transition-all duration-500" 
                     style="width: {{ $statistics['correct_percentage'] }}%"></div>
                     
                <div class="absolute top-0 h-full bg-red-500 transition-all duration-500" 
                     style="left: {{ $statistics['correct_percentage'] }}%; width: {{ $statistics['wrong_percentage'] }}%"></div>
                     
                @if($statistics['unanswered_percentage'] > 0)
                    <div class="absolute top-0 h-full bg-gray-400 transition-all duration-500" 
                         style="left: {{ $statistics['correct_percentage'] + $statistics['wrong_percentage'] }}%; width: {{ $statistics['unanswered_percentage'] }}%"></div>
                @endif
            </div>
            
            <div class="flex items-center justify-center space-x-8 mt-4">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Corrette ({{ number_format($statistics['correct_percentage'], 1) }}%)
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Errate ({{ number_format($statistics['wrong_percentage'], 1) }}%)
                    </span>
                </div>
                @if($statistics['unanswered_percentage'] > 0)
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-gray-400 rounded"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Non risposte ({{ number_format($statistics['unanswered_percentage'], 1) }}%)
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Errori per Argomento --}}
        @if(count($errorsByTopic) > 0)  {{-- CAMBIATO DA $errorsByTopic->count() --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Errori per Argomento</h3>
                
                <div class="space-y-4">
                    @foreach($errorsByTopic as $topicData)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    @if($topicData['icon'])
                                        <x-dynamic-component 
                                            :component="$topicData['icon']" 
                                            class="w-6 h-6 text-gray-600 dark:text-gray-400" 
                                        />
                                    @endif
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        {{ $topicData['name'] }}
                                    </h4>
                                </div>
                                <span class="px-3 py-1 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-full text-sm font-medium">
                                    {{ $topicData['errors'] }} {{ $topicData['errors'] === 1 ? 'errore' : 'errori' }}
                                </span>
                            </div>
                            
                            <div class="space-y-2">
                                @foreach($topicData['questions'] as $question)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                            {{ $question['text'] }}
                                        </p>
                                        <div class="flex items-center space-x-4 mt-2 text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Tua risposta: 
                                                <span class="font-medium text-red-600 dark:text-red-400">
                                                    {{ $question['user_answer'] ? 'Vero' : 'Falso' }}
                                                </span>
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Corretta: 
                                                <span class="font-medium text-green-600 dark:text-green-400">
                                                    {{ $question['correct_answer'] ? 'Vero' : 'Falso' }}
                                                </span>
                                            </span>
                                        </div>
                                        
                                        @if($question['explanation'])
                                            <button onclick="showExplanation('{{ $question['id'] }}')" 
                                                    class="mt-2 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                Mostra spiegazione
                                            </button>
                                            
                                            <div id="explanation-{{ $question['id'] }}" class="hidden mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs text-blue-800 dark:text-blue-200">
                                                {{ $question['explanation'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Info Quiz --}}
        @if($quizSession->type === 'ministerial' && $quizSession->ministerialQuiz)
            <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-6">
                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Dettagli Quiz</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Tipo:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $this->getQuizTypeLabel() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Nome:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $quizSession->ministerialQuiz->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Errori massimi:</span>
                        <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $statistics['max_errors'] }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Azioni --}}
        <div class="flex items-center justify-center space-x-4">
            <x-filament::button color="gray" size="lg" wire:click="backToSelection">
                <x-heroicon-m-arrow-left class="w-5 h-5 mr-2" />
                Torna alla Selezione
            </x-filament::button>
            
            <x-filament::button size="lg" wire:click="retryQuiz">
                <x-heroicon-m-arrow-path class="w-5 h-5 mr-2" />
                Riprova Quiz
            </x-filament::button>
        </div>
    </div>

    <script>
        function showExplanation(questionId) {
            const explanationDiv = document.getElementById('explanation-' + questionId);
            if (explanationDiv) {
                explanationDiv.classList.toggle('hidden');
            }
        }
    </script>
</x-filament-panels::page>