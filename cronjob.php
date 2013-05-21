<?php

/*
 * ©2013 Croce Rossa Italiana
 */

require './core.inc.php';

/*
 * Rimuove limiti di tempo
 */
set_time_limit(0);


$start = microtime(true);

/* Sessione di cronjob */
$log = "Cronjob iniziato: " . date('d-m-Y H:i:s') . "\n";

/* Le patenti in scadenza tra qui e 15 gg */
$patenti = TitoloPersonale::inScadenza(2700, 2709, 15); // Minimo id titolo, Massimo id titolo, Giorni

$n = 0;

/* Contiene gli id dei volontari già insuttati */
$giaInsultati = [];

foreach ( $patenti as $patente ) {
   
    $_v = $patente->volontario();
    
    /* Se l'ho già insultato... */
    if ( in_array($_v->id, $giaInsultati ) ) {
        continue; // Il prossimo...
    }
    
    /* Ricordati che l'ho insuttato */
    $giaInsultati[] = $_v->id;
    
    $m = new Email('patenteScadenza', 'Avviso patente CRI in scadenza');
    $m->a           = $_v;
    $m->_NOME       = $_v->nome;
    $m->_SCADENZA   = date('d-m-Y', $patente->fine);
    $m->invia();
    $n++;
}

$log .= "Inviate $n notifiche di scadenza patente\n";

/* Patenti civili in scadenza da qui a 15 giorni*/
$patenti = TitoloPersonale::inScadenza(70, 77, 15); // Minimo id titolo, Massimo id titolo, Giorni
$n = 0;

/* Contiene gli id dei volontari già insuttati */
$giaInsultati = [];
foreach ( $patenti as $patente ) {
    $_v = $patente->volontario();

    /* Se l'ho già insultato... */
    if ( in_array($_v->id, $giaInsultati ) ) {
        continue; // Il prossimo...
    }

    /* Ricordati che l'ho insuttato */
    $giaInsultati[] = $_v->id;
    $m = new Email('patenteScadenzaCivile', 'Avviso patente Civile in scadenza');
    $m->a = $_v;
    $m->_NOME = $_v->nome;
    $m->_SCADENZA = date('d-m-Y', $patente->fine);
    $m->invia();
    $n++;
}

$log .= "Inviate $n notifiche di scadenza patente civili\n";

/* Cancella i file scaduti da disco e database */
$n = 0;
foreach ( File::scaduti() as $f ) {
    $f->cancella(); $n++;
}
$log .= "Cancellati $n file scaduti\n";


/* Cancella le sessioni scadute */
$n = 0;
foreach ( Sessione::scadute() as $s ) {
    $s->cancella(); $n++;
}
$log .= "Cancellate $n sessioni scadute\n";


/*
 * PRESIDENTE COMITATO
 * - Titoli pendenti
 * - App. pendenti
 */
$n = 0;
/*
 * Per ogni comitato iscritto a Gaia
 */
foreach ( Comitato::elenco() as $comitato ) {
    
    /*
     * Controlla appartenenze e titoli
     */
    $a = $comitato->appartenenzePendenti();
    $b = $comitato->titoliPendenti();
    
    $c = $a + $b;
    if ( $c == 0 ) { continue; }
    
    /*
     * Per ogni presidente...
     */
    foreach ( $comitato->volontariPresidenti() as $presidente ) {
        $m = new Email('riepilogoPresidente', "Promemoria: Ci sono {$c} azioni in sospeso");
        $m->a       = $presidente;
        $m->_NOME       = $presidente->nomeCompleto();
        $m->_COMITATO   = $comitato->nomeCompleto();
        $m->_APPPENDENTI= $a;
        $m->_TITPENDENTI= $b;
        $m->invia();
        $n++;
    }
}
$log .= "Inviati $n promemoria ai presidenti\n";


/* FINE CRONJOB */
$end = microtime(true);
$tempo = $end - $start;
$log .= "Fine esecuzione ({$tempo} secondi)\n";

/* Stampa il log a video */
echo "<pre>$log</pre>";

/* Appende il file al log */
file_put_contents('upload/log/cronjob.txt', "\n\n" . $log, FILE_APPEND);

/* Invia per email il log */
$m = new Email('mailTestolibero', 'Report cronjob');
$dest = new stdClass();
$dest->nome     = 'Servizi';
$dest->email    = 'informatica@cricatania.it';
$m->a = $dest;
$m->_TESTO = nl2br($log);
$m->invia();