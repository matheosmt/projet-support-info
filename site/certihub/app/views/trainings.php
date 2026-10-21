<?php
$trainingDomains = [
    ['cloud','Cours Cloud','Bases cloud, architecture, infrastructure as code et plateformes.'],
    ['cyber','Cours Cybersécurité','Fondamentaux sécurité, réseau, IAM, défense et bonnes pratiques.'],
    ['reseau','Cours Réseau','Adressage, switching, routing, VLAN et dépannage réseau.'],
    ['dev','Cours Développement','Programmation, API, Git, CI/CD et pratiques de développement modernes.'],
    ['data','Cours Data','SQL, modélisation, analyse, pipelines et fondamentaux data engineering.'],
    ['ia','Cours IA','Python, machine learning, IA générative et mise en pratique de modèles.'],
    ['systeme','Cours Système','Linux, administration, services, automatisation et supervision.'],
];
?>
<section class="page-head"><div class="container"><div class="eyebrow">LEARNING</div><h1>Formations de l’école</h1><p>Retrouve les cours par domaine. Les contenus détaillés seront ajoutés progressivement.</p></div></section>
<section class="section"><div class="container"><div class="section-head"><div><div class="eyebrow">COURS</div><h2>Apprendre par domaine</h2></div><span class="analytics-note">Contenus en préparation</span></div><div class="domain-course-grid"><?php foreach($trainingDomains as $course): ?><article class="course-domain-card"><span class="course-domain-label"><?= e(domain_label($course[0])) ?></span><div><h3><?= e($course[1]) ?></h3><p><?= e($course[2]) ?></p></div><span class="course-status">Bientôt disponible</span></article><?php endforeach; ?></div></div></section>