<?php
declare(strict_types=1);

/*
 * Copiez ce fichier vers config/config.php.
 * Ne stockez jamais ce fichier dans public/ et ne publiez pas ses secrets.
 */
return [
    'app' => [
        'session_name' => 'classcloud_session',
        'secure_cookies' => true, // mettre false uniquement en local HTTP de laboratoire
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'classcloud',
        'user' => 'classcloud_app',
        'password' => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'proxmox' => [
        'enabled' => false, // activer uniquement après configuration et test
        'base_url' => 'https://proxmox.example.test:8006/api2/json',
        'token_id' => 'classcloud@pve!api',
        'token_secret' => 'CHANGE_ME',
        'verify_tls' => true,
        'node' => 'pve1',
        'templates' => [
            'vm' => 9000,        // VMID d'un modèle QEMU
            'container' => 9010, // VMID d'un modèle LXC
        ],
        'clone_timeout_seconds' => 180,
    ],
];
