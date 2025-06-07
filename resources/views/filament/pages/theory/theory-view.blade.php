<x-filament-panels::page>
    <div class="relative min-h-screen -m-6">
        {{-- Header fisso --}}
        <div class="sticky top-0 z-40 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    {{-- Lato sinistro --}}
                    <div class="flex items-center space-x-4">
                        {{-- Bottone menu mobile --}}
                        <button wire:click="toggleMenu"
                                class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <x-heroicon-o-bars-3 class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                        </button>
                        
                        {{-- Back button --}}
                        <button wire:click="backToIndex"
                                class="flex items-center space-x-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <x-heroicon-o-arrow-left class="w-5 h-5" />
                            <span class="hidden sm:inline">Torna agli argomenti</span>
                        </button>
                    </div>
                    
                    {{-- Centro - Titolo --}}
                    <div class="flex-1 px-4">
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                            {{ $topic->name }}
                        </h1>
                    </div>
                    
                    {{-- Lato destro - Progress --}}
                    <div class="flex items-center space-x-3">
                        <div class="hidden sm:flex items-center space-x-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Progresso</span>
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full transition-all duration-500"
                                     style="width: {{ $this->getProgressPercentage() }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $this->getProgressPercentage() }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Layout principale con sidebar --}}
        <div class="flex h-[calc(100vh-4rem)]">
            {{-- Sidebar Desktop --}}
            <aside class="hidden lg:block w-80 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                <div class="p-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                        Indice dei contenuti
                    </h2>
                    
                    {{-- Lista sottoargomenti --}}
                    <div class="space-y-4">
                        @foreach($subtopics as $subtopic)
                            <div class="space-y-1">
                                {{-- Titolo sottoargomento --}}
                                <button wire:click="loadSubtopic({{ $subtopic->id }})"
                                        class="w-full text-left p-3 rounded-lg transition-all duration-200
                                               {{ $subtopicId == $subtopic->id 
                                                  ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                  : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium opacity-60">{{ $subtopic->code }}</span>
                                            <span class="font-medium">{{ $subtopic->title }}</span>
                                        </div>
                                        <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded-full">
                                            {{ $subtopic->theory_contents_count }}
                                        </span>
                                    </div>
                                </button>
                                
                                {{-- Lista contenuti del sottoargomento --}}
                                @if($subtopicId == $subtopic->id && $contents->isNotEmpty())
                                    <div class="ml-4 space-y-1">
                                        @foreach($contents as $content)
                                            <button wire:click="loadContent({{ $content->id }})"
                                                    class="w-full text-left p-2 rounded-lg text-sm transition-all duration-200 flex items-center space-x-2
                                                           {{ $contentId == $content->id 
                                                              ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 font-medium' 
                                                              : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                                @if(in_array($content->id, $readContents))
                                                    <x-heroicon-s-check-circle class="w-4 h-4 text-success-500 flex-shrink-0" />
                                                @else
                                                    <x class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                                @endif
                                                <span class="truncate">{{ $content->code }} - {{ Str::limit(strip_tags($content->content), 30) }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>

            {{-- Sidebar Mobile (overlay) --}}
            <div x-show="$wire.isMenuOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="lg:hidden fixed inset-0 z-50 bg-black/50"
                 wire:click="toggleMenu">
            </div>
            
            <aside x-show="$wire.isMenuOpen"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="-translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="-translate-x-full"
                   class="lg:hidden fixed left-0 top-16 bottom-0 z-50 w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                {{-- Stesso contenuto della sidebar desktop --}}
                <div class="p-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                        Indice dei contenuti
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($subtopics as $subtopic)
                            <div class="space-y-1">
                                <button wire:click="loadSubtopic({{ $subtopic->id }})"
                                        class="w-full text-left p-3 rounded-lg transition-all duration-200
                                               {{ $subtopicId == $subtopic->id 
                                                  ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' 
                                                  : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-medium opacity-60">{{ $subtopic->code }}</span>
                                            <span class="font-medium">{{ $subtopic->title }}</span>
                                        </div>
                                        <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-1 rounded-full">
                                            {{ $subtopic->theory_contents_count }}
                                        </span>
                                    </div>
                                </button>
                                
                                @if($subtopicId == $subtopic->id && $contents->isNotEmpty())
                                    <div class="ml-4 space-y-1">
                                        @foreach($contents as $content)
                                            <button wire:click="navigateToContent({{ $content->id }})"
                                                    class="w-full text-left p-2 rounded-lg text-sm transition-all duration-200 flex items-center space-x-2
                                                           {{ $contentId == $content->id 
                                                              ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-200 font-medium' 
                                                              : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                                @if(in_array($content->id, $readContents))
                                                    <x-class="w-4 h-4 text-success-500 flex-shrink-0" />
                                                @else
                                                    <x-class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                                @endif
                                                <span class="truncate">{{ $content->code }} - {{ Str::limit(strip_tags($content->content), 30) }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>

            {{-- Area contenuto principale --}}
            <main class="flex-1 overflow-y-auto bg-white dark:bg-gray-900">
                @if($currentContent)
                    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                        {{-- Breadcrumb --}}
                        <nav class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 mb-6">
                            <span>{{ $topic->name }}</span>
                            <x-heroicon-m-chevron-right class="w-4 h-4" />
                            <span>{{ $currentSubtopic->title }}</span>
                            <x-heroicon-m-chevron-right class="w-4 h-4" />
                            <span class="text-gray-900 dark:text-white font-medium">{{ $currentContent->code }}</span>
                        </nav>

                        {{-- Contenuto principale --}}
                        <div class="prose prose-lg dark:prose-invert max-w-none">
                            {{-- Immagine prima del contenuto (se configurata) --}}
                            @if($currentContent->image_url && $currentContent->image_position === 'before')
                                <figure class="mb-8">
                                    <img src="{{ Storage::url($currentContent->image_url) }}" 
                                         alt="{{ $currentContent->image_caption ?? 'Immagine illustrativa' }}"
                                         class="w-full rounded-xl shadow-lg">
                                    @if($currentContent->image_caption)
                                        <figcaption class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                            {{ $currentContent->image_caption }}
                                        </figcaption>
                                    @endif
                                </figure>
                            @endif

                            {{-- Contenuto testuale --}}
                            <div class="content-area">
                                {!! Str::markdown($currentContent->content) !!}
                            </div>

                            {{-- Immagine dopo il contenuto (se configurata) --}}
                            @if($currentContent->image_url && $currentContent->image_position === 'after')
                                <figure class="mt-8">
                                    <img src="{{ Storage::url($currentContent->image_url) }}" 
                                         alt="{{ $currentContent->image_caption ?? 'Immagine illustrativa' }}"
                                         class="w-full rounded-xl shadow-lg">
                                    @if($currentContent->image_caption)
                                        <figcaption class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                            {{ $currentContent->image_caption }}
                                        </figcaption>
                                    @endif
                                </figure>
                            @endif
                        </div>

                        {{-- Navigazione tra contenuti --}}
                        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                {{-- Bottone precedente --}}
                                <button wire:click="previousContent"
                                        class="flex items-center space-x-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
                                               hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors
                                               {{ $currentContentIndex == 0 && $subtopics->first()->id == $subtopicId ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $currentContentIndex == 0 && $subtopics->first()->id == $subtopicId ? 'disabled' : '' }}>
                                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                                    <span class="hidden sm:inline">Precedente</span>
                                </button>

                                {{-- Indicatore posizione --}}
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">{{ $currentContentIndex + 1 }}</span>
                                    di
                                    <span class="font-medium">{{ $contents->count() }}</span>
                                </div>

                                {{-- Bottone successivo --}}
                                <button wire:click="nextContent"
                                        class="flex items-center space-x-2 px-4 py-2 rounded-lg 
                                               {{ $currentContentIndex < $contents->count() - 1 || $subtopics->last()->id != $subtopicId
                                                  ? 'bg-primary-600 hover:bg-primary-700 text-white' 
                                                  : 'border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800' }}
                                               transition-colors">
                                    <span class="hidden sm:inline">
                                        {{ $currentContentIndex == $contents->count() - 1 && $subtopics->last()->id == $subtopicId 
                                           ? 'Completa argomento' 
                                           : 'Successivo' }}
                                    </span>
                                    <x-heroicon-o-arrow-right class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </article>
                @else
                    {{-- Empty state --}}
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Nessun contenuto selezionato
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Seleziona un contenuto dal menu laterale per iniziare lo studio.
                            </p>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>

    {{-- Custom styles --}}
    <style>
        .prose h1, .prose h2, .prose h3, .prose h4 {
            scroll-margin-top: 5rem;
        }
        
        .content-area ul {
            list-style-type: disc;
            padding-left: 1.5rem;
        }
        
        .content-area ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
        }
        
        .content-area strong {
            font-weight: 600;
            color: rgb(17 24 39);
        }
        
        .dark .content-area strong {
            color: rgb(243 244 246);
        }
        
        .content-area em {
            font-style: italic;
        }
        
        .content-area blockquote {
            border-left: 4px solid rgb(251 146 60);
            padding-left: 1rem;
            margin-left: 0;
            font-style: italic;
        }
        
        .dark .content-area blockquote {
            border-left-color: rgb(251 146 60);
        }

        /* Animazioni smooth per le transizioni */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .content-area {
            animation: slideIn 0.3s ease-out;
        }

        /* Miglioramenti tipografici */
        .prose {
            font-size: 1.125rem;
            line-height: 1.75;
        }

        .prose p {
            margin-bottom: 1.25rem;
        }

        .prose h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .prose h2 {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.875rem;
        }

        .prose h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        /* Miglioramento tabelle */
        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }

        .prose th {
            background-color: rgb(249 250 251);
            font-weight: 600;
            text-align: left;
            padding: 0.75rem;
            border: 1px solid rgb(229 231 235);
        }

        .dark .prose th {
            background-color: rgb(31 41 55);
            border-color: rgb(55 65 81);
        }

        .prose td {
            padding: 0.75rem;
            border: 1px solid rgb(229 231 235);
        }

        .dark .prose td {
            border-color: rgb(55 65 81);
        }

        /* Focus states per accessibilità */
        button:focus-visible {
            outline: 2px solid rgb(251 146 60);
            outline-offset: 2px;
        }

        /* Scrollbar personalizzata */
        aside::-webkit-scrollbar {
            width: 6px;
        }

        aside::-webkit-scrollbar-track {
            background: rgb(243 244 246);
        }

        .dark aside::-webkit-scrollbar-track {
            background: rgb(31 41 55);
        }

        aside::-webkit-scrollbar-thumb {
            background: rgb(156 163 175);
            border-radius: 3px;
        }

        aside::-webkit-scrollbar-thumb:hover {
            background: rgb(107 114 128);
        }

        /* Progress indicator animation */
        @keyframes progress {
            from {
                width: 0;
            }
        }

        .bg-primary-600 {
            animation: progress 0.5s ease-out;
        }
    </style>

    {{-- Script per gestione keyboard navigation --}}
    <script>
        document.addEventListener('keydown', function(e) {
            // Navigazione con frecce
            if (e.key === 'ArrowLeft' && !e.target.matches('input, textarea')) {
                @this.previousContent();
            } else if (e.key === 'ArrowRight' && !e.target.matches('input, textarea')) {
                @this.nextContent();
            }
            
            // Toggle menu con ESC
            if (e.key === 'Escape' && @this.isMenuOpen) {
                @this.toggleMenu();
            }
        });

        // Salva posizione di scroll quando si cambia contenuto
        let scrollPositions = {};
        
        window.addEventListener('beforeunload', function() {
            if (@this.contentId) {
                scrollPositions[@this.contentId] = window.scrollY;
                localStorage.setItem('theoryScrollPositions', JSON.stringify(scrollPositions));
            }
        });
        
        // Ripristina posizione di scroll
        Livewire.on('contentLoaded', function(contentId) {
            const saved = localStorage.getItem('theoryScrollPositions');
            if (saved) {
                scrollPositions = JSON.parse(saved);
                if (scrollPositions[contentId]) {
                    setTimeout(() => {
                        window.scrollTo(0, scrollPositions[contentId]);
                    }, 100);
                }
            }
        });
    </script>
</x-filament-panels::page>