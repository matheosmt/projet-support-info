# CertiHub — V3 redesign — portail certifications IT

MVP PHP/SQLite/CSS/JS conçu à partir du cahier des charges fourni : catalogue de certifications, fiches détaillées, parcours types, formations, actualités, compte étudiant, progression, discussions et support/admin.

## Lancer localement

Prérequis : PHP 8.1+ avec PDO SQLite.

```bash
cd public
php -S localhost:8000
```

Puis ouvrir `http://localhost:8000`.

La base `storage/certihub.sqlite` est créée automatiquement au premier démarrage.

### Comptes de démonstration

- Élève : `alice@certihub.local` / `DemoStudent123!`
- Admin : `directeur@certihub.local` / `DemoAdmin123!`
- Support : `support@certihub.local` / `DemoSupport123!`

## Arborescence

- `public/index.php` : routeur + contrôleurs simples
- `public/assets/` : CSS + JavaScript
- `app/core/` : authentification, base de données, helpers
- `app/views/` : vues PHP
- `database/schema.sql` : schéma SQLite
- `docs/` : architecture SI, sécurité et mise en production

## À renforcer avant production

- remplacer SQLite par PostgreSQL/MySQL isolé sur un VLAN base de données ;
- placer le serveur web/app dans une DMZ et interdire les connexions entrantes vers la DB depuis Internet ;
- activer HTTPS partout, HSTS, CSP, rate limiting et journalisation centralisée ;
- mettre le SSO de l'école ou un fournisseur d'identité, avec MFA obligatoire ;
- appliquer une RBAC plus fine (directeur, responsable formation, student service, CM, comptable, support, admin) ;
- ajouter antivirus/anti-spam et modération des messages ;
- mettre en place sauvegardes chiffrées, supervision, alertes et PRA/PCA ;
- utiliser une librairie TOTP auditée et une procédure de récupération MFA ;
- intégrer une vraie source de statistiques de certifications et une source d'actualités validée.


## Direction visuelle V3

Le catalogue utilise un fond bleu marine, des cartes translucides teintées par domaine, des icônes monochromes en bas des cartes, un point de couleur pour la difficulté et un prix indicatif.

Les prix et statistiques présents dans les données de démonstration sont indicatifs et doivent être remplacés/actualisés avant mise en production.
