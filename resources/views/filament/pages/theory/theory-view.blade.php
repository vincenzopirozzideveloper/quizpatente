{{-- resources/views/filament/pages/theory/theory-view.blade.php --}}
<x-filament-panels::page>
    <div class="h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900 -m-6 overflow-hidden" 
         x-data="theoryView()" 
         x-init="init()">
        
        {{-- Header Bar --}}
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button wire:click="backToTopics" 
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </button>
                    
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $topic->name }}</h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                            <span>{{ $topic->code }}</span>
                            <span>•</span>
                            <span x-text="stats.completed + '/' + stats.total + ' completati'"></span>
                        </div>
                    </div>
                </div>
                
                {{-- Progress Toggle --}}
                <button wire:click="toggleProgress"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                </button>
            </div>
        </div>

        {{-- Progress Overview Bar --}}
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4"
             x-show="showProgress"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="max-w-7xl mx-auto">
                {{-- Overall Progress --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Progresso Complessivo</h3>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.percentage + '%'"></span>
                    </div>
                    <div class="relative h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-500 ease-out"
                             :style="'width: ' + stats.percentage + '%'"></div>
                    </div>
                </div>
                
                {{-- Content Progress Chips --}}
                <div class="flex flex-wrap gap-2">
                    @foreach($contents as $content)
                        @php
                            $status = $contentStatuses[$content->id] ?? 'unread';
                        @endphp
                        <button wire:click="jumpToContent({{ $content->id }})"
                                class="px-3 py-1 rounded-full text-xs font-medium transition-all
                                       {{ $contentId === $content->id 
                                           ? 'bg-blue-600 text-white' 
                                           : ($status === 'read' 
                                               ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' 
                                               : ($status === 'reading' 
                                                   ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' 
                                                   : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400')) }}">
                            {{ $content->code }} - {{ Str::limit($content->title, 20) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content Area with Sidebar --}}
        <div class="flex h-[calc(100%-8rem)]" :class="{ 'h-[calc(100%-4rem)]': !showProgress }">
            {{-- Content Navigator (Left Sidebar) --}}
            <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Navigazione Rapida</h3>
                    <div class="space-y-1">
                        @foreach($contents as $idx => $content)
                            @php
                                $status = $contentStatuses[$content->id] ?? 'unread';
                            @endphp
                            <button wire:click="jumpToContent({{ $content->id }})"
                                    class="w-full flex items-center space-x-3 p-2 rounded-lg transition-colors
                                           {{ $contentId === $content->id 
                                               ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' 
                                               : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                <span class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $content->code }}</span>
                                <div class="flex-1 text-left">
                                    <p class="text-sm {{ $contentId === $content->id ? 'font-medium' : '' }} line-clamp-1">
                                        {{ $content->title }}
                                    </p>
                                </div>
                                @if ($status === 'read')
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 flex-shrink-0" />
                                @elseif ($status === 'reading')
                                    <x-heroicon-o-clock class="w-5 h-5 text-amber-500 flex-shrink-0" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Reading Area --}}
            @if($currentContent)
                <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-900">
                    {{-- Reading Header --}}
                    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-8 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center space-x-3 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-mono">{{ $currentContent->code }}</span>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ $currentContent->title }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Contenuto {{ $currentIndex + 1 }} di {{ $contents->count() }}
                                </p>
                            </div>
                            
                            <button wire:click="markAsComplete"
                                    class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all
                                           {{ ($contentStatuses[$currentContent->id] ?? 'unread') === 'read'
                                               ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300'
                                               : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                @if (($contentStatuses[$currentContent->id] ?? 'unread') === 'read')
                                    <x-heroicon-s-check-circle class="w-5 h-5" />
                                    <span>Completato</span>
                                @else
                                    <x-heroicon-o-check-circle class="w-5 h-5" />
                                    <span>Segna come completato</span>
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- Reading Content --}}
                    <div class="flex-1 overflow-y-auto">
                        <article class="max-w-4xl mx-auto px-8 py-12">
                            @if ($currentContent->image_url && $currentContent->image_position === 'before')
                                <figure class="mb-8 flex flex-col items-center">
                                    <img src="{{ Storage::url($currentContent->image_url) }}" 
                                         alt="{{ $currentContent->image_caption ?? '' }}"
                                         class="max-w-full max-h-96 object-contain rounded-xl shadow-lg hover:shadow-xl transition-shadow cursor-pointer">
                                    @if ($currentContent->image_caption)
                                        <figcaption class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400 italic">
                                            {{ $currentContent->image_caption }}
                                        </figcaption>
                                    @endif
                                </figure>
                            @endif

                            <div class="prose prose-lg prose-gray dark:prose-invert max-w-none">
                                {!! Str::markdown($currentContent->content) !!}
                            </div>

                            @if ($currentContent->image_url && $currentContent->image_position === 'after')
                                <figure class="mt-8 flex flex-col items-center">
                                    <img src="{{ Storage::url($currentContent->image_url) }}" 
                                         alt="{{ $currentContent->image_caption ?? '' }}"
                                         class="max-w-full max-h-96 object-contain rounded-xl shadow-lg hover:shadow-xl transition-shadow cursor-pointer">
                                    @if ($currentContent->image_caption)
                                        <figcaption class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400 italic">
                                            {{ $currentContent->image_caption }}
                                        </figcaption>
                                    @endif
                                </figure>
                            @endif
                        </article>
                    </div>

                    {{-- Navigation Footer --}}
                    <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-8 py-4">
                        <div class="flex items-center justify-between">
                            <button wire:click="previousContent"
                                    class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all
                                           {{ $currentIndex > 0 
                                               ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' 
                                               : 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed' }}"
                                    {{ $currentIndex === 0 ? 'disabled' : '' }}>
                                <x-heroicon-o-arrow-left class="w-5 h-5" />
                                <span>Precedente</span>
                            </button>

                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $currentIndex + 1 }} / {{ $contents->count() }}
                                </span>
                            </div>

                            <button wire:click="nextContent"
                                    class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all
                                           {{ $currentIndex < $contents->count() - 1 
                                               ? 'bg-blue-600 hover:bg-blue-700 text-white' 
                                               : 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed' }}"
                                    {{ $currentIndex === $contents->count() - 1 ? 'disabled' : '' }}>
                                <span>Successivo</span>
                                <x-heroicon-o-arrow-right class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Floating Action Menu --}}
        <div class="fixed bottom-6 right-6">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-lg hover:shadow-xl transition-all">
                    <x-heroicon-o-ellipsis-horizontal class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                </button>
                
                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute bottom-full right-0 mb-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                    <button wire:click="markAllAsRead"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-t-lg">
                        Segna tutto come letto
                    </button>
                    <button wire:click="resetProgress"
                            wire:confirm="Sei sicuro di voler resettare tutto il progresso?"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-b-lg">
                        Resetta progresso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }
        
        /* Gestione immagini responsive */
        article img {
            max-height: 24rem; /* max-h-96 = 384px */
            width: auto;
            height: auto;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Immagini dentro prose content */
        .prose img {
            max-width: 100%;
            height: auto;
            margin-left: auto;
            margin-right: auto;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>

    <script>
        function theoryView() {
            return {
                stats: @json($stats),
                showProgress: @entangle('showProgress'),
                
                init() {
                    this.updateProgressBar();
                    
                    // Listener per aggiornamenti statistiche
                    Livewire.on('statsUpdated', (event) => {
                        this.stats = event.stats;
                        this.updateProgressBar();
                    });
                },
                
                updateProgressBar() {
                    this.$nextTick(() => {
                        const progressBar = document.querySelector('[data-progress]');
                        if (progressBar) {
                            progressBar.style.width = this.stats.percentage + '%';
                        }
                    });
                }
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.matches('input, textarea')) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    @this.previousContent();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    @this.nextContent();
                    break;
                case ' ':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        @this.markAsComplete();
                    }
                    break;
                case 'p':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        @this.toggleProgress();
                    }
                    break;
            }
        });
    </script>
</x-filament-panels::page>