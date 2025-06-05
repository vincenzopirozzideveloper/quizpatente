<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($topics as $topic)
            <x-filament::card>
                <a href="{{ route('filament.quizpatente.pages.theory-view', ['topicId' => $topic['id']]) }}" 
                   class="block hover:no-underline">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center text-2xl">
                            {{ $topic['icon'] }}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $topic['code'] }} - {{ $topic['name'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $topic['completed_questions'] }} su {{ $topic['total_questions'] }} completati
                            </p>
                            <div class="mt-2">
                                <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" 
                                         style="width: {{ $topic['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </x-filament::card>
        @endforeach
    </div>
</x-filament-panels::page>