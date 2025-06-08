<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            [
                'code' => '1',
                'name' => "Definizioni generali e doveri nell'uso della strada",
                'icon' => 'heroicon-o-book-open',
                'order' => 1
            ],
            [
                'code' => '2',
                'name' => 'Segnali di pericolo',
                'icon' => 'heroicon-o-exclamation-triangle',
                'order' => 2
            ],
            [
                'code' => '3',
                'name' => 'Segnali stradali di divieto',
                'icon' => 'heroicon-o-no-symbol',
                'order' => 3
            ],
            [
                'code' => '4',
                'name' => 'Segnali stradali di obbligo',
                'icon' => 'heroicon-o-check-circle',
                'order' => 4
            ],
            [
                'code' => '5',
                'name' => 'Segnali stradali di precedenza',
                'icon' => 'heroicon-o-arrow-right-circle',
                'order' => 5
            ],
            [
                'code' => '6',
                'name' => 'Segnaletica orizzontale e segni sugli ostacoli',
                'icon' => 'heroicon-o-minus',
                'order' => 6
            ],
            [
                'code' => '7',
                'name' => 'Segnalazioni semaforiche e degli agenti del traffico',
                'icon' => 'heroicon-o-light-bulb',
                'order' => 7
            ],
            [
                'code' => '8',
                'name' => 'Segnali di indicazione',
                'icon' => 'heroicon-o-information-circle',
                'order' => 8
            ],
            [
                'code' => '9',
                'name' => 'Pannelli integrativi dei segnali',
                'icon' => 'heroicon-o-squares-plus',
                'order' => 9
            ],
            [
                'code' => '10',
                'name' => 'Segnali complementari, segnali temporanei e di cantiere',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'order' => 10
            ],
            [
                'code' => '11',
                'name' => 'Limiti di velocità, pericolo e di intralcio alla circolazione',
                'icon' => 'heroicon-o-clock',
                'order' => 11
            ],
            [
                'code' => '12',
                'name' => 'Distanza di sicurezza',
                'icon' => 'heroicon-o-arrows-right-left',
                'order' => 12
            ],
            [
                'code' => '13',
                'name' => 'Norme sulla circolazione dei veicoli',
                'icon' => 'heroicon-o-truck',
                'order' => 13
            ],
            [
                'code' => '14',
                'name' => 'Esempi di precedenza (ordine di precedenza agli incroci)',
                'icon' => 'heroicon-o-queue-list',
                'order' => 14
            ],
            [
                'code' => '15',
                'name' => 'Norme sul sorpasso',
                'icon' => 'heroicon-o-arrow-up-right',
                'order' => 15
            ],
            [
                'code' => '16',
                'name' => 'Fermata, sosta, arresto e partenza',
                'icon' => 'heroicon-o-pause-circle',
                'order' => 16
            ],
            [
                'code' => '17',
                'name' => 'Norme varie',
                'icon' => 'heroicon-o-document-text',
                'order' => 17
            ],
            [
                'code' => '18',
                'name' => 'Uso delle luci e dei dispositivi acustici, spie e simboli',
                'icon' => 'heroicon-o-light-bulb',
                'order' => 18
            ],
            [
                'code' => '19',
                'name' => 'Dispositivi di equipaggiamento, funzione ed uso: cinture di sicurezza, sistemi di ritenuta per bambini, casco protettivo e abbigliamento di sicurezza',
                'icon' => 'heroicon-o-shield-check',
                'order' => 19
            ],
            [
                'code' => '20',
                'name' => 'Patenti di guida, sistema sanzionatorio, documenti di circolazione, obblighi verso agenti, uso di lenti e altri apparecchi',
                'icon' => 'heroicon-o-identification',
                'order' => 20
            ],
            [
                'code' => '21',
                'name' => 'Incidenti stradali e comportamenti in caso di incidente',
                'icon' => 'heroicon-o-exclamation-circle',
                'order' => 21
            ],
            [
                'code' => '22',
                'name' => 'Guida in relazione alle qualità e condizioni fisiche e psichiche, alcool, droga, farmaci e primo soccorso',
                'icon' => 'heroicon-o-heart',
                'order' => 22
            ],
            [
                'code' => '23',
                'name' => 'Responsabilità civile, penale e amministrativa, RCA e altre forme assicurative legate al veicolo',
                'icon' => 'heroicon-o-scale',
                'order' => 23
            ],
            [
                'code' => '24',
                'name' => "Limitazione dei consumi, rispetto dell'ambiente e inquinamento",
                'icon' => 'heroicon-o-globe-alt',
                'order' => 24
            ],
            [
                'code' => '25',
                'name' => 'Elementi costitutivi del veicolo, manutenzione ed uso, stabilità e tenuta di strada, comportamenti e cautele di guida',
                'icon' => 'heroicon-o-cog-6-tooth',
                'order' => 25
            ],
        ];

        foreach ($topics as $topicData) {
            Topic::updateOrCreate(
                ['code' => $topicData['code']],
                [
                    'name' => $topicData['name'],
                    'icon' => $topicData['icon'] ?? 'heroicon-o-folder',
                    'order' => $topicData['order'],
                    'is_active' => true,
                    'total_questions' => 0, // Sarà aggiornato quando si aggiungeranno le domande
                ]
            );
        }

        $this->command->info('Creati/aggiornati ' . count($topics) . ' argomenti per la patente.');
    }
}