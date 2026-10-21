<?php
declare(strict_types=1);
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function active(string $path): string { $current=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'','/'); return $current===trim($path,'/')?'active':''; }
function domain_label(string $d): string { return ['cyber'=>'Cybersécurité','reseau'=>'Réseau','cloud'=>'Cloud','dev'=>'Développement','data'=>'Data','ia'=>'IA','systeme'=>'Système'][$d]??ucfirst($d); }
function status_label(string $s): string { return ['obtenue'=>'Obtenue','en_cours'=>'En cours','a_planifier'=>'À planifier'][$s]??ucfirst(str_replace('_',' ',$s)); }
function difficulty_label(string $d): string { return ['debutant'=>'Débutant','intermediaire'=>'Intermédiaire','avance'=>'Avancé'][$d]??ucfirst($d); }
function difficulty_class(string $d): string { return 'difficulty-' . preg_replace('/[^a-z]/', '', strtolower($d)); }
function format_price(int $price): string { return $price > 0 ? number_format($price, 0, ',', ' ') . ' €' : 'Gratuit'; }
function vendor_short(string $vendor): string {
    return [
        'AWS' => 'AWS',
        'Cisco' => 'CS',
        'CompTIA' => 'CT',
        'Microsoft' => 'MS',
        'Google Cloud' => 'GC',
        'ISC2' => 'I2',
        'Linux Foundation' => 'LF',
        'PECB' => 'PB',
        'Oracle' => 'OR',
        'Databricks' => 'DB',
        'GitLab' => 'GL',
        'Red Hat' => 'RH',
        'HashiCorp' => 'HC',
        'Cloud Native Computing Foundation' => 'CNCF',
        'OffSec' => 'OS',
        'EC-Council' => 'EC',
        'ISACA' => 'IC',
    ][$vendor] ?? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $vendor), 0, 2));
}
