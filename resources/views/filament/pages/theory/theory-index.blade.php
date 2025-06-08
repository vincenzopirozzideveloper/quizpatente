<x-filament-panels::page>
    {{-- Header con statistiche --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 rounded-3xl p-8 text-white shadow-2xl">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold mb-2">Studia la Teoria</h1>
                <p class="text-primary-100 mb-6">Padroneggia tutti gli argomenti per superare l'esame di teoria</p>
                
                {{-- Statistiche generali --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Argomenti totali</p>
                                <p class="text-2xl font-bold">{{ $statistics['total_topics'] }}</p>
                            </div>
                            <x-heroicon-o-academic-cap class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Completati</p>
                                <p class="text-2xl font-bold">{{ $statistics['completed_topics'] }}</p>
                            </div>
                            <x-heroicon-o-check-circle class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Progresso totale</p>
                                <p class="text-2xl font-bold">{{ $statistics['total_progress'] }}%</p>
                            </div>
                            <x-heroicon-o-chart-pie class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-100 text-sm">Domande studiate</p>
                                <p class="text-2xl font-bold">{{ $statistics['completed_questions'] }}/{{ $statistics['total_questions'] }}</p>
                            </div>
                            <x-heroicon-o-clipboard-document-check class="w-8 h-8 text-primary-200" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Griglia degli argomenti --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($topics as $topic)
            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700">
                {{-- Progress bar superiore --}}
                <div class="absolute top-0 left-0 w-full h-1 bg-gray-200 dark:bg-gray-700">
                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-500"
                         style="width: {{ $topic['percentage'] }}%"></div>
                </div>
                
                {{-- Contenuto della card --}}
                <div class="p-6">
                    {{-- Header con icona e codice --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl group-hover:scale-110 transition-transform duration-300">
                                <x-dynamic-component 
                                    :component="$topic['icon']" 
                                    class="w-6 h-6 text-primary-600 dark:text-primary-400" 
                                />
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    Argomento {{ $topic['code'] }}
                                </span>
                            </div>
                        </div>
                        
                        @if($topic['is_completed'])
                            <div class="flex items-center space-x-1">
                                <x-heroicon-s-check-circle class="w-5 h-5 text-success-500" />
                                <span class="text-xs text-success-600 dark:text-success-400 font-medium">Completato</span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Titolo e descrizione --}}
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                        {{ $topic['name'] }}
                    </h3>
                    
                    @if($topic['description'])
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                            {{ $topic['description'] }}
                        </p>
                    @endif
                    
                    {{-- Statistiche --}}
                    <div class="space-y-3 mb-6">
                        {{-- Contenuti teorici --}}
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Contenuti teorici:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $topic['theory_contents_count'] }}</span>
                        </div>
                    </div>
                    
                    {{-- Progress bar dettagliata --}}
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-600 dark:text-gray-400">Progresso</span>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $topic['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-primary-500 to-primary-600 h-2 rounded-full transition-all duration-500"
                                 style="width: {{ $topic['percentage'] }}%"></div>
                        </div>
                    </div>
                    
                    {{-- Bottone azione --}}
                    <button wire:click="startTopic({{ $topic['id'] }})"
                            class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 group">
                        <span>{{ $topic['percentage'] > 0 ? 'Continua' : 'Inizia' }} lo studio</span>
                        <x-heroicon-m-arrow-right class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Empty state --}}
    @if($topics->isEmpty())
        <div class="text-center py-16">
            <x-heroicon-o-book-open class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nessun argomento disponibile</h3>
            <p class="text-gray-600 dark:text-gray-400">Gli argomenti di teoria verranno aggiunti a breve.</p>
        </div>
    @endif
</x-filament-panels::page>