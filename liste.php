<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $etudiants = array_filter($etudiants, fn($e) =>
        stripos($e['nom'], $q) !== false || stripos($e['prenom'], $q) !== false
    );
}

$tris = [
    'ajout'   => "Ordre d'ajout",
    'nom'     => 'Nom',
    'filiere' => 'Filière',
    'note'    => 'Note décroissante',
];
$tri = $_GET['tri'] ?? 'ajout';
if (!array_key_exists($tri, $tris)) {
    $tri = 'ajout';
}
switch ($tri) {
    case 'nom':
        usort($etudiants, fn($a, $b) => strcasecmp($a['nom'], $b['nom']));
        break;
    case 'filiere':
        usort($etudiants, fn($a, $b) => strcasecmp($a['filiere'], $b['filiere']));
        break;
    case 'note':
        usort($etudiants, fn($a, $b) => $b['note'] <=> $a['note']);
        break;
    default:
        ksort($etudiants);
}

$nb      = count($etudiants);
$notes   = array_column($etudiants, 'note');
$moyenne = $nb > 0 ? array_sum($notes) / $nb : 0;
$max     = $nb > 0 ? max($notes) : 0;

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$titre = 'Liste des étudiants';
include 'header.php';
?>
<h1>LISTE DES ÉTUDIANTS</h1>

<?php if ($q !== ''): ?>
    <p class="resultat-recherche">Résultats pour « <strong><?= h($q) ?></strong> » : <?= $nb ?> trouvé(s) · <a href="liste.php">Effacer</a></p>
<?php endif; ?>

<?php if ($flash): ?>
    <p class="msg <?= h($flash['type']) ?>"><?= h($flash['msg']) ?></p>
<?php endif; ?>

<form method="get" class="carte filtres">
    <label>Recherche
        <input type="search" id="recherche" name="q" value="<?= h($q) ?>" placeholder="nom ou prénom…">
    </label>
    <label>Tri
        <select name="tri">
            <?php foreach ($tris as $cle => $libelle): ?>
                <option value="<?= h($cle) ?>" <?= $tri === $cle ? 'selected' : '' ?>><?= h($libelle) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn" type="submit">Appliquer</button>
</form>

<div class="stats">
    <div><span>Affichés</span><strong><?= $nb ?></strong></div>
    <div><span>Moyenne</span><strong><?= number_format($moyenne, 2, ',', ' ') ?></strong></div>
    <div><span>Meilleure note</span><strong><?= $nb > 0 ? number_format($max, 2, ',', ' ') : '—' ?></strong></div>
</div>

<div class="carte plein">
    <table id="table-etudiants">
        <thead>
            <tr><th>Photo</th><th>Nom</th><th>Prénom</th><th>Filière</th><th>Note</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$etudiants): ?>
            <tr><td colspan="6" class="vide">Aucun étudiant</td></tr>
        <?php else: foreach ($etudiants as $e):
            $classe = $e['note'] >= 10 ? 'note-ok' : 'note-ko';
            $aPhoto = $e['photo'] !== '' && file_exists(DOSSIER_PHOTOS . $e['photo']);
        ?>
            <tr data-nom="<?= h(mb_strtolower($e['nom'] . ' ' . $e['prenom'])) ?>">
                <td>
                    <span class="cadre-photo">
                        <?php if ($aPhoto): ?>
                            <img src="uploads/<?= h($e['photo']) ?>" alt="Photo de <?= h($e['prenom']) ?>">
                        <?php else: ?>
                            <span class="initiales"><?= h(mb_strtoupper(mb_substr($e['prenom'], 0, 1) . mb_substr($e['nom'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </span>
                </td>
                <td><?= surligner(mb_strtoupper($e['nom']), $q) ?></td>
                <td><?= surligner(mb_strtoupper(mb_substr($e['prenom'], 0, 1)) . mb_substr($e['prenom'], 1), $q) ?></td>
                <td><?= h($e['filiere']) ?></td>
                <td class="<?= $classe ?>"><?= number_format($e['note'], 2, ',', '') ?></td>
                <td>
                    <a class="act" href="ajouter.php?id=<?= (int)$e['id'] ?>">Modifier</a>
                    <a class="act lien-suppr" href="supprimer.php?id=<?= (int)$e['id'] ?>">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>
