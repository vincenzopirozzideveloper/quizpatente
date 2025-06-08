<x-filament-panels::page>
    <div x-data="theoryViewApp()" 
         x-init="init()"
         @statsUpdated.window="updateStats($event.detail.stats)"
         class="h-[calc(100vh-4rem)] bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-900 dark:to-gray-950 -m-6">
        
        {{-- Modern Header Bar --}}
        <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-800/50 px-6 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button wire:click="backToTopics" 
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
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
                
                {{-- View Mode Switcher --}}
                <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                    <button wire:click="switchViewMode('grid')"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-all
                                   {{ $viewMode === 'grid' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                            <span>Griglia</span>
                        </div>
                    </button>
                    <button wire:click="switchViewMode('list')"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-all
                                   {{ $viewMode === 'list' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-bars-3 class="w-4 h-4" />
                            <span>Lista</span>
                        </div>
                    </button>
                    <button wire:click="switchViewMode('focus')"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-all
                                   {{ $viewMode === 'focus' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400' }}">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-book-open class="w-4 h-4" />
                            <span>Focus</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        {{-- Progress Overview Bar --}}
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4"
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
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-medium text-white mix-blend-difference" 
                                  x-text="stats.completed + ' di ' + stats.total"></span>
                        </div>
                    </div>
                </div>
                
                {{-- Subtopic Progress Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($this->progressBySubtopic as $subtopic)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $subtopic['code'] }}</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $subtopic['percentage'] }}%</span>
                            </div>
                            <h4 class="text-xs text-gray-700 dark:text-gray-300 truncate mb-2">{{ $subtopic['title'] }}</h4>
                            <div class="h-1 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-full transition-all duration-300"
                                     style="width: {{ $subtopic['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-hidden">
            {{-- Grid View --}}
            <div x-show="viewMode === 'grid'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="h-full overflow-y-auto p-6">
                
                {{-- Filters --}}
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button wire:click="filterByStatus('all')"
                                class="px-3 py-1 rounded-full text-sm font-medium transition-colors
                                       {{ $filterStatus === 'all' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                            Tutti ({{ $stats['total'] }})
                        </button>
                        <button wire:click="filterByStatus('unread')"
                                class="px-3 py-1 rounded-full text-sm font-medium transition-colors
                                       {{ $filterStatus === 'unread' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                            Da leggere ({{ $stats['total'] - $stats['completed'] - $stats['inProgress'] }})
                        </button>
                        <button wire:click="filterByStatus('reading')"
                                class="px-3 py-1 rounded-full text-sm font-medium transition-colors
                                       {{ $filterStatus === 'reading' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                            In lettura ({{ $stats['inProgress'] }})
                        </button>
                        <button wire:click="filterByStatus('read')"
                                class="px-3 py-1 rounded-full text-sm font-medium transition-colors
                                       {{ $filterStatus === 'read' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                            Completati ({{ $stats['completed'] }})
                        </button>
                    </div>
                    
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.300ms="searchQuery"
                               placeholder="Cerca contenuti..."
                               class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg 
                                      bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                      focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                    </div>
                </div>
                
                {{-- Content Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($this->filteredContents as $content)
                        @php
                            $status = $contentStatuses[$content->id] ?? 'unread';
                        @endphp
                        <div class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden
                                    {{ $contentId === $content->id ? 'ring-2 ring-blue-500' : '' }}">
                            
                            {{-- Status Badge --}}
                            <div class="absolute top-3 right-3 z-10">
                                <button wire:click="toggleContentStatus({{ $content->id }})"
                                        class="p-2 rounded-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm shadow-lg
                                               hover:scale-110 transition-transform">
                                    @if ($status === 'read')
                                        <x-heroicon-s-check-circle class="w-6 h-6 text-green-500" />
                                    @elseif ($status === 'reading')
                                        <x-heroicon-o-clock class="w-6 h-6 text-amber-500" />
                                    @else
                                        <x-heroicon-o-check-circle class="w-6 h-6 text-gray-400" />
                                    @endif
                                </button>
                            </div>
                            
                            {{-- Content Preview --}}
                            <button wire:click="jumpToContent({{ $content->id }})" 
                                    class="w-full text-left">
                                @if($content->image_url)
                                    <div class="h-40 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                        <img src="{{ Storage::url($content->image_url) }}" 
                                             alt=""
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @else
                                    <div class="h-40 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 
                                                flex items-center justify-center">
                                        <x-heroicon-o-document-text class="w-16 h-16 text-gray-300 dark:text-gray-600" />
                                    </div>
                                @endif
                                
                                <div class="p-4">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        <span class="font-mono">{{ $content->code }}</span>
                                        <span>•</span>
                                        <span>{{ $content->subtopic->title }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                                        {{ Str::limit(strip_tags($content->content), 120) }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- List View --}}
            <div x-show="viewMode === 'list'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="h-full overflow-y-auto">
                <div class="max-w-4xl mx-auto p-6 space-y-3">
                    @foreach($this->filteredContents as $content)
                        @php
                            $status = $contentStatuses[$content->id] ?? 'unread';
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                    {{ $contentId === $content->id ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="flex items-center p-4">
                                <button wire:click="toggleContentStatus({{ $content->id }})"
                                        class="flex-shrink-0 mr-4">
                                    @if ($status === 'read')
                                        <x-heroicon-s-check-circle class="w-8 h-8 text-green-500" />
                                    @elseif ($status === 'reading')
                                        <x-heroicon-o-clock class="w-8 h-8 text-amber-500" />
                                    @else
                                        <x-heroicon-o-check-circle class="w-8 h-8 text-gray-400 hover:text-gray-600" />
                                    @endif
                                </button>
                                
                                <button wire:click="jumpToContent({{ $content->id }})" 
                                        class="flex-1 text-left">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $content->code }}</span>
                                                <span class="text-sm text-gray-400">•</span>
                                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $content->subtopic->title }}</span>
                                            </div>
                                            <p class="text-gray-900 dark:text-white font-medium">
                                                {{ Str::limit(strip_tags($content->content), 150) }}
                                            </p>
                                        </div>
                                        @if($content->image_url)
                                            <div class="ml-4 flex-shrink-0">
                                                <img src="{{ Storage::url($content->image_url) }}" 
                                                     alt=""
                                                     class="w-20 h-20 object-cover rounded-lg">
                                            </div>
                                        @endif
                                    </div>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Focus View --}}
            <div x-show="viewMode === 'focus'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="h-full flex">
                
                {{-- Content Navigator (Left Panel) --}}
                <div class="w-80 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 overflow-y-auto">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Navigazione Rapida</h3>
                        <div class="space-y-1">
                            @foreach($allContents as $idx => $content)
                                @php
                                    $status = $contentStatuses[$content->id] ?? 'unread';
                                @endphp
                                <button wire:click="jumpToContent({{ $content->id }})"
                                        class="w-full flex items-center space-x-3 p-2 rounded-lg transition-colors
                                               {{ $contentId === $content->id 
                                                   ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' 
                                                   : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                    <span class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $idx + 1 }}</span>
                                    <div class="flex-1 text-left">
                                        <p class="text-sm {{ $contentId === $content->id ? 'font-medium' : '' }} line-clamp-1">
                                            {{ Str::limit(strip_tags($content->content), 40) }}
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
                    <div class="flex-1 flex flex-col">
                        {{-- Reading Header --}}
                        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-8 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="flex items-center space-x-3 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="font-mono">{{ $currentContent->code }}</span>
                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                        <span>{{ $currentContent->subtopic->title }}</span>
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                        Contenuto {{ $currentIndex + 1 }} di {{ $allContents->count() }}
                                    </h2>
                                </div>
                                
                                <button wire:click="markAsComplete"
                                        class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all
                                               {{ ($contentStatuses[$currentContent->id] ?? 'unread') === 'read'
                                                   ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300'
                                                   : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
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
                        <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-950">
                            <article class="max-w-4xl mx-auto px-8 py-12">
                                @if ($currentContent->image_url && $currentContent->image_position === 'before')
                                    <figure class="mb-8">
                                        <img src="{{ Storage::url($currentContent->image_url) }}" 
                                             alt="{{ $currentContent->image_caption ?? '' }}"
                                             class="w-full rounded-xl shadow-lg">
                                        @if ($currentContent->image_caption)
                                            <figcaption class="text-sm text-gray-600 dark:text-gray-400 text-center mt-3 italic">
                                                {{ $currentContent->image_caption }}
                                            </figcaption>
                                        @endif
                                    </figure>
                                @endif

                                <div class="prose prose-lg prose-gray dark:prose-invert max-w-none">
                                    {!! Str::markdown($currentContent->content) !!}
                                </div>

                                @if ($currentContent->image_url && $currentContent->image_position === 'after')
                                    <figure class="mt-8">
                                        <img src="{{ Storage::url($currentContent->image_url) }}" 
                                             alt="{{ $currentContent->image_caption ?? '' }}"
                                             class="w-full rounded-xl shadow-lg">
                                        @if ($currentContent->image_caption)
                                            <figcaption class="text-sm text-gray-600 dark:text-gray-400 text-center mt-3 italic">
                                                {{ $currentContent->image_caption }}
                                            </figcaption>
                                        @endif
                                    </figure>
                                @endif
                            </article>
                        </div>

                        {{-- Navigation Footer --}}
                        <div class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 px-8 py-4">
                            <div class="flex items-center justify-between">
                                <button wire:click="previousContent"
                                        class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium
                                               {{ $currentIndex > 0 
                                                   ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' 
                                                   : 'bg-gray-50 dark:bg-gray-900 text-gray-400 dark:text-gray-600 cursor-not-allowed' }}"
                                        {{ $currentIndex === 0 ? 'disabled' : '' }}>
                                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                                    <span>Precedente</span>
                                </button>

                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $currentIndex + 1 }} / {{ $allContents->count() }}
                                    </span>
                                </div>

                                <button wire:click="nextContent"
                                        class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium
                                               {{ $currentIndex < $allContents->count() - 1 
                                                   ? 'bg-blue-600 hover:bg-blue-700 text-white' 
                                                   : 'bg-gray-50 dark:bg-gray-900 text-gray-400 dark:text-gray-600 cursor-not-allowed' }}"
                                        {{ $currentIndex === $allContents->count() - 1 ? 'disabled' : '' }}>
                                    <span>Successivo</span>
                                    <x-heroicon-o-arrow-right class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Floating Action Buttons --}}
        <div class="fixed bottom-6 right-6 flex flex-col space-y-3">
            <button wire:click="toggleProgress"
                    class="p-3 bg-white dark:bg-gray-800 rounded-full shadow-lg hover:shadow-xl transition-all">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-gray-600 dark:text-gray-400" />
            </button>
            
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
        
        .line-clamp-3 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
    </style>

    <script>
        function theoryViewApp() {
            return {
                stats: @json($stats),
                showProgress: @entangle('showProgress'),
                viewMode: @entangle('viewMode'),
                
                init() {
                    // Initialize stats
                    this.updateProgressBar();
                },
                
                updateStats(newStats) {
                    this.stats = newStats;
                    this.updateProgressBar();
                },
                
                updateProgressBar() {
                    // Force re-render of progress bars
                    this.$nextTick(() => {
                        const progressBars = document.querySelectorAll('[data-progress]');
                        progressBars.forEach(bar => {
                            const width = bar.getAttribute('data-progress');
                            bar.style.width = width + '%';
                        });
                    });
                }
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.matches('input, textarea')) return;
            
            switch(e.key) {
                case '1':
                    @this.switchViewMode('grid');
                    break;
                case '2':
                    @this.switchViewMode('list');
                    break;
                case '3':
                    @this.switchViewMode('focus');
                    break;
                case 'ArrowLeft':
                    if (@this.viewMode === 'focus') {
                        e.preventDefault();
                        @this.previousContent();
                    }
                    break;
                case 'ArrowRight':
                    if (@this.viewMode === 'focus') {
                        e.preventDefault();
                        @this.nextContent();
                    }
                    break;
                case ' ':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        @this.markAsComplete();
                    }
                    break;
            }
        });
    </script>
</x-filament-panels::page>