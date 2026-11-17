<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();

$id      = (int)($_GET['id'] ?? 0);
$edition = $id > 0;
if ($edition && !isset($etudiants[$id])) {
    flash('erreur', 'Étudiant introuvable.');
    header('Location: liste.php');
    exit;
}
$anciennePhoto = $edition ? $etudiants[$id]['photo'] : '';
$valeurs = $edition
    ? $etudiants[$id]
    : ['nom' => '', 'prenom' => '', 'filiere' => '', 'note' => '', 'photo' => ''];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = nettoyer($_POST['nom'] ?? '');
    $prenom   = nettoyer($_POST['prenom'] ?? '');
    $filiere  = $_POST['filiere'] ?? '';
    $noteBrut = str_replace(',', '.', trim($_POST['note'] ?? ''));

    $valeurs = ['nom' => $nom, 'prenom' => $prenom, 'filiere' => $filiere,
                'note' => $_POST['note'] ?? '', 'photo' => $anciennePhoto];

    if ($nom === '')    { $erreurs[] = 'Le nom est obligatoire.'; }
    if ($prenom === '') { $erreurs[] = 'Le prénom est obligatoire.'; }
    if (!in_array($filiere, FILIERES, true)) { $erreurs[] = 'Filière invalide.'; }
    if (!is_numeric($noteBrut) || $noteBrut < 0 || $noteBrut > 20) {
        $erreurs[] = 'La note doit être un nombre entre 0 et 20.';
    }

    $fichier = null;
    $ext     = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['photo'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Erreur pendant l'envoi de la photo (code {$f['error']}).";
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $erreurs[] = 'Photo : formats autorisés jpg, jpeg, png.';
            } elseif ($f['size'] > 1024 * 1024) {
                $erreurs[] = 'Photo : taille maximale 1 Mo.';
            } else {
                $fichier = $f;
            }
        }
    }

    if (!$erreurs) {
        $photo = $anciennePhoto;

        if ($fichier !== null) {
            $nouveauNom = uniqid('etu_') . '.' . $ext;
            if (move_uploaded_file($fichier['tmp_name'], DOSSIER_PHOTOS . $nouveauNom)) {
                if ($anciennePhoto !== '' && file_exists(DOSSIER_PHOTOS . $anciennePhoto)) {
                    unlink(DOSSIER_PHOTOS . $anciennePhoto);
                }
                $photo = $nouveauNom;
            } else {
                $erreurs[] = "Impossible d'enregistrer la photo (dossier uploads/ accessible en écriture ?).";
            }
        }

        if (!$erreurs) {
            $nouvelId = $edition ? $id : ($etudiants ? max(array_keys($etudiants)) + 1 : 1);
            $etudiants[$nouvelId] = [
                'id'      => $nouvelId,
                'nom'     => $nom,
                'prenom'  => $prenom,
                'filiere' => $filiere,
                'note'    => round((float)$noteBrut, 2),
                'photo'   => $photo,
            ];
            sauver_etudiants($etudiants);
            flash('succes', $edition ? 'Étudiant modifié.' : 'Étudiant ajouté.');
            header('Location: liste.php');
            exit;
        }
    }
}

$titre = $edition ? 'Modifier un étudiant' : 'Ajouter un étudiant';
include 'header.php';
?>
<h1><?= $edition ? 'MODIFIER' : 'AJOUTER' ?> UN ÉTUDIANT</h1>

<?php if ($erreurs): ?>
    <div class="msg erreur">
        Corrigez les points suivants :
        <ul>
            <?php foreach ($erreurs as $err): ?>
                <li><?= h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="carte formulaire">
    <label>Nom
        <input type="text" name="nom" value="<?= h($valeurs['nom']) ?>">
    </label>
    <label>Prénom
        <input type="text" name="prenom" value="<?= h($valeurs['prenom']) ?>">
    </label>
    <label>Filière
        <select name="filiere">
            <option value="">-- choisir --</option>
            <?php foreach (FILIERES as $fil): ?>
                <option value="<?= h($fil) ?>" <?= $valeurs['filiere'] === $fil ? 'selected' : '' ?>><?= h($fil) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Note (0 à 20)
        <input type="text" name="note" value="<?= h((string)$valeurs['note']) ?>">
    </label>
    <label>Photo (jpg, png, 1 Mo max, facultatif)
        <input type="file" name="photo" accept=".jpg,.jpeg,.png">
    </label>
    <?php if ($anciennePhoto !== '' && file_exists(DOSSIER_PHOTOS . $anciennePhoto)): ?>
        <p class="aide">Photo actuelle :
            <span class="cadre-photo"><img src="uploads/<?= h($anciennePhoto) ?>" alt="Photo actuelle"></span>
        </p>
    <?php endif; ?>
    <button class="btn" type="submit"><?= $edition ? 'Enregistrer' : 'Ajouter' ?> ▸</button>
    <a class="btn sec" href="liste.php">Annuler</a>
</form>
<?php include 'footer.php'; ?>
