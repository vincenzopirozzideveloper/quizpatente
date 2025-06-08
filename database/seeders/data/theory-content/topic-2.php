<?php

/**
 * Path: database/seeders/data/theory-content/topic-2.php
 * Topic 2: Segnali di pericolo
 */

return [
    [
        'code' => '2.1',
        'title' => 'Strada deformata',
        'content' => <<<'MARKDOWN'
È un segnale di pericolo che preannuncia una strada deformata, in cattivo stato, dissestata o con pavimentazione irregolare (di norma a 150 metri).

Può essere integrato con il pannello di 'ESTESA' (che indica l'estensione del tratto), 'DISTANZA' (che indica tra quanti metri si trova il tratto) o con un segnale di 'LIMITE MASSIMO DI VELOCITÀ'.

Se a fondo giallo, è usato in presenza di cantieri stradali.

In presenza del segnale è necessario:
* adeguare la velocità in relazione alle particolari condizioni del fondo stradale (specie se si traina un rimorchio) anche per evitare eccessive sollecitazioni e danni alle sospensioni
* prevedere eventuali sbandamenti dei veicoli provenienti dal senso opposto
* tenere saldamente il volante, per controllare possibili sbandamenti

Da non confondere con il segnale 'DOSSO' o 'CUNETTA'.

**Non è vero che:**
- preannuncia una serie di dossi, una cunetta o un tratto di visibilità ridotta
- è obbligatorio circolare al centro della carreggiata o fare attenzione al restringimento della carreggiata
- è vietato sorpassare
MARKDOWN,
        'order' => 1,
        'is_published' => true,
        'image_caption' => 'Segnale di pericolo - Strada deformata', // Aggiungi didascalia
        'image_position' => 'after', // Posizione immagine
        'metadata' => [
            'keywords' => ['segnali', 'pericolo', 'strada deformata', 'dissestata'],
            'difficulty' => 'base',
            'image_file' => 'segnale-strada-deformata.png', // Nome del file immagine
            'signal_code' => 'FIG-001', // Codice ministeriale del segnale
        ],
    ],
];