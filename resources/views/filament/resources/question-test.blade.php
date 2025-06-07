{{-- resources/views/filament/resources/question-test.blade.php --}}
<div x-data="questionTest()" class="space-y-6 p-6">
    {{-- Simulazione interfaccia quiz --}}
    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            Simulazione Quiz
        </h3>
        
        {{-- Domanda --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 mb-4">
            <p class="text-gray-900 dark:text-white mb-4">
                {{ $question->text }}
            </p>
            
            @if($question->image_url)
                <div class="mb-4">
                    <img src="{{ Storage::url($question->image_url) }}" 
                         alt="Immagine domanda" 
                         class="rounded-lg shadow-md max-w-full h-auto"
                         style="max-height: 250px;">
                </div>
            @endif
            
            {{-- Bottoni risposta --}}
            <div class="grid grid-cols-2 gap-4">
                <button @click="selectAnswer(true)" 
                        :class="{
                            'ring-2 ring-green-500': selectedAnswer === true,
                            'bg-green-100 dark:bg-green-900/20': showResult && correctAnswer === true,
                            'bg-red-100 dark:bg-red-900/20': showResult && selectedAnswer === true && correctAnswer === false
                        }"
                        :disabled="showResult"
                        class="py-4 px-6 rounded-lg border-2 transition-all duration-200
                               hover:bg-gray-50 dark:hover:bg-gray-800
                               disabled:cursor-not-allowed"
                        :class="selectedAnswer === true ? 'border-green-500' : 'border-gray-300 dark:border-gray-600'">
                    <div class="flex items-center justify-center space-x-2">
                        <x-heroicon-o-check-circle class="w-6 h-6" 
                            :class="selectedAnswer === true ? 'text-green-600' : 'text-gray-400'" />
                        <span class="font-medium text-lg">VERO</span>
                    </div>
                </button>
                
                <button @click="selectAnswer(false)" 
                        :class="{
                            'ring-2 ring-red-500': selectedAnswer === false,
                            'bg-green-100 dark:bg-green-900/20': showResult && correctAnswer === false,
                            'bg-red-100 dark:bg-red-900/20': showResult && selectedAnswer === false && correctAnswer === true
                        }"
                        :disabled="showResult"
                        class="py-4 px-6 rounded-lg border-2 transition-all duration-200
                               hover:bg-gray-50 dark:hover:bg-gray-800
                               disabled:cursor-not-allowed"
                        :class="selectedAnswer === false ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                    <div class="flex items-center justify-center space-x-2">
                        <x-heroicon-o-x-circle class="w-6 h-6" 
                            :class="selectedAnswer === false ? 'text-red-600' : 'text-gray-400'" />
                        <span class="font-medium text-lg">FALSO</span>
                    </div>
                </button>
            </div>
        </div>
        
        {{-- Bottone conferma --}}
        <div class="flex justify-center" x-show="selectedAnswer !== null && !showResult">
            <button @click="checkAnswer()" 
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                Conferma Risposta
            </button>
        </div>
        
        {{-- Risultato --}}
        <div x-show="showResult" x-transition class="mt-4">
            <div :class="isCorrect ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'" 
                 class="rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <template x-if="isCorrect">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" />
                    </template>
                    <template x-if="!isCorrect">
                        <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" />
                    </template>
                    
                    <div class="flex-1">
                        <p class="font-medium" :class="isCorrect ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100'">
                            <span x-text="isCorrect ? 'Risposta corretta!' : 'Risposta errata!'"></span>
                        </p>
                        
                        @if($question->explanation)
                            <p class="mt-2 text-sm" :class="isCorrect ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
                                {{ $question->explanation }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Bottone reset --}}
            <div class="flex justify-center mt-4">
                <button @click="reset()" 
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 
                               text-gray-700 dark:text-gray-200 font-medium rounded-lg transition-colors">
                    Riprova
                </button>
            </div>
        </div>
    </div>
    
    {{-- Informazioni aggiuntive --}}
    <div class="text-sm text-gray-600 dark:text-gray-400">
        <p>Questa è una simulazione di come apparirà la domanda nel quiz.</p>
        <p class="mt-1">Risposta corretta: <span class="font-medium">{{ $question->correct_answer ? 'VERO' : 'FALSO' }}</span></p>
    </div>
</div>

<script>
function questionTest() {
    return {
        selectedAnswer: null,
        showResult: false,
        correctAnswer: {{ $question->correct_answer ? 'true' : 'false' }},
        isCorrect: false,
        
        selectAnswer(answer) {
            if (!this.showResult) {
                this.selectedAnswer = answer;
            }
        },
        
        checkAnswer() {
            if (this.selectedAnswer !== null) {
                this.isCorrect = this.selectedAnswer === this.correctAnswer;
                this.showResult = true;
            }
        },
        
        reset() {
            this.selectedAnswer = null;
            this.showResult = false;
            this.isCorrect = false;
        }
    }
}
</script>