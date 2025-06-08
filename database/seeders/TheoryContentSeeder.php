<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\TheoryContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TheoryContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Inizio importazione contenuti teorici...');
        
        $disk = Storage::disk('seeders');
        $publicDisk = Storage::disk('public');

        // Ottieni tutti i topics attivi
        $topics = Topic::active()->ordered()->get();
        
        foreach ($topics as $topic) {
            $this->command->info("Processing Topic {$topic->code}: {$topic->name}");
            
            // Costruisci il path del file di configurazione
            $filePath = "theory-content/topic-{$topic->code}.php";
            
            // Verifica se esiste il file di configurazione per questo topic
            if (!$disk->exists($filePath)) {
                $this->command->warn("  ⚠ File di configurazione non trovato: topic-{$topic->code}.php");
                continue;
            }
            
            // Carica i contenuti dal file
            $contents = require $disk->path($filePath);
            
            if (!is_array($contents) || empty($contents)) {
                $this->command->warn("  ⚠ Nessun contenuto trovato nel file topic-{$topic->code}.php");
                continue;
            }
            
            // Importa i contenuti
            $imported = 0;
            foreach ($contents as $content) {
                try {
                    // Gestione dell'immagine
                    $imageUrl = null;
                    if (isset($content['metadata']['image_file'])) {
                        $imageUrl = $this->processImage(
                            $content['metadata']['image_file'],
                            $disk,
                            $publicDisk,
                            $topic->code
                        );
                    }
                    
                    TheoryContent::updateOrCreate(
                        [
                            'topic_id' => $topic->id,
                            'code' => $content['code'],
                        ],
                        [
                            'title' => $content['title'],
                            'content' => $content['content'],
                            'order' => $content['order'] ?? 0,
                            'is_published' => $content['is_published'] ?? true,
                            'image_url' => $imageUrl,
                            'image_caption' => $content['image_caption'] ?? null,
                            'image_position' => $content['image_position'] ?? 'before',
                            'media' => $content['metadata'] ?? null, // Salva tutti i metadata
                        ]
                    );
                    $imported++;
                    
                    $this->command->info("  ✓ Importato: {$content['code']} - {$content['title']}");
                    
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Errore importazione contenuto {$content['code']}: " . $e->getMessage());
                }
            }
            
            $this->command->info("  ✓ Importati {$imported} contenuti per Topic {$topic->code}");
        }
        
        $this->command->info('✓ Importazione contenuti teorici completata!');
    }
    
    /**
     * Processa e copia l'immagine dalla directory del seeder alla directory pubblica
     *
     * @param string $imageFile Nome del file immagine
     * @param \Illuminate\Contracts\Filesystem\Filesystem $sourceDisk Disco sorgente
     * @param \Illuminate\Contracts\Filesystem\Filesystem $targetDisk Disco destinazione
     * @param string $topicCode Codice del topic per organizzazione
     * @return string|null Path dell'immagine salvata o null se non trovata
     */
    protected function processImage($imageFile, $sourceDisk, $targetDisk, $topicCode): ?string
    {
        $sourcePath = "images/{$imageFile}";
        
        // Verifica se l'immagine esiste nel disco sorgente
        if (!$sourceDisk->exists($sourcePath)) {
            $this->command->warn("    ⚠ Immagine non trovata: {$sourcePath}");
            return null;
        }
        
        // Costruisci il path di destinazione
        $targetDir = "theory-images/topic-{$topicCode}";
        $targetPath = "{$targetDir}/{$imageFile}";
        
        try {
            // Crea la directory se non esiste
            if (!$targetDisk->exists($targetDir)) {
                $targetDisk->makeDirectory($targetDir);
            }
            
            // Copia l'immagine
            $targetDisk->put(
                $targetPath,
                $sourceDisk->get($sourcePath)
            );
            
            $this->command->info("    ✓ Immagine copiata: {$imageFile}");
            
            return $targetPath;
            
        } catch (\Exception $e) {
            $this->command->error("    ✗ Errore copia immagine {$imageFile}: " . $e->getMessage());
            return null;
        }
    }
}