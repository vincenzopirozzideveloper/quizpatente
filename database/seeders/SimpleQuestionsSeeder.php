<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\TheoryContent;

class SimpleQuestionsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Trova il primo topic con subtopics e theory contents
        $topic = Topic::with(['subtopics.theoryContents'])->first();
        
        if (!$topic) {
            $this->command->error('Nessun topic trovato. Esegui prima i seeder base.');
            return;
        }
        
        $subtopic = $topic->subtopics->first();
        $theoryContent = $subtopic->theoryContents->first();
        
        $questions = [
            // DEFINIZIONI STRADALI (10 domande)
            ['text' => 'La strada è un\'area aperta alla circolazione dei pedoni, degli animali e dei veicoli.', 'correct_answer' => true, 'explanation' => 'Definizione corretta secondo il Codice della Strada.', 'difficulty_level' => 1],
            ['text' => 'La strada può essere solo a senso unico di circolazione.', 'correct_answer' => false, 'explanation' => 'La strada può essere sia a senso unico che a doppio senso.', 'difficulty_level' => 1],
            ['text' => 'La carreggiata è la parte della strada destinata alla circolazione dei veicoli.', 'correct_answer' => true, 'explanation' => 'La carreggiata è destinata ai veicoli.', 'difficulty_level' => 1],
            ['text' => 'Le banchine non fanno parte della strada.', 'correct_answer' => false, 'explanation' => 'Le banchine sono parte integrante della strada.', 'difficulty_level' => 1],
            ['text' => 'Il marciapiede è riservato ai pedoni.', 'correct_answer' => true, 'explanation' => 'I marciapiedi sono aree riservate ai pedoni.', 'difficulty_level' => 1],
            ['text' => 'La corsia è una parte della carreggiata che consente la circolazione di una sola fila di veicoli.', 'correct_answer' => true, 'explanation' => 'Definizione corretta di corsia stradale.', 'difficulty_level' => 2],
            ['text' => 'Lo spartitraffico può essere solo in cemento.', 'correct_answer' => false, 'explanation' => 'Lo spartitraffico può essere realizzato in vari materiali.', 'difficulty_level' => 2],
            ['text' => 'Le piste ciclabili sono parte della carreggiata.', 'correct_answer' => false, 'explanation' => 'Le piste ciclabili sono separate dalla carreggiata.', 'difficulty_level' => 2],
            ['text' => 'L\'isola di traffico serve a canalizzare le correnti veicolari.', 'correct_answer' => true, 'explanation' => 'Le isole di traffico guidano il flusso veicolare.', 'difficulty_level' => 2],
            ['text' => 'La strada extraurbana può avere marciapiedi.', 'correct_answer' => false, 'explanation' => 'Le strade extraurbane generalmente non hanno marciapiedi.', 'difficulty_level' => 2],
            
            // SEGNALETICA STRADALE (10 domande)
            ['text' => 'I segnali stradali si dividono in verticali e orizzontali.', 'correct_answer' => true, 'explanation' => 'Classificazione base della segnaletica.', 'difficulty_level' => 1],
            ['text' => 'I segnali di pericolo hanno forma triangolare.', 'correct_answer' => true, 'explanation' => 'I segnali di pericolo sono triangolari con bordo rosso.', 'difficulty_level' => 1],
            ['text' => 'I segnali di divieto sono sempre rotondi con bordo rosso.', 'correct_answer' => true, 'explanation' => 'Caratteristica standard dei segnali di divieto.', 'difficulty_level' => 1],
            ['text' => 'I segnali di obbligo hanno sfondo blu.', 'correct_answer' => true, 'explanation' => 'I segnali di obbligo sono circolari con sfondo blu.', 'difficulty_level' => 1],
            ['text' => 'La segnaletica orizzontale può essere solo bianca.', 'correct_answer' => false, 'explanation' => 'Può essere bianca, gialla o blu.', 'difficulty_level' => 2],
            ['text' => 'Le strisce pedonali fanno parte della segnaletica orizzontale.', 'correct_answer' => true, 'explanation' => 'Le strisce pedonali sono segnaletica orizzontale.', 'difficulty_level' => 1],
            ['text' => 'I segnali luminosi prevalgono su quelli verticali.', 'correct_answer' => true, 'explanation' => 'I semafori hanno precedenza sui segnali verticali.', 'difficulty_level' => 2],
            ['text' => 'La segnaletica temporanea ha sempre sfondo giallo.', 'correct_answer' => true, 'explanation' => 'I segnali temporanei hanno sfondo giallo per maggiore visibilità.', 'difficulty_level' => 2],
            ['text' => 'I pannelli integrativi possono modificare il significato dei segnali.', 'correct_answer' => true, 'explanation' => 'I pannelli integrativi specificano o limitano il segnale principale.', 'difficulty_level' => 3],
            ['text' => 'I delineatori di margine sono obbligatori su tutte le strade.', 'correct_answer' => false, 'explanation' => 'Non sono obbligatori su tutte le tipologie di strada.', 'difficulty_level' => 3],
            
            // NORME DI COMPORTAMENTO (10 domande)
            ['text' => 'È obbligatorio dare la precedenza a destra agli incroci non segnalati.', 'correct_answer' => true, 'explanation' => 'Regola base della precedenza.', 'difficulty_level' => 1],
            ['text' => 'Il sorpasso a destra è sempre vietato.', 'correct_answer' => false, 'explanation' => 'È consentito in alcuni casi specifici.', 'difficulty_level' => 2],
            ['text' => 'La distanza di sicurezza dipende dalla velocità.', 'correct_answer' => true, 'explanation' => 'Maggiore è la velocità, maggiore deve essere la distanza.', 'difficulty_level' => 1],
            ['text' => 'È vietato sostare sulle strisce pedonali.', 'correct_answer' => true, 'explanation' => 'La sosta sulle strisce pedonali è sempre vietata.', 'difficulty_level' => 1],
            ['text' => 'L\'uso delle cinture di sicurezza è obbligatorio solo per il conducente.', 'correct_answer' => false, 'explanation' => 'È obbligatorio per tutti gli occupanti del veicolo.', 'difficulty_level' => 1],
            ['text' => 'In autostrada la velocità minima è 60 km/h.', 'correct_answer' => false, 'explanation' => 'La velocità minima in autostrada è 80 km/h sulla corsia più a destra.', 'difficulty_level' => 2],
            ['text' => 'È consentito l\'uso del cellulare con auricolare durante la guida.', 'correct_answer' => true, 'explanation' => 'È consentito con dispositivi vivavoce o auricolari.', 'difficulty_level' => 2],
            ['text' => 'La retromarcia è vietata in autostrada.', 'correct_answer' => true, 'explanation' => 'Manovra estremamente pericolosa e vietata.', 'difficulty_level' => 1],
            ['text' => 'Il casco è obbligatorio per tutti i motociclisti.', 'correct_answer' => true, 'explanation' => 'Obbligo per conducente e passeggero di motocicli.', 'difficulty_level' => 1],
            ['text' => 'È consentito trasportare bambini sul sedile anteriore.', 'correct_answer' => true, 'explanation' => 'È consentito con appositi sistemi di ritenuta.', 'difficulty_level' => 3],
        ];
        
        $this->command->info('Inserimento di 30 domande di esempio...');
        
        $ministerialNumber = 1;
        foreach ($questions as $index => $questionData) {
            Question::create([
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
        }
        
        // Aggiorna conteggio domande nel topic
        $topic->total_questions = $topic->questions()->count();
        $topic->save();
        
        $this->command->info('✓ Create 30 domande di esempio con successo!');
    }
}