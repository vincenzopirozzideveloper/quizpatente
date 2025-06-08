<x-filament-panels::page>
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Quiz Completati --}}
        <x-filament::card>
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-academic-cap class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Quiz completati</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_quizzes'] }}</p>
                </div>
            </div>
        </x-filament::card>

        {{-- Tasso di Successo --}}
        <x-filament::card>
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tasso successo</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['success_rate'] }}%</p>
                </div>
            </div>
        </x-filament::card>

        {{-- Errori da Ripassare --}}
        <x-filament::card>
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Errori da ripassare</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['errors_to_review'] }}</p>
                </div>
            </div>
        </x-filament::card>

        {{-- Streak Giorni --}}
        <x-filament::card>
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-fire class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Streak giorni</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $streakDays }}</p>
                </div>
            </div>
        </x-filament::card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Azioni Rapide --}}
        <div>
            <x-filament::card>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Azioni rapide</h3>
                </div>
                <div class="space-y-3">
                    <x-filament::button 
                        wire:click="startQuiz"
                        color="primary"
                        class="w-full"
                        icon="heroicon-o-play"
                    >
                        Inizia un Quiz
                    </x-filament::button>
                    
                    <x-filament::button 
                    wire:click="startQuiz"
                    color="danger"
                    class="w-full"
                    icon="heroicon-o-arrow-path"
                    outlined
                >
                    Ripassa errori ({{ $stats['errors_to_review'] }})
                </x-filament::button>
                </div>
            </x-filament::card>
        </div>
    </div>

    {{-- Errori Frequenti --}}
    @if($recentErrors->count() > 0)
        <x-filament::card class="mt-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Errori più frequenti</h3>
            </div>
            <div class="space-y-3">
                @foreach($recentErrors as $error)
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">
                                {{ Str::limit($error->question->text, 80) }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                {{ $error->question->topic->name }} • Sbagliata {{ $error->error_count }} volt{{ $error->error_count > 1 ? 'e' : 'a' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 rounded-full font-semibold">
                                {{ $error->error_count }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::card>
    @endif
</x-filament-panels::page>