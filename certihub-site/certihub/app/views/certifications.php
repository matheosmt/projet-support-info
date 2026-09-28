<section class="page-head"><div class="container"><div class="eyebrow">CATALOGUE</div><h1>Certifications IT</h1><p>Une vue simple pour trouver une certification adaptée à ton domaine et à ton niveau.</p></div></section>
<section class="section"><div class="container">
<form class="filters" method="get"><div class="search-box"><span>⌕</span><input name="q" value="<?= e($q) ?>" placeholder="Rechercher AWS, Cisco, sécurité…"></div><select name="domaine"><option value="">Tous les domaines</option><?php foreach($domains as $d): ?><option value="<?= e($d) ?>" <?= $domain===$d?'selected':'' ?>><?= e(domain_label($d)) ?></option><?php endforeach; ?></select><button class="btn btn-dark">Filtrer</button></form>
<div class="results-count"><?= count($certifications) ?> certification(s)</div>
<div class="card-grid three cert-grid"><?php foreach($certifications as $cert): ?>
<?php $difficulty = $cert['difficulty'] ?? 'intermediaire'; ?>
<article class="cert-card glass-<?= e($cert['domain']) ?>">
    <div class="card-top">
        <span class="chip domain-<?= e($cert['domain']) ?>"><?= e(domain_label($cert['domain'])) ?></span>
        <span class="difficulty <?= e(difficulty_class($difficulty)) ?>"><i></i><?= e(difficulty_label($difficulty)) ?></span>
    </div>
    <h3><?= e($cert['name']) ?></h3>
    <p><?= e($cert['summary']) ?></p>
    <div class="cert-meta-grid">
        <div><span>Prix indicatif</span><b><?= e(format_price((int)($cert['price_eur'] ?? 0))) ?></b></div>
        <div><span>Certifiés</span><b><?= number_format((int)$cert['certified_count'],0,' ',' ') ?></b></div>
    </div>
    <div class="cert-card-bottom">
        <span class="cert-icon large vendor-label"><?= e($cert['vendor']) ?></span>
        <a class="card-link" href="/certification?id=<?= (int)$cert['id'] ?>">Voir le détail</a>
    </div>
</article>
<?php endforeach; ?></div>
</div></section>
