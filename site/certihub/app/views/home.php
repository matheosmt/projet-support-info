<?php
$schoolTrend = [45,47,52,58,63,68,74,79,84,89];
$schoolTrendLabels = ['2016','2017','2018','2019','2020','2021','2022','2023','2024','2025'];
$domainRows = $pdo->query('SELECT domain, COUNT(*) AS count FROM certifications GROUP BY domain ORDER BY count DESC')->fetchAll();
$maxDomain = max(array_map(fn($row) => (int)$row['count'], $domainRows) ?: [1]);
$globalBenchmarks = [
    ['name' => 'AWS Certified Solutions Architect - Associate', 'short' => 'AWS SAA', 'value' => '≈ 750 k', 'note' => 'estimation mondiale', 'width' => 86],
    ['name' => 'CCNA', 'short' => 'CCNA', 'value' => '≈ 1,0 M', 'note' => 'estimation mondiale', 'width' => 100],
    ['name' => 'CompTIA Security+', 'short' => 'Security+', 'value' => '≈ 1,6 M', 'note' => 'estimation mondiale', 'width' => 100],
];
$certifiedRate = $stats['students'] > 0 ? min(100, (int)round(($stats['certified_students'] / $stats['students']) * 100)) : 0;
$schoolTrendMax = max($schoolTrend ?: [1]);
$trendPoints=[];
$trendCount=count($schoolTrend);
$trendMin=35; $trendMaxScale=100;
foreach($schoolTrend as $i=>$value){
    $x = 12 + ($trendCount > 1 ? $i * 276 / ($trendCount - 1) : 0);
    $y = 118 - ((($value - $trendMin) / ($trendMaxScale - $trendMin)) * 88);
    $trendPoints[] = round($x,1).','.round($y,1);
}
?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <div class="eyebrow">CERTIFICATIONS · FORMATIONS · PROJETS</div>
            <h1>Construis un CV qui <span>prouve</span> ce que tu sais faire.</h1>
            <p class="hero-copy">CertiHub aide les étudiants à trouver les bonnes certifications, suivre leur progression et accéder aux ressources de l’école.</p>
            <div class="hero-actions"><a class="btn btn-primary" href="/certifications">Explorer les certifications</a><a class="btn btn-ghost" href="/parcours">Voir les parcours types</a></div>
            <div class="hero-note"><span>●</span> Cloud · Cyber · Réseau · Dev · Data · IA · Système</div>
        </div>
        <div class="hero-card school-snapshot">
            <div class="mini-label">CERTIFICATIONS À L'ÉCOLE</div>
            <div class="progress-big school-big"><span><?= $stats['certified_students'] ?></span><small>élève<?= $stats['certified_students'] > 1 ? 's' : '' ?> certifié<?= $stats['certified_students'] > 1 ? 's' : '' ?></small></div>
            <div class="snapshot-meter"><span style="width:<?= $certifiedRate ?>%"></span></div>
            <div class="stats-line"><span><?= $stats['certified_students'] ?> / <?= $stats['students'] ?> étudiant<?= $stats['students'] > 1 ? 's' : '' ?></span><span><?= $certifiedRate ?>%</span></div>
            <div class="school-mini"><div><b><?= $stats['certified'] ?></b><small>certification<?= $stats['certified'] > 1 ? 's' : '' ?> obtenue<?= $stats['certified'] > 1 ? 's' : '' ?></small></div><span class="chip success">≥ 1 certif.</span></div>
            <div class="school-mini"><div><b><?= $stats['certs'] ?></b><small>certifications disponibles</small></div><span class="chip neutral"><?= $stats['domains'] ?> domaines</span></div>
        </div>
    </div>
</section>
<section class="section intro-stats">
    <div class="container stat-grid home-kpis">
        <div class="stat-card"><strong><?= $stats['certified_students'] ?></strong><span>élèves avec ≥ 1 certification</span></div>
        <div class="stat-card"><strong><?= $stats['certified'] ?></strong><span>certifications obtenues dans l’école</span></div>
        <div class="stat-card"><strong><?= $stats['certs'] ?></strong><span>certifications référencées</span></div>
        <div class="stat-card"><strong><?= $stats['domains'] ?></strong><span>domaines IT couverts</span></div>
    </div>
