<x-filament-panels::page>
    @php
        $stats = $this->getProgressStats();
    @endphp

    <div class="h-screen flex flex-col bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
        {{-- Header Glassmorphism --}}
        <div class="backdrop-blur-xl bg-white/70 dark:bg-gray-900/70 border-b border-white/20 dark:border-gray-800/20 shadow-lg">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <button wire:click="backToIndex" 
                            class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 transition-all duration-300 shadow-sm hover:shadow-md">
                            <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors" />
                        </button>
                        
                        <div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400 bg-clip-text text-transparent">
                                {{ $topic->name }}
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Esplora e apprendi al tuo ritmo
                            </p>
                        </div>
                    </div>
                    
                    {{-- Progress Ring --}}
                    <div class="flex items-center space-x-6">
                        <div class="relative">
                            <svg class="w-16 h-16 transform -rotate-90">
                                <circle cx="32" cy="32" r="28" stroke-width="4" 
                                    class="fill-none stroke-gray-200 dark:stroke-gray-700" />
                                <circle cx="32" cy="32" r="28" stroke-width="4" 
                                    stroke-dasharray="{{ 176 * $stats['percentage'] / 100 }} 176"
                                    class="fill-none stroke-blue-500 transition-all duration-500" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $stats['percentage'] }}%
                                </span>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $stats['read'] }} di {{ $stats['total'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                contenuti completati
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar moderna con animazioni --}}
            <div class="relative">
                <div class="{{ $isSidebarOpen ? 'w-96' : 'w-0' }} transition-all duration-300 ease-in-out overflow-hidden">
                    <div class="w-96 h-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-r border-gray-200/50 dark:border-gray-800/50 overflow-y-auto">
                        <div class="p-4 space-y-3">
                            @foreach ($subtopics as $subtopic)
                                <div class="group">
                                    {{-- Subtopic Card --}}
                                    <button wire:click="loadSubtopic({{ $subtopic->id }})" 
                                        class="w-full text-left p-4 rounded-2xl transition-all duration-300
                                            {{ $subtopicId == $subtopic->id 
                                                ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg scale-[1.02]' 
                                                : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750 shadow-sm hover:shadow-md' }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs font-mono opacity-70">{{ $subtopic->code }}</span>
                                                    <span class="text-sm font-semibold">{{ $subtopic->title }}</span>
                                                </div>
                                                @if ($subtopic->description)
                                                    <p class="text-xs mt-1 opacity-80">{{ Str::limit($subtopic->description, 60) }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @php
                                                    $subtopicContents = $subtopic->theoryContents()->published()->pluck('id');
                                                    $readCount = $userProgress->whereIn('theory_content_id', $subtopicContents)->where('status', 'read')->count();
                                                    $totalCount = $subtopic->theory_contents_count;
                                                @endphp
                                                <span class="text-xs {{ $subtopicId == $subtopic->id ? 'text-white/80' : 'text-gray-500' }}">
                                                    {{ $readCount }}/{{ $totalCount }}
                                                </span>
                                                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-300
                                                    {{ $subtopicId == $subtopic->id ? 'rotate-180' : '' }}" />
                                            </div>
                                        </div>
                                    </button>

                                    {{-- Content List with Animation --}}
                                    @if ($subtopicId == $subtopic->id && $contents->isNotEmpty())
                                        <div class="mt-3 space-y-2 animate-fade-in">
                                            @foreach ($contents as $content)
                                                @php
                                                    $status = $this->getContentStatus($content->id);
                                                    $isActive = $contentId == $content->id;
                                                @endphp
                                                
                                                <div class="ml-4 group/content">
                                                    <div class="flex items-start space-x-3 p-3 rounded-xl transition-all duration-300
                                                        {{ $isActive 
                                                            ? 'bg-gradient-to-r from-blue-100 to-purple-100 dark:from-blue-900/30 dark:to-purple-900/30 shadow-md scale-[1.02]' 
                                                            : 'hover:bg-gray-100 dark:hover:bg-gray-800/50' }}">
                                                        
                                                        {{-- Status Icon with Animation --}}
                                                        <button wire:click="toggleContentReadStatus({{ $content->id }})"
                                                            class="flex-shrink-0 mt-0.5 transition-all duration-300 hover:scale-110">
                                                            @if ($status === 'read')
                                                                <div class="relative">
                                                                    <x-heroicon-s-check-circle class="w-6 h-6 text-green-500" />
                                                                    <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-30"></div>
                                                                </div>
                                                            @elseif ($status === 'reading')
                                                                <div class="relative">
                                                                    <x-heroicon-o-clock class="w-6 h-6 text-amber-500 animate-pulse" />
                                                                </div>
                                                            @else
                                                                <x-heroicon-s-check-circle class="w-6 h-6 text-gray-300 hover:text-gray-500 transition-colors" />
                                                            @endif
                                                        </button>

                                                        {{-- Content Info --}}
                                                        <button wire:click="navigateToContent({{ $content->id }})" 
                                                            class="flex-1 text-left">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="text-xs font-mono text-gray-500 dark:text-gray-400">
                                                                    {{ $content->code }}
                                                                </span>
                                                                @if ($content->image_url)
                                                                    <x-heroicon-s-check-circle class="w-4 h-4 text-gray-400" />
                                                                @endif
                                                            </div>
                                                            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2 mt-1">
                                                                {{ Str::limit(strip_tags($content->content), 80) }}
                                                            </p>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Sidebar Toggle Button --}}
                <button wire:click="toggleSidebar"
                    class="absolute top-1/2 -right-5 transform -translate-y-1/2 z-10 
                           bg-white dark:bg-gray-800 rounded-full p-2 shadow-lg 
                           hover:shadow-xl transition-all duration-300 hover:scale-110">
                    <x-heroicon-o-chevron-left class="w-5 h-5 text-gray-600 dark:text-gray-400 transition-transform duration-300
                        {{ !$isSidebarOpen ? 'rotate-180' : '' }}" />
                </button>
            </div>

            {{-- Main Content Area --}}
            <div class="flex-1 flex flex-col bg-white/60 dark:bg-gray-900/60 backdrop-blur-sm">
                @if ($currentContent)
                    {{-- Content Header --}}
                    <div class="bg-gradient-to-r from-white/80 to-gray-50/80 dark:from-gray-900/80 dark:to-gray-800/80 backdrop-blur-md border-b border-gray-200/50 dark:border-gray-800/50">
                        <div class="px-8 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-2 text-sm">
                                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full font-medium">
                                            {{ $currentSubtopic->code }}
                                        </span>
                                        <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                                        <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full font-medium">
                                            {{ $currentContent->code }}
                                        </span>
                                    </div>
                                </div>
                                
                                <button wire:click="toggleContentReadStatus({{ $currentContent->id }})"
                                    class="group flex items-center space-x-2 px-4 py-2 rounded-full transition-all duration-300
                                        {{ $this->getContentStatus($currentContent->id) === 'read' 
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' 
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                                    @if ($this->getContentStatus($currentContent->id) === 'read')
                                        <x-heroicon-s-check-circle class="w-5 h-5" />
                                        <span class="text-sm font-medium">Completato</span>
                                    @else
                                        <x-heroicon-o-check-circle class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                        <span class="text-sm font-medium">Segna come letto</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Content Body with Beautiful Typography --}}
                    <div class="flex-1 overflow-y-auto">
                        <div class="max-w-5xl mx-auto px-8 py-12">
                            {{-- Image Before Content --}}
                            @if ($currentContent->image_url && $currentContent->image_position === 'before')
                                <div class="mb-12 group">
                                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                                        <img src="{{ Storage::url($currentContent->image_url) }}" 
                                             alt="{{ $currentContent->image_caption ?? '' }}"
                                             class="w-full object-cover transform transition-transform duration-500 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </div>
                                    @if ($currentContent->image_caption)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-4 italic">
                                            {{ $currentContent->image_caption }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            {{-- Main Content with Enhanced Typography --}}
                            <article class="prose prose-xl prose-blue dark:prose-invert max-w-none
                                          prose-headings:font-bold prose-headings:tracking-tight
                                          prose-h1:text-4xl prose-h2:text-3xl prose-h3:text-2xl
                                          prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-p:leading-relaxed
                                          prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline
                                          prose-strong:text-gray-900 dark:prose-strong:text-white
                                          prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-2 prose-code:py-1 prose-code:rounded
                                          prose-pre:bg-gray-900 dark:prose-pre:bg-gray-950 prose-pre:shadow-xl
                                          prose-ul:list-disc prose-ol:list-decimal
                                          prose-li:marker:text-blue-500 dark:prose-li:marker:text-blue-400">
                                {!! Str::markdown($currentContent->content) !!}
                            </article>

                            {{-- Image After Content --}}
                            @if ($currentContent->image_url && $currentContent->image_position === 'after')
                                <div class="mt-12 group">
                                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                                        <img src="{{ Storage::url($currentContent->image_url) }}" 
                                             alt="{{ $currentContent->image_caption ?? '' }}"
                                             class="w-full object-cover transform transition-transform duration-500 group-hover:scale-105">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </div>
                                    @if ($currentContent->image_caption)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mt-4 italic">
                                            {{ $currentContent->image_caption }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Modern Navigation Footer --}}
                    <div class="bg-gradient-to-t from-white to-gray-50/80 dark:from-gray-900 dark:to-gray-800/80 backdrop-blur-md border-t border-gray-200/50 dark:border-gray-800/50">
                        <div class="px-8 py-6">
                            <div class="flex items-center justify-between max-w-5xl mx-auto">
                                <button wire:click="previousContent"
                                    class="group flex items-center space-x-3 px-6 py-3 rounded-xl
                                           bg-white dark:bg-gray-800 shadow-md hover:shadow-xl
                                           transform transition-all duration-300 hover:scale-105
                                           disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                                    {{ $currentContentIndex == 0 && $subtopics->first()->id == $subtopicId ? 'disabled' : '' }}>
                                    <x-heroicon-o-arrow-left class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                        Precedente
                                    </span>
                                </button>

                                <div class="flex items-center space-x-4">
                                    <div class="flex space-x-1">
                                        @for ($i = 0; $i < min(5, $contents->count()); $i++)
                                            <div class="w-2 h-2 rounded-full transition-all duration-300
                                                {{ $i === $currentContentIndex 
                                                    ? 'w-8 bg-gradient-to-r from-blue-500 to-purple-500' 
                                                    : ($i < $currentContentIndex ? 'bg-green-400' : 'bg-gray-300 dark:bg-gray-600') }}">
                                            </div>
                                        @endfor
                                        @if ($contents->count() > 5)
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                                +{{ $contents->count() - 5 }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        {{ $currentContentIndex + 1 }} di {{ $contents->count() }}
                                    </span>
                                </div>

                                <button wire:click="nextContent"
                                    class="group flex items-center space-x-3 px-6 py-3 rounded-xl
                                           bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700
                                           text-white shadow-md hover:shadow-xl
                                           transform transition-all duration-300 hover:scale-105">
                                    <span class="font-medium">Successivo</span>
                                    <x-heroicon-o-arrow-right class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Empty State with Modern Design --}}
                    <div class="flex-1 flex items-center justify-center p-8">
                        <div class="text-center max-w-md">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full blur-3xl opacity-20 animate-pulse"></div>
                                <x-heroicon-o-book-open class="w-24 h-24 text-gray-300 dark:text-gray-600 mx-auto mb-6 relative" />
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                                Inizia il tuo percorso di apprendimento
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">
                                Seleziona un contenuto dalla barra laterale per iniziare a esplorare questo argomento.
                            </p>
                            <button wire:click="toggleSidebar" 
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700
                                       text-white font-medium rounded-xl shadow-md hover:shadow-xl
                                       transform transition-all duration-300 hover:scale-105">
                                Mostra contenuti
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #2563eb, #7c3aed);
        }

        /* Smooth scroll behavior */
        * {
            scroll-behavior: smooth;
        }

        /* Enhanced focus states */
        button:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Dark mode enhancements */
        @media (prefers-color-scheme: dark) {
            ::-webkit-scrollbar-thumb {
                background: linear-gradient(180deg, #1e40af, #5b21b6);
            }
        }
    </style>

    <script>
        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.matches('input, textarea')) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    @this.previousContent();
                    break;
                case 'ArrowRight':
                    @this.nextContent();
                    break;
                case ' ':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        @this.toggleContentReadStatus(@this.currentContent?.id);
                    }
                    break;
                case 'b':
                    if (e.ctrlKey) {
                        e.preventDefault();
                        @this.toggleSidebar();
                    }
                    break;
            }
        });

        // Progress animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressCircle = document.querySelector('circle:last-child');
            if (progressCircle) {
                progressCircle.style.strokeDasharray = '0 176';
                setTimeout(() => {
                    progressCircle.style.strokeDasharray = progressCircle.getAttribute('stroke-dasharray');
                }, 100);
            }
        });
    </script>
</x-filament-panels::page>