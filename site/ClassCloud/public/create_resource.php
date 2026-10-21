<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/ProxmoxClient.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }
verify_csrf();

$name = trim((string)($_POST['name'] ?? ''));
$type = (string)($_POST['type'] ?? '');
$model = (string)($_POST['model'] ?? '');
$size = (string)($_POST['size'] ?? '');

if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,62}$/', $name)) {
    flash('Nom invalide : utilisez 2 à 63 lettres, chiffres ou tirets.', 'error'); redirect('deploy.php');
}
if (!in_array($type, ['vm','container'], true) ||
    !in_array($model, ['Debian 12','Ubuntu 24.04'], true) ||
    !in_array($size, ['small','medium','large'], true)) {
    flash('Paramètres de déploiement invalides.', 'error'); redirect('deploy.php');
}
$sizes = [
  'small' => ['vcpu'=>1, 'ram'=>1024, 'disk'=>10],
  'medium' => ['vcpu'=>2, 'ram'=>2048, 'disk'=>20],
  'large' => ['vcpu'=>4, 'ram'=>4096, 'disk'=>40],
];
$spec = $sizes[$size];
$pdo = db();

try {
    // Sérialise les créations d'une école pendant le contrôle de quota.
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT * FROM schools WHERE id = ? FOR UPDATE');
    $stmt->execute([(int)$user['school_id']]);
    $school = $stmt->fetch();
    if (!$school) throw new RuntimeException('École introuvable.');

    $stmt = $pdo->prepare("SELECT resource_type, status, vcpu, ram_mb, disk_gb FROM resources WHERE school_id = ? AND status <> 'error'");
    $stmt->execute([(int)$user['school_id']]);
    $current = $stmt->fetchAll();
    $counts = ['vm'=>0, 'container'=>0, 'vcpu'=>0, 'ram'=>0, 'disk'=>0];
    foreach ($current as $r) {
        $counts[$r['resource_type']]++;
        $counts['vcpu'] += (int)$r['vcpu'];
        $counts['ram'] += (int)$r['ram_mb'];
        $counts['disk'] += (int)$r['disk_gb'];
    }
    $typeQuota = $type === 'vm' ? (int)$school['quota_vms'] : (int)$school['quota_containers'];
    if ($counts[$type] + 1 > $typeQuota ||
        $counts['vcpu'] + $spec['vcpu'] > (int)$school['quota_vcpu'] ||
        $counts['ram'] + $spec['ram'] > (int)$school['quota_ram_mb'] ||
        $counts['disk'] + $spec['disk'] > (int)$school['quota_disk_gb']) {
        throw new RuntimeException('Quota de l’école dépassé. Choisissez une taille plus petite ou contactez l’administrateur.');
    }
    $stmt = $pdo->prepare('SELECT id FROM resources WHERE school_id = ? AND name = ? AND status <> ? LIMIT 1');
    $stmt->execute([(int)$user['school_id'], $name, 'error']);
    if ($stmt->fetch()) throw new RuntimeException('Une ressource porte déjà ce nom dans votre école.');

    $stmt = $pdo->prepare("INSERT INTO resources (school_id, created_by, name, resource_type, model, status, vcpu, ram_mb, disk_gb) VALUES (?, ?, ?, ?, ?, 'provisioning', ?, ?, ?)");
    $stmt->execute([(int)$user['school_id'], (int)$user['id'], $name, $type, $model, $spec['vcpu'], $spec['ram'], $spec['disk']]);
    $resourceId = (int)$pdo->lastInsertId();
    $pdo->commit();

    // L'appel Proxmox se fait uniquement côté serveur.
    global $config;
    $client = new ProxmoxClient($config['proxmox']);
    $created = $client->cloneResource($type, $name);
    $client->start($type, $created['node'], $created['vmid']);

    $stmt = $pdo->prepare("UPDATE resources SET vmid = ?, proxmox_node = ?, status = 'running', error_message = NULL WHERE id = ? AND school_id = ?");
    $stmt->execute([$created['vmid'], $created['node'], $resourceId, (int)$user['school_id']]);
    flash('Ressource déployée et démarrée (VMID ' . $created['vmid'] . ').');
    redirect('dashboard.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // Si l'enregistrement existe, on marque l'échec sans exposer les détails techniques.
    if (isset($resourceId)) {
        $stmt = $pdo->prepare("UPDATE resources SET status = 'error', error_message = ? WHERE id = ? AND school_id = ?");
        $safeMessage = mb_substr($e->getMessage(), 0, 490);
        $stmt->execute([$safeMessage, $resourceId, (int)$user['school_id']]);
    }
    error_log('[Classcloud deploy] ' . $e->getMessage());
    flash('Déploiement impossible. Vérifiez la configuration et les tâches Proxmox, puis consultez les journaux du serveur.', 'error');
    redirect('deploy.php');
}
