<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['code' => '1', 'name' => "Definizioni generali e doveri nell'uso della strada", 'order' => 1],
            ['code' => '2', 'name' => 'Segnali di pericolo', 'order' => 2],
            ['code' => '3', 'name' => 'Segnali di divieto', 'order' => 3],
        ];

        foreach ($topics as $topic) {
            Topic::updateOrCreate(
                ['code' => $topic['code']],
                ['name' => $topic['name'], 'order' => $topic['order']]
            );
        }
    }
}
