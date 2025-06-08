<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Header con statistiche --}}
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-3xl p-8 text-white shadow-2xl">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold mb-2">Centro Quiz</h1>
                <p class="text-primary-100 mb-6">Metti alla prova le tue conoscenze con i nostri quiz</p>
                
                {{-- Mini statistiche --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Quiz Completati</p>
                                <p class="text-2xl font-bold">{{ $quizStats['ministerial']['total'] ?? 0 }}</p>
                            </div>
                            <x-heroicon-o-academic-cap class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Tasso Successo</p>
                                <p class="text-2xl font-bold">{{ $quizStats['ministerial']['avg_score'] ?? 0 }}%</p>
                            </div>
                            <x-heroicon-o-chart-pie class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Errori da Ripassare</p>
                                <p class="text-2xl font-bold">{{ $errorsToReview }}</p>
                            </div>
                            <x-heroicon-o-x-circle class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tipologie di Quiz --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Quiz Ministeriali Predefiniti --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 bg-blue-100 dark:bg-blue-900/20 rounded-2xl">
                            <x-heroicon-o-academic-cap class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                            Ufficiale
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                        Quiz Ministeriali
                    </h3>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Quiz ufficiali con 30 domande selezionate. 
                        Massimo {{ $maxErrors }} errori consentiti per superare il test.
                    </p>
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Progressione</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $ministerialProgress['completed'] }} / {{ $ministerialProgress['total'] }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                                 style="width: {{ $ministerialProgress['completion_percentage'] }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $ministerialProgress['completion_percentage'] }}% completato 
                            ({{ $ministerialProgress['remaining'] }} quiz rimanenti)
                        </p>
                    </div>
                    
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-clock class="w-4 h-4 mr-2 text-gray-400" />
                            30 minuti di tempo
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-x-circle class="w-4 h-4 mr-2 text-gray-400" />
                            Massimo {{ $maxErrors }} errori
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-check-circle class="w-4 h-4 mr-2 text-gray-400" />
                            Valutazione immediata
                        </li>
                    </ul>
                    
                    <button wire:click="showMinisterialQuizList" 
                            class="w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2 group">
                        <span>Scegli Quiz</span>
                        <x-heroicon-m-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </button>
                </div>
            </div>

            {{-- Quiz Ministeriale con Manuale --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 bg-green-100 dark:bg-green-900/20 rounded-2xl">
                            <x-heroicon-o-book-open class="w-8 h-8 text-green-600 dark:text-green-400" />
                        </div>
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-full text-sm font-medium">
                            Studio
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                        Quiz con Manuale
                    </h3>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        30 domande ministeriali con possibilità di consultare la teoria. 
                        Perfetto per studiare e capire gli errori.
                    </p>
                    
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-book-open class="w-4 h-4 mr-2 text-gray-400" />
                            Accesso alla teoria
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-light-bulb class="w-4 h-4 mr-2 text-gray-400" />
                            Spiegazioni dettagliate
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-clock class="w-4 h-4 mr-2 text-gray-400" />
                            Senza limite di tempo
                        </li>
                    </ul>
                    
                    <button wire:click="startMinisterialQuizWithManual" 
                            class="w-full py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2 group">
                        <span>Inizia con Manuale</span>
                        <x-heroicon-m-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </button>
                </div>
            </div>

            {{-- Quiz per Argomento --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 bg-purple-100 dark:bg-purple-900/20 rounded-2xl">
                            <x-heroicon-o-folder-open class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                        </div>
                        <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-full text-sm font-medium">
                            Mirato
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                        Quiz per Argomento
                    </h3>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Concentrati su un argomento specifico. 
                        30 domande selezionate dall'argomento scelto.
                    </p>
                    
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-adjustments-horizontal class="w-4 h-4 mr-2 text-gray-400" />
                            Scegli l'argomento
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-arrow-path class="w-4 h-4 mr-2 text-gray-400" />
                            Domande casuali
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-chart-bar class="w-4 h-4 mr-2 text-gray-400" />
                            Progressi per topic
                        </li>
                    </ul>
                    
                    <button wire:click="showTopicSelection" 
                            class="w-full py-3 px-6 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2 group">
                        <span>Scegli Argomento</span>
                        <x-heroicon-m-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </button>
                </div>
            </div>

            {{-- Ripassa Errori --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div class="p-4 bg-red-100 dark:bg-red-900/20 rounded-2xl">
                            <x-heroicon-o-arrow-path class="w-8 h-8 text-red-600 dark:text-red-400" />
                        </div>
                        @if($errorsToReview > 0)
                            <span class="px-3 py-1 bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-full text-sm font-medium">
                                {{ $errorsToReview }} errori
                            </span>
                        @endif
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                        Ripassa i Tuoi Errori
                    </h3>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Ripassa le domande che hai sbagliato più spesso. 
                        Il modo migliore per imparare dai propri errori.
                    </p>
                    
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-x-circle class="w-4 h-4 mr-2 text-gray-400" />
                            Focus sugli errori
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-arrow-trending-up class="w-4 h-4 mr-2 text-gray-400" />
                            Migliora rapidamente
                        </li>
                        <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-check-badge class="w-4 h-4 mr-2 text-gray-400" />
                            Padroneggia gli argomenti
                        </li>
                    </ul>
                    
                    <button wire:click="startErrorsReviewQuiz" 
                            @if($errorsToReview === 0) disabled @endif
                            class="w-full py-3 px-6 font-medium rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2 group
                                   @if($errorsToReview > 0) 
                                       bg-red-600 hover:bg-red-700 text-white
                                   @else 
                                       bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed
                                   @endif">
                        <span>{{ $errorsToReview > 0 ? 'Ripassa Errori' : 'Nessun Errore' }}</span>
                        @if($errorsToReview > 0)
                            <x-heroicon-m-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        @endif
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistiche Recenti --}}
        @if(($quizStats['ministerial']['total'] ?? 0) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Le Tue Statistiche</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ $quizStats['ministerial']['total'] ?? 0 }}
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Quiz Completati</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-1">
                            {{ $quizStats['ministerial']['passed'] ?? 0 }}
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Quiz Superati</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                            {{ $quizStats['ministerial']['avg_score'] ?? 0 }}%
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Media Punteggio</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-1">
                            {{ $quizStats['ministerial']['best_score'] ?? 0 }}%
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Miglior Punteggio</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Selezione Argomento --}}
    <x-filament::modal id="topic-selection" width="lg">
        <x-slot name="heading">
            Seleziona un Argomento
        </x-slot>

        <form wire:submit.prevent="startTopicQuiz">
            {{ $this->form }}
            
            <div class="mt-6 flex justify-end gap-3">
                <x-filament::button type="button" color="gray" x-on:click="close">
                    Annulla
                </x-filament::button>
                
                <x-filament::button type="submit">
                    Inizia Quiz
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>

    {{-- Modal Lista Quiz Ministeriali --}}
    <x-filament::modal id="ministerial-quiz-list" width="3xl">
        <x-slot name="heading">
            Seleziona un Quiz Ministeriale
        </x-slot>

        <div class="space-y-4 max-h-[60vh] overflow-y-auto">
            @forelse($availableMinisterialQuizzes as $quiz)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
                                {{ $quiz['name'] }}
                            </h4>
                            @if($quiz['description'])
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    {{ $quiz['description'] }}
                                </p>
                            @endif
                            
                            <div class="flex items-center gap-4 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">
                                    Max errori: {{ $quiz['max_errors'] }}
                                </span>
                                
                                @if($quiz['is_completed'])
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-full text-xs">
                                        <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                        Completato
                                    </span>
                                @endif
                                
                                @if($quiz['best_score'])
                                    <span class="text-gray-500 dark:text-gray-400">
                                        Miglior voto: {{ $quiz['best_score'] }}%
                                    </span>
                                @endif
                                
                                @if($quiz['attempts'] > 0)
                                    <span class="text-gray-500 dark:text-gray-400">
                                        Tentativi: {{ $quiz['attempts'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <button wire:click="startMinisterialQuiz({{ $quiz['id'] }})"
                                class="ml-4 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors flex items-center gap-2">
                            <x-heroicon-o-play class="w-4 h-4" />
                            <span>{{ $quiz['is_completed'] ? 'Riprova' : 'Inizia' }}</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <x-heroicon-o-inbox class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <p class="text-gray-600 dark:text-gray-400">
                        Nessun quiz ministeriale disponibile al momento.
                    </p>
                </div>
            @endforelse
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="close">
                Chiudi
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>