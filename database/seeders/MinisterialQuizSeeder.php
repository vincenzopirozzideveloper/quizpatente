<?php
// database/seeders/MinisterialQuizSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MinisterialQuiz;
use App\Models\Question;

class MinisterialQuizSeeder extends Seeder
{
    public function run(): void
    {
        // Assicurati di avere almeno 30 domande
        $questions = Question::active()
            ->inRandomOrder()
            ->limit(30)
            ->get();
            
        if ($questions->count() < 30) {
            $this->command->warn('Non ci sono abbastanza domande per creare quiz ministeriali di esempio.');
            return;
        }

        // Crea alcuni quiz ministeriali di esempio
        for ($i = 1; $i <= 5; $i++) {
            $quiz = MinisterialQuiz::create([
                'name' => "Quiz Ministeriale #{$i}",
                'description' => "Quiz di esempio numero {$i} per testare il sistema",
                'order' => $i,
                'is_active' => true,
                'max_errors' => 3,
            ]);

            // Seleziona 30 domande casuali
            $selectedQuestions = $questions->random(30);
            
            foreach ($selectedQuestions as $index => $question) {
                $quiz->questions()->attach($question->id, [
                    'order' => $index + 1
                ]);
            }
        }

        $this->command->info('Quiz ministeriali di esempio creati con successo!');
    }
}