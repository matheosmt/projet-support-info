<?php
declare(strict_types=1);

final class ProxmoxClient {
    public function __construct(private array $settings) {}

    private function request(string $method, string $path, array $data = []): array {
        if (empty($this->settings['enabled'])) {
            throw new RuntimeException('Intégration Proxmox désactivée dans la configuration.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Extension PHP cURL manquante.');
        }
        $base = rtrim((string)$this->settings['base_url'], '/');
        $url = $base . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        $headers = [
            'Authorization: PVEAPIToken=' . $this->settings['token_id'] . '=' . $this->settings['token_secret'],
            'Accept: application/json',
        ];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => (bool)($this->settings['verify_tls'] ?? true),
            CURLOPT_SSL_VERIFYHOST => !empty($this->settings['verify_tls']) ? 2 : 0,
        ]);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('Connexion à Proxmox impossible : ' . $error);
        }
        $decoded = json_decode((string)$body, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded) ? ($decoded['message'] ?? 'Erreur API Proxmox') : 'Erreur API Proxmox';
            throw new RuntimeException('Proxmox HTTP ' . $status . ' : ' . mb_substr((string)$message, 0, 300));
        }
        if (!is_array($decoded)) throw new RuntimeException('Réponse Proxmox invalide.');
        return $decoded;
    }

    public function nextId(): int {
        $result = $this->request('GET', 'cluster/nextid');
        $id = filter_var($result['data'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 100) throw new RuntimeException('Proxmox a retourné un VMID invalide.');
        return $id;
    }

    public function cloneResource(string $type, string $name): array {
        $node = (string)$this->settings['node'];
        $template = (int)($this->settings['templates'][$type] ?? 0);
        if ($template <= 0) throw new RuntimeException('VMID du modèle non configuré.');
        $newid = $this->nextId();
        if ($type === 'vm') {
            $path = "nodes/{$node}/qemu/{$template}/clone";
            $payload = ['newid' => $newid, 'name' => $name, 'full' => 1];
        } elseif ($type === 'container') {
            $path = "nodes/{$node}/lxc/{$template}/clone";
            $payload = ['newid' => $newid, 'hostname' => $name, 'full' => 1];
        } else {
            throw new InvalidArgumentException('Type de ressource invalide.');
        }
        $result = $this->request('POST', $path, $payload);
        $upid = $result['data'] ?? null;
        if (is_string($upid) && $upid !== '') {
            $this->waitForTask($node, $upid);
        }
        return ['vmid' => $newid, 'node' => $node];
    }

    public function start(string $type, string $node, int $vmid): void {
        $kind = $type === 'vm' ? 'qemu' : 'lxc';
        $result = $this->request('POST', "nodes/{$node}/{$kind}/{$vmid}/status/start");
        $upid = $result['data'] ?? null;
        if (is_string($upid) && $upid !== '') $this->waitForTask($node, $upid);
    }

    public function setStatus(string $type, string $node, int $vmid, string $action): void {
        $kind = $type === 'vm' ? 'qemu' : 'lxc';
        if (!in_array($action, ['start', 'stop'], true)) {
            throw new InvalidArgumentException('Action invalide.');
        }
        $result = $this->request('POST', "nodes/{$node}/{$kind}/{$vmid}/status/{$action}");
        $upid = $result['data'] ?? null;
        if (is_string($upid) && $upid !== '') $this->waitForTask($node, $upid);
    }

    private function waitForTask(string $node, string $upid): void {
        $deadline = time() + max(30, (int)($this->settings['clone_timeout_seconds'] ?? 180));
        do {
            $result = $this->request('GET', 'nodes/' . rawurlencode($node) .
                '/tasks/' . rawurlencode($upid) . '/status');
            $data = $result['data'] ?? [];
            if (($data['status'] ?? '') === 'stopped') {
                if (($data['exitstatus'] ?? 'OK') !== 'OK') {
                    throw new RuntimeException('La tâche Proxmox a échoué : ' . (string)($data['exitstatus'] ?? 'inconnue'));
                }
                return;
            }
            usleep(500000);
        } while (time() < $deadline);
        throw new RuntimeException('Délai dépassé pendant une tâche Proxmox. Vérifiez la tâche dans Proxmox avant de réessayer.');
    }
}
