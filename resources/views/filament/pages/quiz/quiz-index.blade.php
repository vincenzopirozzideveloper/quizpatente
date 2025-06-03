<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-filament::card>
            <div class="text-center p-8">
                <div class="text-6xl mb-4">🎯</div>
                <h3 class="text-xl font-semibold mb-2">Scheda Ministeriale</h3>
                <p class="text-gray-600 mb-4">40 domande come all'esame reale</p>
                <x-filament::button size="lg" wire:click="startMinisterialQuiz">
                    Inizia Quiz
                </x-filament::button>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-center p-8">
                <div class="text-6xl mb-4">📚</div>
                <h3 class="text-xl font-semibold mb-2">Quiz per Argomento</h3>
                <p class="text-gray-600 mb-4">Esercitati su temi specifici</p>
                <x-filament::button size="lg" wire:click="selectTopics">
                    Scegli Argomento
                </x-filament::button>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>