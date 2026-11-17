<?php
const FICHIER        = __DIR__ . '/data/etudiants.txt';
const DOSSIER_PHOTOS = __DIR__ . '/uploads/';
const FILIERES       = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS   = [
    'admin' => 'admin123',
    'prof'  => 'prof2026',
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function connecte(): bool
{
    return isset($_SESSION['user']);
}

function exiger_connexion(): void
{
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function nettoyer(string $s): string
{
    $s = str_replace([';', "\r", "\n"], ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
}

function lire_etudiants(): array
{
    if (!file_exists(FICHIER)) {
        return [];
    }
    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $liste = [];
    foreach ($lignes as $ligne) {
        $p = explode(';', $ligne);
        if (count($p) < 5) {
            continue;
        }
        $id = (int)$p[0];
        $liste[$id] = [
            'id'      => $id,
            'nom'     => $p[1],
            'prenom'  => $p[2],
            'filiere' => $p[3],
            'note'    => (float)$p[4],
            'photo'   => $p[5] ?? '',
        ];
    }
    return $liste;
}

function sauver_etudiants(array $liste): void
{
    $lignes = [];
    foreach ($liste as $e) {
        $lignes[] = implode(';', [$e['id'], $e['nom'], $e['prenom'], $e['filiere'], $e['note'], $e['photo']]);
    }
    $contenu = $lignes ? implode(PHP_EOL, $lignes) . PHP_EOL : '';
    file_put_contents(FICHIER, $contenu, LOCK_EX);
}

/** Affiche un texte avec la partie recherchée surlignée (sécurisé). */
function surligner(string $texte, string $q): string
{
    if ($q === '') {
        return h($texte);
    }
    $res = preg_split('/(' . preg_quote($q, '/') . ')/iu', $texte, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($res as $i => $morceau) {
        $out .= $i % 2 === 1 ? '<mark>' . h($morceau) . '</mark>' : h($morceau);
    }
    return $out;
}
