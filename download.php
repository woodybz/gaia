<?php

/*
 * ©2012 Croce Rossa Italiana
 */

require('./core.inc.php');

$f = $_GET['id'];
if (!$f) { die('Specificare ID del file da scaricare.'); }

$f = File::id($f);
if (!$f) { die('File non valido o scaduto.'); }

$f->download = $f->download + 1;

if ( $f->mime )
    header('Content-type: ' . $f->mime);

header("Content-Description: File Transfer");
header("X-Debug: " . str_replace("\n", "-", print_r($f, true)));

if ( !isset($_GET['anteprima']) )
	header("Content-Disposition: attachment; filename=\"{$f->nome}\"");

readfile($f->percorso());