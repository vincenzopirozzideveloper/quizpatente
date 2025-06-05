<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="lg:col-span-1">
            <x-filament::card>
                <h3 class="text-lg font-semibold mb-4">{{ $topic->code }} - {{ $topic->name }}</h3>
                
                <nav class="space-y-2">
                    @foreach($subtopics as $subtopic)
                        <button 
                            wire:click="loadSubtopic({{ $subtopic->id }})"
                            @class([
                                'w-full text-left px-3 py-2 rounded-lg transition-colors',
                                'bg-primary-600 text-white' => $subtopicId == $subtopic->id,
                                'hover:bg-gray-100 dark:hover:bg-gray-700' => $subtopicId != $subtopic->id,
                            ])
                        >
                            <div class="font-medium">{{ $subtopic->code }} {{ $subtopic->title }}</div>
                        </button>
                    @endforeach
                </nav>
            </x-filament::card>
        </div>

        {{-- Content Area --}}
        <div class="lg:col-span-3">
            @if($currentSubtopic)
                <x-filament::card>
                    <h2 class="text-2xl font-bold mb-6">
                        {{ $currentSubtopic->code }} - {{ $currentSubtopic->title }}
                    </h2>

                    {{-- Content Navigation --}}
                    @if($contents && $contents->count() > 1)
                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach($contents as $content)
                                <button 
                                    wire:click="setActiveContent({{ $content->id }})"
                                    @class([
                                        'px-4 py-2 rounded-lg transition-colors',
                                        'bg-primary-600 text-white' => $activeContentId == $content->id,
                                        'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' => $activeContentId != $content->id,
                                    ])
                                >
                                    {{ $content->code }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Content Display --}}
                    @if($contents)
                        @foreach($contents as $content)
                            <div 
                                @class([
                                    'prose dark:prose-invert max-w-none',
                                    'hidden' => $activeContentId != $content->id,
                                ])
                            >
                                <div class="content-body">
                                    {!! nl2br(e($content->content)) !!}
                                </div>

                                @if($content->media && count($content->media) > 0)
                                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($content->media as $media)
                                            <img 
                                                src="{{ $media['url'] }}" 
                                                alt="{{ $media['alt'] ?? '' }}"
                                                class="rounded-lg shadow-md"
                                            >
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </x-filament::card>
            @endif
        </div>
    </div>
</x-filament-panels::page>