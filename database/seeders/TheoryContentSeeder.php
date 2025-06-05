<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\TheoryContent;

class TheoryContentSeeder extends Seeder
{
    public function run(): void
    {
        $topic1 = Topic::where('code', '1')->first();

        if ($topic1) {
            $subtopic1_1 = Subtopic::updateOrCreate([
                'topic_id' => $topic1->id,
                'code' => '1.1',
            ], [
                'title' => 'Strada',
                'order' => 1,
            ]);

            TheoryContent::updateOrCreate([
                'code' => '1.1.1',
            ], [
                'topic_id' => $topic1->id,
                'subtopic_id' => $subtopic1_1->id,
                'content' => <<<EOT
La strada è un'area aperta alla circolazione dei pedoni, degli animali e dei veicoli.

Può essere a senso unico o a doppio senso di circolazione.
Può essere suddivisa in più carreggiate in presenza di uno spartitraffico.

Comprende:
* le carreggiate, riservate a veicoli ed animali
* le banchine
* i marciapiedi, riservati ai pedoni
* le piste ciclabili, riservate alle biciclette
EOT,
                'order' => 1,
            ]);
        }
    }
}
