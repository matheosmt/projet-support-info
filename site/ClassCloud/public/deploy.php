<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$user = require_login();
$flash = take_flash();
?>
<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Classcloud — Déployer</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="topbar"><div class="topbar-inner"><a class="brand" href="dashboard.php">Classcloud</a>
<nav aria-label="Navigation principale"><a href="dashboard.php">Mes ressources</a><a href="deploy.php" aria-current="page">Déployer</a>
<form action="logout.php" method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button class="btn-small" type="submit">Se déconnecter</button></form></nav></div></header>
<main class="page">
<h1>Déployer une ressource</h1><p class="lead">Crée une VM ou un conteneur sur les serveurs de l'école.</p>
<?php if ($flash): ?><p class="notice <?= h($flash['type'] === 'error' ? 'error' : '') ?>"><?= h($flash['message']) ?></p><?php endif; ?>
<form class="deploy" action="create_resource.php" method="post" id="deploy-form">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<div class="panel">
<fieldset><legend>Type de ressource</legend><div class="choices">
<label class="choice"><input type="radio" name="type" value="vm" checked>Machine virtuelle<small>QEMU/KVM, isolée avec son propre système</small></label>
<label class="choice"><input type="radio" name="type" value="container">Conteneur LXC<small>Plus léger, partage le noyau Linux de l'hôte</small></label>
</div></fieldset>
<label for="name">Nom de la ressource</label><input type="text" id="name" name="name" maxlength="63" pattern="[a-zA-Z0-9][a-zA-Z0-9-]{1,62}" placeholder="ex. web-tp2" required>
<label for="model">Modèle</label><select id="model" name="model"><option value="Debian 12">Debian 12</option><option value="Ubuntu 24.04">Ubuntu 24.04</option></select>
<fieldset><legend>Taille</legend><div class="choices">
<label class="choice"><input type="radio" name="size" value="small" checked>Petite<small>1 vCPU · 1 Go RAM · 10 Go</small></label>
<label class="choice"><input type="radio" name="size" value="medium">Moyenne<small>2 vCPU · 2 Go RAM · 20 Go</small></label>
<label class="choice"><input type="radio" name="size" value="large">Grande<small>4 vCPU · 4 Go RAM · 40 Go</small></label>
</div></fieldset>
<p class="notice">Le modèle et la taille doivent correspondre aux modèles et ressources réellement préparés sur Proxmox. Les limites sont contrôlées côté serveur.</p>
</div>
<aside class="panel summary"><h2>Récapitulatif</h2><dl>
<dt>Type</dt><dd id="r-type">Machine virtuelle</dd><dt>Nom</dt><dd id="r-name">à définir</dd><dt>Modèle</dt><dd id="r-model">Debian 12</dd><dt>Taille</dt><dd id="r-size">Petite</dd>
</dl><button class="btn" type="submit">Déployer</button></aside>
</form>
</main>
<script>
const form = document.getElementById('deploy-form');
function updateSummary() {
 const d = new FormData(form);
 document.getElementById('r-type').textContent = d.get('type') === 'vm' ? 'Machine virtuelle' : 'Conteneur LXC';
 document.getElementById('r-name').textContent = d.get('name') || 'à définir';
 document.getElementById('r-model').textContent = d.get('model');
 document.getElementById('r-size').textContent = ({small:'Petite',medium:'Moyenne',large:'Grande'})[d.get('size')];
}
form.addEventListener('input', updateSummary);
</script>
</body></html>
