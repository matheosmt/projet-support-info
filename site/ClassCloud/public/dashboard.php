<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$user = require_login();

$stmt = db()->prepare('SELECT name, quota_vms, quota_containers, quota_vcpu, quota_ram_mb, quota_disk_gb FROM schools WHERE id = ?');
$stmt->execute([(int)$user['school_id']]);
$school = $stmt->fetch();
if (!$school) { http_response_code(403); exit('École introuvable.'); }

$stmt = db()->prepare('SELECT id, name, resource_type, model, status, vmid, vcpu, ram_mb, disk_gb, created_at FROM resources WHERE school_id = ? ORDER BY created_at DESC');
$stmt->execute([(int)$user['school_id']]);
$resources = $stmt->fetchAll();

$used = ['vm'=>0, 'container'=>0, 'vcpu'=>0, 'ram'=>0, 'disk'=>0];
foreach ($resources as $r) {
    if ($r['status'] !== 'error') {
        $used[$r['resource_type']]++;
        $used['vcpu'] += (int)$r['vcpu'];
        $used['ram'] += (int)$r['ram_mb'];
        $used['disk'] += (int)$r['disk_gb'];
    }
}
$flash = take_flash();
function quota_percent(int $used, int $limit): int { return $limit > 0 ? min(100, (int)round($used * 100 / $limit)) : 0; }
?>
<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Classcloud — Mes ressources</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="topbar"><div class="topbar-inner">
<a class="brand" href="dashboard.php">Classcloud</a>
<nav aria-label="Navigation principale"><a href="dashboard.php" aria-current="page">Mes ressources</a><a href="deploy.php">Déployer</a>
<form action="logout.php" method="post" style="display:inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button class="btn-small" type="submit">Se déconnecter</button></form></nav>
</div></header>
<main class="page">
<div class="page-head"><div><h1>Mes ressources</h1><p><?= h($school['name']) ?> · <?= h($user['email']) ?></p></div><a class="btn" href="deploy.php">Déployer une ressource</a></div>
<?php if ($flash): ?><p class="notice <?= h($flash['type'] === 'error' ? 'error' : '') ?>"><?= h($flash['message']) ?></p><?php endif; ?>
<section class="quotas" aria-label="Quotas de l'école">
  <div class="panel quota"><small>Machines virtuelles</small><br><b><?= $used['vm'] ?> / <?= (int)$school['quota_vms'] ?></b><progress max="<?= max(1,(int)$school['quota_vms']) ?>" value="<?= min($used['vm'],(int)$school['quota_vms']) ?>"></progress></div>
  <div class="panel quota"><small>Conteneurs</small><br><b><?= $used['container'] ?> / <?= (int)$school['quota_containers'] ?></b><progress max="<?= max(1,(int)$school['quota_containers']) ?>" value="<?= min($used['container'],(int)$school['quota_containers']) ?>"></progress></div>
  <div class="panel quota"><small>Mémoire RAM</small><br><b><?= number_format($used['ram']/1024,1,',',' ') ?> / <?= number_format((int)$school['quota_ram_mb']/1024,0,',',' ') ?> Go</b><progress max="<?= max(1,(int)$school['quota_ram_mb']) ?>" value="<?= min($used['ram'],(int)$school['quota_ram_mb']) ?>"></progress></div>
  <div class="panel quota"><small>Stockage déclaré</small><br><b><?= $used['disk'] ?> / <?= (int)$school['quota_disk_gb'] ?> Go</b><progress max="<?= max(1,(int)$school['quota_disk_gb']) ?>" value="<?= min($used['disk'],(int)$school['quota_disk_gb']) ?>"></progress></div>
</section>
<section class="panel table-wrap">
<table><caption class="sr">Ressources de mon école</caption><thead><tr><th>Nom</th><th>Type</th><th>Modèle</th><th>VMID</th><th>État</th><th>Actions</th></tr></thead><tbody>
<?php if (!$resources): ?><tr><td colspan="6">Aucune ressource pour le moment.</td></tr><?php endif; ?>
<?php foreach ($resources as $r): ?>
<tr><td><?= h($r['name']) ?></td><td><?= $r['resource_type'] === 'vm' ? 'Machine virtuelle' : 'Conteneur LXC' ?></td><td><?= h($r['model']) ?></td><td><?= $r['vmid'] ? (int)$r['vmid'] : '—' ?></td><td><span class="status <?= $r['status'] === 'error' ? 'error' : ($r['status'] === 'stopped' ? 'off' : '') ?>"><?= h($r['status']) ?></span></td>
<td>
<?php if (in_array($r['status'], ['running','stopped'], true) && $r['vmid']): ?>
<form action="resource_action.php" method="post" style="display:inline">
<input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
<input type="hidden" name="action" value="<?= $r['status'] === 'running' ? 'stop' : 'start' ?>">
<button class="btn-small" type="submit"><?= $r['status'] === 'running' ? 'Arrêter' : 'Démarrer' ?></button></form>
<?php else: ?>—<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></section>
</main></body></html>
