<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

if (current_user()) redirect('dashboard.php');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT id, password_hash, active FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $record = $stmt->fetch();
    if ($record && (int)$record['active'] === 1 && password_verify($password, $record['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$record['id'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        redirect('dashboard.php');
    }
    $error = 'Identifiants incorrects ou compte désactivé.';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Classcloud — Connexion</title><link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar"><div class="topbar-inner"><a class="brand" href="index.php">Classcloud</a></div></header>
<main class="page">
  <div class="hero">
    <section>
      <h1>Ton infrastructure, accessible depuis le navigateur.</h1>
      <p class="lead">Déploie des machines virtuelles et des conteneurs sur l'infrastructure de l'école.</p>
      <ul class="features">
        <li><strong>Déploiement simplifié</strong><span>Choisis un modèle et une taille autorisée.</span></li>
        <li><strong>Ressources isolées</strong><span>Chaque compte est rattaché à une école.</span></li>
        <li><strong>Hébergement centralisé</strong><span>Les ressources tournent sur le serveur, pas sur ton PC.</span></li>
      </ul>
    </section>
    <section class="panel login">
      <h2>Se connecter</h2>
      <?php if ($error): ?><p class="notice error"><?= h($error) ?></p><?php endif; ?>
      <form method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <label for="email">Adresse e-mail</label>
        <input type="text" id="email" name="email" autocomplete="username" required>
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>
        <button class="btn" type="submit">Se connecter</button>
      </form>
    </section>
  </div>
</main>
</body></html>
