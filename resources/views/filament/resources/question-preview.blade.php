{{-- resources/views/filament/resources/question-preview.blade.php --}}
<div class="space-y-6 p-6">
    {{-- Header con percorso teoria --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Percorso Teoria</h3>
        <p class="text-gray-900 dark:text-white font-medium">{{ $question->theory_path }}</p>
    </div>

    {{-- Domanda --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/20 rounded-full flex items-center justify-center">
                    <x-heroicon-o-question-mark-circle class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
            </div>
            <div class="flex-1">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">
                    {{ $question->text }}
                </h4>
                
                @if($question->image_url)
                    <div class="mt-4 mb-4">
                        <img src="{{ Storage::url($question->image_url) }}" 
                             alt="Immagine domanda" 
                             class="rounded-lg shadow-md max-w-full h-auto"
                             style="max-height: 300px;">
                    </div>
                @endif
                
                {{-- Risposta corretta --}}
                <div class="mt-4 flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Risposta corretta:</span>
                    @if($question->correct_answer)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            <x-heroicon-s-check-circle class="w-4 h-4 mr-1" />
                            Vero
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                            <x-heroicon-s-x-circle class="w-4 h-4 mr-1" />
                            Falso
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Spiegazione --}}
    @if($question->explanation)
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
            <div class="flex items-start space-x-3">
                <x-heroicon-o-light-bulb class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">Spiegazione</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-200">{{ $question->explanation }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Metadati --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">Difficoltà</p>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                @switch($question->difficulty_level)
                    @case(1)
                        <span class="text-green-600 dark:text-green-400">Facile</span>
                        @break
                    @case(2)
                        <span class="text-yellow-600 dark:text-yellow-400">Medio</span>
                        @break
                    @case(3)
                        <span class="text-red-600 dark:text-red-400">Difficile</span>
                        @break
                @endswitch
            </p>
        </div>
        
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">Tipo</p>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $question->is_ministerial ? 'Ministeriale' : 'Personalizzata' }}
            </p>
        </div>
        
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">N° Ministeriale</p>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $question->ministerial_number ?? 'N/D' }}
            </p>
        </div>
        
        <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">Stato</p>
            <p class="text-sm font-medium">
                @if($question->is_active)
                    <span class="text-green-600 dark:text-green-400">Attiva</span>
                @else
                    <span class="text-red-600 dark:text-red-400">Non attiva</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Statistiche utenti --}}
    @php
        $stats = $question->user_answer_stats;
    @endphp
    
    @if($stats['total_attempts'] > 0)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Statistiche Risposte</h4>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_attempts'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tentativi totali</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['correct'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Risposte corrette</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['incorrect'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Risposte errate</p>
                </div>
            </div>
            
            <div class="mt-3">
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                    <span>Tasso di successo</span>
                    <span>{{ $stats['accuracy'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-green-600 dark:bg-green-400 h-2 rounded-full" style="width: {{ $stats['accuracy'] }}%"></div>
                </div>
            </div>
        </div>
    @endif
</div>