</section>
<section class="section home-analytics">
    <div class="container">
        <div class="section-head"><div><div class="eyebrow">REPÈRES</div><h2>Quelques chiffres pour se situer</h2></div><span class="analytics-note">Les volumes mondiaux sont des estimations de maquette.</span></div>
        <div class="analytics-grid">
            <article class="analytics-card trend-card">
                <div class="analytics-title"><div><span class="eyebrow">MARCHÉ IT</span><h3>Tendance & importance professionnelle</h3></div><span class="mini-badge">10 ans</span></div>
                <div class="trend-value"><strong><?= end($schoolTrend) ?>/100</strong><span>indice de tendance professionnelle<br>des certifications IT</span></div>
                <div class="trend-chart-wrap">
                    <svg class="trend-chart" viewBox="0 0 300 145" role="img" aria-label="Indice illustratif de tendance et importance professionnelle des certifications IT de 2016 à 2025">
                        <defs><linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#66dfb7" stop-opacity="0.28"/><stop offset="100%" stop-color="#66dfb7" stop-opacity="0"/></linearGradient></defs>
                        <line x1="12" y1="118" x2="288" y2="118" stroke="rgba(159,211,232,.14)"/>
                        <line x1="12" y1="89" x2="288" y2="89" stroke="rgba(159,211,232,.08)"/>
                        <line x1="12" y1="60" x2="288" y2="60" stroke="rgba(159,211,232,.08)"/>
                        <line x1="12" y1="30" x2="288" y2="30" stroke="rgba(159,211,232,.08)"/>
                        <polygon points="<?= implode(' ', array_merge($trendPoints, ['288,118','12,118'])) ?>" fill="url(#areaGrad)"/>
                        <polyline points="<?= implode(' ', $trendPoints) ?>" fill="none" stroke="#66dfb7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <?php foreach($trendPoints as $pt): [$cx,$cy]=array_map('floatval',explode(',',$pt)); ?>
                            <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="3.5" fill="#061525" stroke="#66dfb7" stroke-width="2"/>
                        <?php endforeach; ?>
                    </svg>
                    <div class="trend-labels"><?php foreach($schoolTrendLabels as $label): ?><span><?= e($label) ?></span><?php endforeach; ?></div>
                </div>
                <small class="chart-footnote">Indice de maquette destiné à visualiser l’évolution de la place des certifications IT dans le monde professionnel ; les valeurs ne sont pas des statistiques officielles.</small>
            </article>
            <article class="analytics-card domain-card">
                <div class="analytics-title"><div><span class="eyebrow">CATALOGUE</span><h3>Répartition par domaine</h3></div><span class="mini-badge"><?= $stats['certs'] ?> certifs</span></div>
                <div class="domain-bars">
                    <?php foreach($domainRows as $row): $pct = max(8, (int)round(((int)$row['count'] / $maxDomain) * 100)); ?>
                    <div class="domain-bar-row"><div><span><?= e(domain_label($row['domain'])) ?></span><b><?= (int)$row['count'] ?></b></div><div class="bar-track"><i style="width:<?= $pct ?>%"></i></div></div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>
    </div>
</section>
<section class="section section-global">
    <div class="container">
        <div class="section-head"><div><div class="eyebrow">MONDE IT</div><h2>3 certifications qui servent de repères</h2></div><span class="analytics-note">Ordres de grandeur pour la démo, pas un compteur officiel.</span></div>
        <div class="global-grid">
            <?php foreach($globalBenchmarks as $item): ?>
            <article class="global-card">
                <div class="global-head"><span class="vendor-label">Benchmark IT</span><span class="global-pill">MONDE</span></div>
                <h3><?= e($item['short']) ?></h3>
                <p><?= e($item['name']) ?></p>
                <div class="global-number"><strong><?= e($item['value']) ?></strong><span><?= e($item['note']) ?></span></div>
                <div class="global-bar"><i style="width:<?= (int)$item['width'] ?>%"></i></div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-head"><div><div class="eyebrow">À LA UNE</div><h2>Certifications à regarder</h2></div><a class="text-link" href="/certifications">Tout le catalogue</a></div>
        <div class="card-grid three cert-grid">
            <?php foreach($featured as $cert): ?>
            <?php $difficulty = $cert['difficulty'] ?? 'intermediaire'; ?>
            <article class="cert-card glass-<?= e($cert['domain']) ?>">
                <div class="card-top"><span class="difficulty <?= e(difficulty_class($difficulty)) ?>"><i></i><?= e(difficulty_label($difficulty)) ?></span><span class="price-pill"><?= e(format_price((int)($cert['price_eur'] ?? 0))) ?></span></div>
                <h3><?= e($cert['name']) ?></h3>
                <p><?= e($cert['summary']) ?></p>
                <div class="cert-meta-grid"><div><span>Prix</span><b><?= e(format_price((int)($cert['price_eur'] ?? 0))) ?></b></div><div><span>Certifiés</span><b><?= number_format((int)$cert['certified_count'],0,' ',' ') ?></b></div></div>
                <div class="cert-card-bottom"><span class="cert-icon large vendor-label"><?= e($cert['vendor']) ?></span><a class="card-link" href="/certification?id=<?= (int)$cert['id'] ?>">Voir le détail</a></div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="section section-soft">
  <div class="container feature-grid">
    <div><div class="eyebrow">POUR L'ÉCOLE</div><h2>Une même boussole pour les étudiants et les équipes.</h2><p>Le portail centralise les certifications, les parcours conseillés par le Student Service, les ressources pédagogiques et le support.</p><a class="btn btn-dark" href="/support">Ouvrir un ticket</a></div>
    <div class="feature-panel"><div class="feature-row"><span>01</span><div><b>Choisir</b><small>Filtrer par domaine et niveau.</small></div></div><div class="feature-row"><span>02</span><div><b>Se préparer</b><small>Accéder aux cours et labs de l'école.</small></div></div><div class="feature-row"><span>03</span><div><b>Se certifier</b><small>Déclarer l'obtention et enrichir son profil.</small></div></div><div class="feature-row"><span>04</span><div><b>Être accompagné</b><small>Discuter sous chaque certification et contacter le support.</small></div></div></div>
  </div>
</section>
