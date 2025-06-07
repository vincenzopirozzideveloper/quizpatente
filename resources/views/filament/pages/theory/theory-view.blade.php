<div>
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Sidebar con lista sottoargomenti --}}
        <div class="lg:w-1/4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $topic->name }}</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($subtopics as $subtopic)
                        <button
                            wire:click="loadSubtopic({{ $subtopic->id }})"
                            class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors 
                                   {{ $subtopicId == $subtopic->id ? 'bg-primary-50 dark:bg-primary-900/20 border-l-4 border-primary-500' : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $subtopic->code }}</span>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $subtopic->title }}</h4>
                                </div>
                                @if($subtopic->theoryContents->count() > 0)
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full">
                                        {{ $subtopic->theoryContents->count() }}
                                    </span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Contenuto principale --}}
        <div class="lg:w-3/4">
            @if($currentSubtopic && $contents->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    {{-- Tabs per i contenuti --}}
                    @if($contents->count() > 1)
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <div class="flex overflow-x-auto">
                                @foreach($contents as $content)
                                    <button
                                        wire:click="setActiveContent({{ $content->id }})"
                                        class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors
                                               {{ $activeContentId == $content->id 
                                                  ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' 
                                                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                    >
                                        {{ $content->code }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Contenuto attivo --}}
                    @php
                        $activeContent = $contents->firstWhere('id', $activeContentId);
                    @endphp

                    @if($activeContent)
                        <div class="p-6">
                            {{-- Titolo del contenuto --}}
                            <div class="mb-6">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $currentSubtopic->title }}
                                    @if($contents->count() > 1)
                                        <span class="text-lg text-gray-500 dark:text-gray-400 ml-2">{{ $activeContent->code }}</span>
                                    @endif
                                </h2>
                            </div>

                            {{-- Immagine prima del contenuto (se presente e posizione = before) --}}
                            @if($activeContent->image_url && $activeContent->image_position === 'before')
                                <div class="mb-6">
                                    <figure class="rounded-lg overflow-hidden">
                                        <img 
                                            src="{{ Storage::url($activeContent->image_url) }}" 
                                            alt="{{ $activeContent->image_caption ?: $currentSubtopic->title }}"
                                            class="w-full h-auto object-contain bg-gray-100 dark:bg-gray-900"
                                        >
                                        @if($activeContent->image_caption)
                                            <figcaption class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center italic">
                                                {{ $activeContent->image_caption }}
                                            </figcaption>
                                        @endif
                                    </figure>
                                </div>
                            @endif

                            {{-- Contenuto testuale --}}
                            <div class="prose prose-lg dark:prose-invert max-w-none">
                                {!! Str::markdown($activeContent->content) !!}
                            </div>

                            {{-- Immagine dopo il contenuto (se presente e posizione = after) --}}
                            @if($activeContent->image_url && $activeContent->image_position === 'after')
                                <div class="mt-6">
                                    <figure class="rounded-lg overflow-hidden">
                                        <img 
                                            src="{{ Storage::url($activeContent->image_url) }}" 
                                            alt="{{ $activeContent->image_caption ?: $currentSubtopic->title }}"
                                            class="w-full h-auto object-contain bg-gray-100 dark:bg-gray-900"
                                        >
                                        @if($activeContent->image_caption)
                                            <figcaption class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center italic">
                                                {{ $activeContent->image_caption }}
                                            </figcaption>
                                        @endif
                                    </figure>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @elseif($currentSubtopic)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <x-heroicon-o-document-text class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">
                        Nessun contenuto disponibile per {{ $currentSubtopic->title }}
                    </p>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <x-heroicon-o-academic-cap class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">
                        Seleziona un sottoargomento per visualizzare il contenuto
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>