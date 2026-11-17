<?php
require_once 'functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
$login  = $_COOKIE['dernier_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';

    if (isset(UTILISATEURS[$login]) && UTILISATEURS[$login] === $mdp) {
        $_SESSION['user'] = $login;
        setcookie('dernier_user', $login, time() + 7 * 24 * 3600);
        header('Location: liste.php');
        exit;
    }
    $erreur = 'Identifiants incorrects.';
}

$titre = 'Connexion';
include 'header.php';
?>
<section class="carte login">
    <h1>LOGIN</h1>
    <?php if ($erreur): ?>
        <p class="msg erreur"><?= h($erreur) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Login
            <input type="text" name="login" value="<?= h($login) ?>" required autofocus>
        </label>
        <label>Mot de passe
            <input type="password" name="mdp" required>
        </label>
        <button class="btn" type="submit">Entrer ▸</button>
    </form>
    <p class="aide">Compte de test : admin / admin123</p>
</section>
<?php include 'footer.php'; ?>
