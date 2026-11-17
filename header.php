<?php $titre = $titre ?? 'Gestion des étudiants'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titre) ?> · ÉTUDIANTS.EXE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<header class="barre">
    <div class="logo">▚ ÉTUDIANTS<b>.EXE</b></div>
    <?php if (connecte()): ?>
        <?php $page = basename($_SERVER['PHP_SELF']); ?>
        <nav>
            <a href="liste.php" class="<?= $page === 'liste.php' ? 'actif' : '' ?>">Liste</a>
            <a href="ajouter.php" class="<?= $page === 'ajouter.php' ? 'actif' : '' ?>">Ajouter</a>
            <a href="logout.php">Quitter (<?= h($_SESSION['user']) ?>)</a>
        </nav>
    <?php endif; ?>
</header>
<div class="bande"><i></i><i></i><i></i><i></i></div>
<main class="contenu">
