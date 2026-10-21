<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/ProxmoxClient.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée.'); }
verify_csrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$action = (string)($_POST['action'] ?? '');
if (!$id || !in_array($action, ['start','stop'], true)) {
    flash('Action invalide.', 'error'); redirect('dashboard.php');
}

// Le filtre school_id est obligatoire : un ID d'une autre école ne suffit jamais.
$stmt = db()->prepare('SELECT id, school_id, resource_type, status, vmid, proxmox_node FROM resources WHERE id = ? AND school_id = ? LIMIT 1');
$stmt->execute([$id, (int)$user['school_id']]);
$r = $stmt->fetch();
if (!$r || !$r['vmid'] || !$r['proxmox_node']) {
    http_response_code(404); exit('Ressource introuvable.');
}
$allowed = $action === 'start' ? ['stopped'] : ['running'];
if (!in_array($r['status'], $allowed, true)) {
    flash('Cette action ne correspond pas à l’état actuel de la ressource.', 'error'); redirect('dashboard.php');
}
try {
    global $config;
    $client = new ProxmoxClient($config['proxmox']);
    $client->setStatus($r['resource_type'], $r['proxmox_node'], (int)$r['vmid'], $action);
    $newStatus = $action === 'start' ? 'running' : 'stopped';
    $stmt = db()->prepare('UPDATE resources SET status = ? WHERE id = ? AND school_id = ?');
    $stmt->execute([$newStatus, $id, (int)$user['school_id']]);
    flash($action === 'start' ? 'Ressource démarrée.' : 'Ressource arrêtée.');
} catch (Throwable $e) {
    error_log('[Classcloud action] ' . $e->getMessage());
    flash('Action impossible. Vérifiez Proxmox et les journaux du serveur.', 'error');
}
redirect('dashboard.php');
