{{-- File: resources/views/filament/resources/theory-content-preview.blade.php --}}

<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ $record->subtopic->title }}
            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $record->code }}</span>
        </h3>
    </div>

    {{-- Immagine prima del contenuto (se presente e posizione = before) --}}
    @if($record->image_url && $record->image_position === 'before')
        <div class="mb-6">
            <figure class="rounded-lg overflow-hidden">
                <img 
                    src="{{ Storage::url($record->image_url) }}" 
                    alt="{{ $record->image_caption ?: $record->subtopic->title }}"
                    class="w-full h-auto object-contain bg-gray-100 dark:bg-gray-900"
                >
                @if($record->image_caption)
                    <figcaption class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center italic">
                        {{ $record->image_caption }}
                    </figcaption>
                @endif
            </figure>
        </div>
    @endif

    {{-- Contenuto testuale --}}
    <div class="prose prose-lg dark:prose-invert max-w-none">
        {!! Str::markdown($record->content) !!}
    </div>

    {{-- Immagine dopo il contenuto (se presente e posizione = after) --}}
    @if($record->image_url && $record->image_position === 'after')
        <div class="mt-6">
            <figure class="rounded-lg overflow-hidden">
                <img 
                    src="{{ Storage::url($record->image_url) }}" 
                    alt="{{ $record->image_caption ?: $record->subtopic->title }}"
                    class="w-full h-auto object-contain bg-gray-100 dark:bg-gray-900"
                >
                @if($record->image_caption)
                    <figcaption class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center italic">
                        {{ $record->image_caption }}
                    </figcaption>
                @endif
            </figure>
        </div>
    @endif
    
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Stato</dt>
                <dd class="mt-1">
                    @if($record->is_published)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Pubblicato
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                            Bozza
                        </span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500 dark:text-gray-400">Ultima modifica</dt>
                <dd class="mt-1 text-gray-900 dark:text-white">{{ $record->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>