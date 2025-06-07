{{-- resources/views/filament/resources/question-import-form.blade.php --}}
<div class="space-y-4">
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <div class="flex">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Formato CSV richiesto
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>Il file CSV deve contenere le seguenti colonne:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li><strong>topic_code</strong>: Codice dell'argomento (es: 1, 2, 3)</li>
                        <li><strong>subtopic_code</strong>: Codice del sottoargomento (es: 1.1, 1.2)</li>
                        <li><strong>theory_code</strong>: Codice del contenuto teorico (es: 1.1.1, 1.1.2)</li>
                        <li><strong>text</strong>: Testo della domanda</li>
                        <li><strong>correct_answer</strong>: V o F (Vero o Falso)</li>
                        <li><strong>explanation</strong>: Spiegazione della risposta (opzionale)</li>
                        <li><strong>difficulty</strong>: 1, 2 o 3 (Facile, Medio, Difficile)</li>
                        <li><strong>ministerial_number</strong>: Numero ministeriale (opzionale, es: D001)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div>
        {{ $this->getFormSchema() }}
    </div>
    
    <div class="border-t pt-4">
        <a href="#" class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
            <div class="flex items-center space-x-1">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                <span>Scarica template CSV di esempio</span>
            </div>
        </a>
    </div>
</div>