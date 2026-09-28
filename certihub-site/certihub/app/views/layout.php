<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CertiHub — parcours, certifications, formations et actualités IT pour étudiants.">
    <title><?= e($title) ?> · CertiHub</title>
    <link rel="stylesheet" href="/assets/app.css">
    <script defer src="/assets/app.js"></script>
</head>
<body>
<header class="topbar">
    <div class="container nav-wrap">
        <a class="brand" href="/"><span class="brand-mark">C</span><span>certi<span>hub</span></span></a>
        <button class="nav-toggle" aria-label="Ouvrir le menu" data-menu-toggle>☰</button>
        <nav class="main-nav" data-menu>
            <a class="<?= active('certifications') ?>" href="/certifications">Certifications</a>
            <a class="<?= active('parcours') ?>" href="/parcours">Parcours</a>
            <a class="<?= active('formations') ?>" href="/formations">Formations</a>
            <a class="<?= active('actualites') ?>" href="/actualites">Actualités</a>
            <?php if (Auth::check()): ?>
                <a class="<?= active('support') ?>" href="/support">Support</a>
                <?php if (in_array(Auth::user()['role'], ['admin','support'], true)): ?><a class="<?= active('admin') ?>" href="/admin">Admin</a><?php endif; ?>
                <a class="nav-cta <?= active('tableau-de-bord') ?>" href="/tableau-de-bord">Mon espace</a>
            <?php else: ?>
                <a href="/connexion">Connexion</a>
                <a class="nav-cta" href="/inscription">Créer un compte</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main><?php include $viewFile; ?></main>
<footer class="footer">
    <div class="container footer-grid">
        <div><a class="brand" href="/"><span class="brand-mark">C</span><span>certi<span>hub</span></span></a><p>La boussole des certifications IT pour les étudiants.</p></div>
        <div><strong>Explorer</strong><a href="/certifications">Certifications</a><a href="/parcours">Parcours</a><a href="/formations">Formations</a></div>
        <div><strong>Ressources</strong><a href="/actualites">Actualités</a><a href="/support">Support</a><a href="/profil">Sécurité du compte</a></div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> CertiHub</span><span>Prototype pédagogique — à adapter aux procédures de l'école</span></div>
</footer>
</body>
</html>
