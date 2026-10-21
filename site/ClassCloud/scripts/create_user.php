<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Ce script doit être exécuté en ligne de commande.\n");
}
if ($argc < 4 || $argc > 5) {
    fwrite(STDERR, "Usage: php scripts/create_user.php email mot_de_passe school_id [admin|student]\n");
    exit(1);
}
$email = strtolower(trim($argv[1]));
$password = $argv[2];
$schoolId = filter_var($argv[3], FILTER_VALIDATE_INT);
$role = $argv[4] ?? 'student';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Adresse e-mail invalide.\n"); exit(1);
}
if (strlen($password) < 14) {
    fwrite(STDERR, "Utilisez un mot de passe d'au moins 14 caractères.\n"); exit(1);
}
if (!$schoolId || !in_array($role, ['admin','student'], true)) {
    fwrite(STDERR, "Identifiant d'école ou rôle invalide.\n"); exit(1);
}
$stmt = db()->prepare('SELECT id FROM schools WHERE id = ?');
$stmt->execute([$schoolId]);
if (!$stmt->fetch()) { fwrite(STDERR, "Cette école n'existe pas.\n"); exit(1); }

try {
    $stmt = db()->prepare('INSERT INTO users (school_id, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$schoolId, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
    fwrite(STDOUT, "Utilisateur créé avec succès (ID " . db()->lastInsertId() . ").\n");
} catch (PDOException $e) {
    fwrite(STDERR, "Création impossible : adresse déjà utilisée ou erreur de base de données.\n");
    exit(1);
}
