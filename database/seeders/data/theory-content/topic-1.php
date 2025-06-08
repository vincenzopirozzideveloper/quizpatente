<?php

/**
 * Path: database/seeders/data/theory-content/topic-1.php
 * Topic 1: Definizioni generali e doveri nell'uso della strada
 */

return [
    [
        'code' => '1.1',
        'title' => 'Strada',
        'content' => <<<'MARKDOWN'
La strada è un'area aperta alla circolazione dei pedoni, degli animali e dei veicoli.
Può essere a senso unico o a doppio senso di circolazione.
Può essere suddivisa in più carreggiate in presenza di uno spartitraffico.
Comprende:
* le carreggiate, riservate a veicoli ed animali
* le banchine
* i marciapiedi, riservati ai pedoni
* le piste ciclabili, riservate alle biciclette
MARKDOWN,
        'order' => 1,
        'is_published' => true,
        'metadata' => [
            'keywords' => ['strada', 'circolazione', 'veicoli', 'pedoni'],
            'difficulty' => 'base',
        ],
    ],
    [
        'code' => '1.2',
        'title' => 'Carreggiata',
        'content' => <<<'MARKDOWN'
La carreggiata è la parte della strada destinata al transito dei veicoli.
Può essere a senso unico o a doppio senso di circolazione.
Può essere suddivisa in più corsie.
Fanno parte della carreggiata:
* tutte le corsie, tranne la corsia di emergenza
* gli attraversamenti pedonali
* gli attraversamenti ciclabili
Non fanno parte della carreggiata, ma possono affiancarla:
* marciapiedi, banchine o viali pedonali
* piste ciclabili
* piazzole di sosta
MARKDOWN,
        'order' => 2,
        'is_published' => true,
        'metadata' => [
            'keywords' => ['carreggiata', 'corsie', 'veicoli', 'attraversamenti'],
            'difficulty' => 'base',
        ],
    ],
];