<section class="hero">
    <div class="container hero-grid">
        <div>
            <div class="eyebrow">CERTIFICATIONS · FORMATIONS · PROJETS</div>
            <h1>Construis un CV qui <span>prouve</span> ce que tu sais faire.</h1>
            <p class="hero-copy">CertiHub aide les étudiants à trouver les bonnes certifications, suivre leur progression et accéder aux ressources de l’école.</p>
            <div class="hero-actions"><a class="btn btn-primary" href="/certifications">Explorer les certifications</a><a class="btn btn-ghost" href="/parcours">Voir les parcours types</a></div>
            <div class="hero-note"><span>●</span> Cloud · Cyber · Réseau · Dev · Data · IA · Système</div>
        </div>
        <div class="hero-card">
            <div class="mini-label">PROGRESSION ÉLÈVE</div>
            <div class="progress-big"><span>68%</span><small>Cloud Foundations</small></div>
            <div class="meter"><i style="width:68%"></i></div>
            <div class="stats-line"><span>8 modules</span><span>5 terminés</span></div>
            <div class="cert-mini"><span class="cert-icon">AWS</span><div><b>Cloud Practitioner</b><small>Examen recommandé</small></div><span class="chip success">En route</span></div>
            <div class="cert-mini"><span class="cert-icon">CC</span><div><b>CCNA</b><small>Réseau · Intermédiaire</small></div><span class="chip neutral">À planifier</span></div>
        </div>
    </div>
</section>
<section class="section intro-stats">
    <div class="container stat-grid">
        <div class="stat-card"><strong><?= $stats['certs'] ?></strong><span>certifications cataloguées</span></div>
        <div class="stat-card"><strong><?= $stats['domains'] ?></strong><span>domaines IT</span></div>
        <div class="stat-card"><strong><?= $stats['students'] ?></strong><span>comptes étudiants de démo</span></div>
        <div class="stat-card"><strong><?= $stats['certified'] ?></strong><span>certifications obtenues</span></div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-head"><div><div class="eyebrow">À LA UNE</div><h2>Certifications à regarder</h2></div><a class="text-link" href="/certifications">Tout le catalogue</a></div>
        <div class="card-grid three cert-grid">
            <?php foreach($featured as $cert): ?>
            <?php $difficulty = $cert['difficulty'] ?? 'intermediaire'; ?>
            <article class="cert-card glass-<?= e($cert['domain']) ?>">
                <div class="card-top"><span class="chip domain-<?= e($cert['domain']) ?>"><?= e(domain_label($cert['domain'])) ?></span><span class="difficulty <?= e(difficulty_class($difficulty)) ?>"><i></i><?= e(difficulty_label($difficulty)) ?></span></div>
                <h3><?= e($cert['name']) ?></h3>
                <p><?= e($cert['summary']) ?></p>
                <div class="cert-meta-grid"><div><span>Prix indicatif</span><b><?= e(format_price((int)($cert['price_eur'] ?? 0))) ?></b></div><div><span>Certifiés</span><b><?= number_format((int)$cert['certified_count'],0,' ',' ') ?></b></div></div>
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
