<x-filament-panels::page>
    <div class="h-screen flex flex-col -m-6" wire:poll.1s="decrementTimer">
        {{-- Header Quiz --}}
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ ucfirst(str_replace('_', ' ', $quizSession->type)) }}
                    </h1>
                    
                    {{-- Progress --}}
                    <div class="flex items-center space-x-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Domanda</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $currentQuestionIndex + 1 }} / {{ count($quizSession->quizAnswers) }}
                        </span>
                    </div>
                    
                    {{-- Statistiche --}}
                    <div class="hidden md:flex items-center space-x-4 ml-8">
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ $correctCount }}</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                            <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ $wrongCount }}</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <x-heroicon-o-question-mark-circle class="w-5 h-5 text-gray-400" />
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{ count($quizSession->quizAnswers) - $answeredCount }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    {{-- Timer (solo per quiz ministeriali) --}}
                    @if(in_array($quizSession->type, ['ministerial', 'ministerial_manual']))
                        <div class="flex items-center space-x-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <x-heroicon-o-clock class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                            <span class="font-mono text-lg font-medium {{ $remainingTime < 300 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                {{ $this->getFormattedTime() }}
                            </span>
                        </div>
                    @endif
                    
                    {{-- Bottone Manuale (se disponibile) --}}
                    @if($quizSession->metadata['with_manual'] ?? false)
                        <button wire:click="toggleTheory" 
                                class="flex items-center space-x-2 px-4 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg transition-colors">
                            <x-heroicon-o-book-open class="w-5 h-5" />
                            <span class="font-medium">Manuale</span>
                        </button>
                    @endif
                    
                    {{-- Bottone Completa --}}
                    <button wire:click="completeQuiz" 
                            wire:confirm="Sei sicuro di voler completare il quiz? Le domande non risposte verranno considerate errate."
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        Completa Quiz
                    </button>
                </div>
            </div>
        </div>

        {{-- Contenuto Quiz --}}
        <div class="flex-1 flex overflow-hidden">
            {{-- Sidebar Domande --}}
            <div class="w-64 bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Navigazione Domande</h3>
                
                <div class="grid grid-cols-5 gap-2">
                    @foreach($quizSession->quizAnswers as $index => $answer)
                        <button wire:click="goToQuestion({{ $index }})" 
                                class="relative w-full aspect-square rounded-lg font-medium text-sm transition-all
                                       {{ $currentQuestionIndex === $index 
                                           ? 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-900' 
                                           : '' }}
                                       {{ $answer->user_answer === null 
                                           ? 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-600' 
                                           : ($this->getQuestionButtonColor($index) === 'success' 
                                               ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300' 
                                               : ($this->getQuestionButtonColor($index) === 'danger'
                                                   ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300'
                                                   : 'bg-primary-100 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300')) }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
                
                {{-- Legenda --}}
                <div class="mt-6 space-y-2 text-xs">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Non risposta</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-primary-100 dark:bg-primary-900/20 rounded"></div>
                        <span class="text-gray-600 dark:text-gray-400">Risposta data</span>
                    </div>
                    @if($quizSession->metadata['with_manual'] ?? false)
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-green-100 dark:bg-green-900/20 rounded"></div>
                            <span class="text-gray-600 dark:text-gray-400">Corretta</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-4 h-4 bg-red-100 dark:bg-red-900/20 rounded"></div>
                            <span class="text-gray-600 dark:text-gray-400">Errata</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Area Domanda --}}
            <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-900">
                {{-- Domanda --}}
                <div class="flex-1 overflow-y-auto p-8">
                    <div class="max-w-4xl mx-auto">
                        {{-- Info Argomento --}}
                        <div class="mb-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $currentAnswer->question->topic->name }}
                                @if($currentAnswer->question->subtopic)
                                    > {{ $currentAnswer->question->subtopic->title }}
                                @endif
                            </p>
                        </div>
                        
                        {{-- Testo Domanda --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 mb-8">
                            <h2 class="text-xl font-medium text-gray-900 dark:text-white mb-6">
                                {{ $currentAnswer->question->text }}
                            </h2>
                            
                            @if($currentAnswer->question->image_url)
                                <div class="mb-6">
                                    <img src="{{ Storage::url($currentAnswer->question->image_url) }}" 
                                         alt="Immagine domanda" 
                                         class="rounded-lg shadow-md max-w-full h-auto mx-auto"
                                         style="max-height: 400px;">
                                </div>
                            @endif
                            
                            {{-- Bottoni Risposta --}}
                            <div class="grid grid-cols-2 gap-6 mt-8">
                                <button wire:click="selectAnswer(true)" 
                                        class="relative py-6 px-8 rounded-xl border-2 transition-all duration-200
                                               {{ $selectedAnswer === true 
                                                   ? 'border-green-500 bg-green-50 dark:bg-green-900/20' 
                                                   : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500' }}">
                                    <div class="flex items-center justify-center space-x-3">
                                        <x-heroicon-o-check-circle class="w-8 h-8 {{ $selectedAnswer === true ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}" />
                                        <span class="text-xl font-medium {{ $selectedAnswer === true ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300' }}">
                                            VERO
                                        </span>
                                    </div>
                                    @if($selectedAnswer === true)
                                        <div class="absolute top-2 right-2">
                                            <x-heroicon-s-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                                        </div>
                                    @endif
                                </button>
                                
                                <button wire:click="selectAnswer(false)" 
                                        class="relative py-6 px-8 rounded-xl border-2 transition-all duration-200
                                               {{ $selectedAnswer === false 
                                                   ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                                   : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500' }}">
                                    <div class="flex items-center justify-center space-x-3">
                                        <x-heroicon-o-x-circle class="w-8 h-8 {{ $selectedAnswer === false ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}" />
                                        <span class="text-xl font-medium {{ $selectedAnswer === false ? 'text-red-700 dark:text-red-300' : 'text-gray-700 dark:text-gray-300' }}">
                                            FALSO
                                        </span>
                                    </div>
                                    @if($selectedAnswer === false)
                                        <div class="absolute top-2 right-2">
                                            <x-heroicon-s-check-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                                        </div>
                                    @endif
                                </button>
                            </div>
                            
                            {{-- Feedback immediato (solo con manuale) --}}
                            @if(($quizSession->metadata['with_manual'] ?? false) && $selectedAnswer !== null)
                                <div class="mt-6 p-4 rounded-lg {{ $selectedAnswer === $currentAnswer->question->correct_answer ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20' }}">
                                    <div class="flex items-start space-x-3">
                                        @if($selectedAnswer === $currentAnswer->question->correct_answer)
                                            <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" />
                                            <div>
                                                <p class="font-medium text-green-900 dark:text-green-100">Risposta corretta!</p>
                                                @if($currentAnswer->question->explanation)
                                                    <p class="mt-1 text-sm text-green-800 dark:text-green-200">
                                                        {{ $currentAnswer->question->explanation }}
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" />
                                            <div>
                                                <p class="font-medium text-red-900 dark:text-red-100">
                                                    Risposta errata! La risposta corretta è: <strong>{{ $currentAnswer->question->correct_answer ? 'VERO' : 'FALSO' }}</strong>
                                                </p>
                                                @if($currentAnswer->question->explanation)
                                                    <p class="mt-1 text-sm text-red-800 dark:text-red-200">
                                                        {{ $currentAnswer->question->explanation }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Navigazione Domande --}}
                <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-8 py-4">
                    <div class="max-w-4xl mx-auto flex items-center justify-between">
                        <button wire:click="previousQuestion" 
                                @if($currentQuestionIndex === 0) disabled @endif
                                class="flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors
                                       {{ $currentQuestionIndex === 0 
                                           ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed' 
                                           : 'bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
                            <x-heroicon-o-chevron-left class="w-5 h-5" />
                            <span>Precedente</span>
                        </button>
                        
                        <div class="flex items-center space-x-2">
                            @foreach(range(max(0, $currentQuestionIndex - 2), min(count($quizSession->quizAnswers) - 1, $currentQuestionIndex + 2)) as $i)
                                <button wire:click="goToQuestion({{ $i }})"
                                        class="w-10 h-10 rounded-lg font-medium transition-all
                                               {{ $i === $currentQuestionIndex 
                                                   ? 'bg-primary-600 text-white' 
                                                   : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                                    {{ $i + 1 }}
                                </button>
                            @endforeach
                        </div>
                        
                        <button wire:click="nextQuestion" 
                                @if($currentQuestionIndex === count($quizSession->quizAnswers) - 1) disabled @endif
                                class="flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors
                                       {{ $currentQuestionIndex === count($quizSession->quizAnswers) - 1 
                                           ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed' 
                                           : 'bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
                            <span>Successiva</span>
                            <x-heroicon-o-chevron-right class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Teoria (solo con manuale) --}}
    @if($quizSession->metadata['with_manual'] ?? false)
        <x-filament::modal id="theory-modal" :visible="$showTheoryModal" width="5xl">
            <x-slot name="heading">
                Teoria Correlata
            </x-slot>

            @if($currentAnswer->question->theoryContent)
                <div class="space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $currentAnswer->question->topic->name }} > 
                            {{ $currentAnswer->question->subtopic?->title }} > 
                            {{ $currentAnswer->question->theoryContent->code }}
                        </p>
                    </div>
                    
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        @if($currentAnswer->question->theoryContent->image_url && $currentAnswer->question->theoryContent->image_position === 'before')
                            <figure class="mb-6">
                                <img src="{{ Storage::url($currentAnswer->question->theoryContent->image_url) }}" 
                                     alt="{{ $currentAnswer->question->theoryContent->image_caption ?? '' }}"
                                     class="w-full rounded-lg shadow-md">
                                @if($currentAnswer->question->theoryContent->image_caption)
                                    <figcaption class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $currentAnswer->question->theoryContent->image_caption }}
                                    </figcaption>
                                @endif
                            </figure>
                        @endif
                        
                        {!! Str::markdown($currentAnswer->question->theoryContent->content) !!}
                        
                        @if($currentAnswer->question->theoryContent->image_url && $currentAnswer->question->theoryContent->image_position === 'after')
                            <figure class="mt-6">
                                <img src="{{ Storage::url($currentAnswer->question->theoryContent->image_url) }}" 
                                     alt="{{ $currentAnswer->question->theoryContent->image_caption ?? '' }}"
                                     class="w-full rounded-lg shadow-md">
                                @if($currentAnswer->question->theoryContent->image_caption)
                                    <figcaption class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ $currentAnswer->question->theoryContent->image_caption }}
                                    </figcaption>
                                @endif
                            </figure>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-exclamation-circle class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-600 dark:text-gray-400">
                        Nessun contenuto teorico associato a questa domanda.
                    </p>
                </div>
            @endif

            <x-slot name="footer">
                <x-filament::button color="gray" wire:click="toggleTheory">
                    Chiudi
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    @endif
</x-filament-panels::page>