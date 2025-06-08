<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Header Risultati --}}
        <div class="text-center">
            @if($statistics['passed'])
                <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 dark:bg-green-900/20 rounded-full mb-6">
                    <x-heroicon-o-check-circle class="w-16 h-16 text-green-600 dark:text-green-400" />
                </div>
                <h1 class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">Quiz Superato!</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Complimenti! Hai superato il quiz con successo.
                </p>
            @else
                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 dark:bg-red-900/20 rounded-full mb-6">
                    <x-heroicon-o-x-circle class="w-16 h-16 text-red-600 dark:text-red-400" />
                </div>
                <h1 class="text-3xl font-bold text-red-600 dark:text-red-400 mb-2">Quiz Non Superato</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Non ti preoccupare, continua a studiare e riprova!
                </p>
            @endif
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
                @php
                    $correctPercentage = ($statistics['correct'] / $statistics['total_questions']) * 100;
                    $wrongPercentage = ($statistics['wrong'] / $statistics['total_questions']) * 100;
                    $unansweredPercentage = ($statistics['unanswered'] / $statistics['total_questions']) * 100;
                @endphp
                
                <div class="absolute top-0 left-0 h-full bg-green-500 transition-all duration-500" 
                     style="width: {{ $correctPercentage }}%"></div>
                     
                <div class="absolute top-0 h-full bg-red-500 transition-all duration-500" 
                     style="left: {{ $correctPercentage }}%; width: {{ $wrongPercentage }}%"></div>
                     
                @if($unansweredPercentage > 0)
                    <div class="absolute top-0 h-full bg-gray-400 transition-all duration-500" 
                         style="left: {{ $correctPercentage + $wrongPercentage }}%; width: {{ $unansweredPercentage }}%"></div>
                @endif
            </div>
            
            <div class="flex items-center justify-center space-x-8 mt-4">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Corrette ({{ number_format($correctPercentage, 1) }}%)
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Errate ({{ number_format($wrongPercentage, 1) }}%)
                    </span>
                </div>
                @if($unansweredPercentage > 0)
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-gray-400 rounded"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Non risposte ({{ number_format($unansweredPercentage, 1) }}%)
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Errori per Argomento --}}
        @if($errorsByTopic->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Errori per Argomento</h3>
                
                <div class="space-y-4">
                    @foreach($errorsByTopic as $topicId => $topicData)
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
                                    <button wire:click="showQuestionDetail({{ $question['id'] }})"
                                            class="w-full text-left p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
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
                                    </button>
                                    
                                    {{-- Modal Dettaglio Domanda --}}
                                    <x-filament::modal id="question-detail-{{ $question['id'] }}" width="3xl">
                                        <x-slot name="heading">
                                            Dettaglio Domanda
                                        </x-slot>
                                        
                                        <div class="space-y-4">
                                            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                <p class="text-gray-900 dark:text-white">
                                                    {{ $question['text'] }}
                                                </p>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                                    <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-1">
                                                        Tua risposta
                                                    </p>
                                                    <p class="text-red-900 dark:text-red-100 font-semibold">
                                                        {{ $question['user_answer'] ? 'VERO' : 'FALSO' }}
                                                    </p>
                                                </div>
                                                
                                                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                                    <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-1">
                                                        Risposta corretta
                                                    </p>
                                                    <p class="text-green-900 dark:text-green-100 font-semibold">
                                                        {{ $question['correct_answer'] ? 'VERO' : 'FALSO' }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            @if($question['explanation'])
                                                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">
                                                        Spiegazione
                                                    </p>
                                                    <p class="text-sm text-blue-900 dark:text-blue-100">
                                                        {{ $question['explanation'] }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </x-filament::modal>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
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
</x-filament-panels::page>