<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="flex justify-end mt-6">
            {{ $this->saveAction }}
        </div>
    </form>
</x-filament-panels::page>