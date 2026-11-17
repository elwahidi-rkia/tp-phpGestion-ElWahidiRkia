<?php
require_once 'functions.php';
exiger_connexion();

$id        = (int)($_GET['id'] ?? 0);
$etudiants = lire_etudiants();

if (isset($etudiants[$id])) {
    $photo = basename($etudiants[$id]['photo']);
    if ($photo !== '' && file_exists(DOSSIER_PHOTOS . $photo)) {
        unlink(DOSSIER_PHOTOS . $photo);
    }
    unset($etudiants[$id]);
    sauver_etudiants($etudiants);
    flash('succes', 'Étudiant supprimé.');
} else {
    flash('erreur', 'Étudiant introuvable.');
}

header('Location: liste.php');
exit;
