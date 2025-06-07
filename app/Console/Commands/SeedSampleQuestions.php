<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\TheoryContent;

class SeedSampleQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questions:seed-samples';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera domande di esempio per testare il sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generazione domande di esempio...');
        
        // Trova il primo topic con subtopics e theory contents
        $topic = Topic::with(['subtopics.theoryContents'])->first();
        
        if (!$topic) {
            $this->error('Nessun argomento trovato. Assicurati di aver eseguito i seeder base.');
            return 1;
        }
        
        $questions = [
            [
                'text' => 'La strada è un\'area aperta alla circolazione dei pedoni, degli animali e dei veicoli.',
                'correct_answer' => true,
                'explanation' => 'La definizione è corretta secondo il Codice della Strada.',
                'difficulty_level' => 1,
            ],
            [
                'text' => 'La strada può essere solo a senso unico di circolazione.',
                'correct_answer' => false,
                'explanation' => 'Falso. La strada può essere sia a senso unico che a doppio senso di circolazione.',
                'difficulty_level' => 1,
            ],
            [
                'text' => 'La carreggiata è riservata esclusivamente ai veicoli a motore.',
                'correct_answer' => false,
                'explanation' => 'Falso. La carreggiata è riservata a veicoli e animali, non solo ai veicoli a motore.',
                'difficulty_level' => 2,
            ],
            [
                'text' => 'I marciapiedi sono riservati ai pedoni.',
                'correct_answer' => true,
                'explanation' => 'Vero. I marciapiedi sono aree della strada specificamente riservate alla circolazione dei pedoni.',
                'difficulty_level' => 1,
            ],
            [
                'text' => 'Le piste ciclabili possono essere utilizzate anche dai motocicli.',
                'correct_answer' => false,
                'explanation' => 'Falso. Le piste ciclabili sono riservate esclusivamente alle biciclette.',
                'difficulty_level' => 2,
            ],
            [
                'text' => 'Lo spartitraffico serve a suddividere la strada in più carreggiate.',
                'correct_answer' => true,
                'explanation' => 'Vero. Lo spartitraffico è l\'elemento che divide fisicamente le carreggiate.',
                'difficulty_level' => 2,
            ],
            [
                'text' => 'Le banchine fanno parte della strada.',
                'correct_answer' => true,
                'explanation' => 'Vero. Le banchine sono parte integrante della struttura stradale.',
                'difficulty_level' => 1,
            ],
            [
                'text' => 'In una strada a doppio senso di circolazione è sempre presente uno spartitraffico.',
                'correct_answer' => false,
                'explanation' => 'Falso. Lo spartitraffico non è sempre presente nelle strade a doppio senso.',
                'difficulty_level' => 3,
            ],
        ];
        
        $bar = $this->output->createProgressBar(count($questions));
        $bar->start();
        
        $created = 0;
        $ministerialNumber = 1;
        
        foreach ($topic->subtopics as $subtopic) {
            foreach ($subtopic->theoryContents as $theoryContent) {
                foreach ($questions as $index => $questionData) {
                    // Crea la domanda collegandola al contenuto teorico corrente
                    $question = Question::create([
                        'topic_id' => $topic->id,
                        'subtopic_id' => $subtopic->id,
                        'theory_content_id' => $theoryContent->id,
                        'text' => $questionData['text'],
                        'correct_answer' => $questionData['correct_answer'],
                        'explanation' => $questionData['explanation'],
                        'difficulty_level' => $questionData['difficulty_level'],
                        'is_ministerial' => true,
                        'ministerial_number' => 'D' . str_pad($ministerialNumber++, 3, '0', STR_PAD_LEFT),
                        'is_active' => true,
                        'order' => $index + 1,
                    ]);
                    
                    $created++;
                    $bar->advance();
                    
                    // Limita a creare solo alcune domande per non sovraccaricare
                    if ($created >= 10) {
                        break 3;
                    }
                }
            }
        }
        
        $bar->finish();
        $this->newLine();
        
        // Aggiorna il conteggio delle domande nel topic
        $topic->total_questions = $topic->questions()->count();
        $topic->save();
        
        $this->info("Create {$created} domande di esempio!");
        $this->info('Puoi ora gestire le domande dalla sezione "Domande Quiz" in Filament.');
        
        return 0;
    }
}