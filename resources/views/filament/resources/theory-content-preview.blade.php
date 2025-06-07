{{-- resources/views/filament/resources/theory-content-preview.blade.php --}}
<div class="max-w-4xl mx-auto p-6">
    {{-- Header informazioni --}}
    <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Argomento</h3>
                <p class="text-gray-900 dark:text-white font-medium">{{ $record->topic->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Sottoargomento</h3>
                <p class="text-gray-900 dark:text-white font-medium">{{ $record->subtopic->title }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Codice</h3>
                <p class="text-gray-900 dark:text-white font-medium">{{ $record->code }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Stato</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                             {{ $record->is_published ? 'bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-400' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/20 dark:text-warning-400' }}">
                    {{ $record->is_published ? 'Pubblicato' : 'Bozza' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Contenuto principale --}}
    <div class="prose prose-lg dark:prose-invert max-w-none">
        {{-- Immagine prima del contenuto --}}
        @if($record->image_url && $record->image_position === 'before')
            <figure class="mb-8">
                <img src="{{ Storage::url($record->image_url) }}" 
                     alt="{{ $record->image_caption ?? 'Immagine illustrativa' }}"
                     class="w-full rounded-xl shadow-lg">
                @if($record->image_caption)
                    <figcaption class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400">
                        {{ $record->image_caption }}
                    </figcaption>
                @endif
            </figure>
        @endif

        {{-- Contenuto testuale --}}
        <div class="content-preview">
            {!! Str::markdown($record->content) !!}
        </div>

        {{-- Immagine dopo il contenuto --}}
        @if($record->image_url && $record->image_position === 'after')
            <figure class="mt-8">
                <img src="{{ Storage::url($record->image_url) }}" 
                     alt="{{ $record->image_caption ?? 'Immagine illustrativa' }}"
                     class="w-full rounded-xl shadow-lg">
                @if($record->image_caption)
                    <figcaption class="mt-3 text-center text-sm text-gray-600 dark:text-gray-400">
                        {{ $record->image_caption }}
                    </figcaption>
                @endif
            </figure>
        @endif
    </div>

    {{-- Footer con metadati --}}
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <div>
                <span>Ordine: </span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $record->order }}</span>
            </div>
            <div>
                <span>Ultima modifica: </span>
                <span class="font-medium text-gray-900 dark:text-white">
                    {{ $record->updated_at->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    .content-preview h1, 
    .content-preview h2, 
    .content-preview h3, 
    .content-preview h4 {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }
    
    .content-preview ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin: 0.75rem 0;
    }
    
    .content-preview ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin: 0.75rem 0;
    }
    
    .content-preview p {
        margin: 0.75rem 0;
    }
    
    .content-preview blockquote {
        border-left: 4px solid rgb(251 146 60);
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
    }
</style>