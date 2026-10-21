# Classcloud — backend PHP/MySQL + Proxmox VE (MVP)

Cette base fournit :
- authentification PHP avec sessions et mots de passe hachés ;
- association obligatoire utilisateur → école ;
- filtrage des ressources par `school_id` côté serveur ;
- protections CSRF et requêtes PDO préparées ;
- quotas simples ;
- déploiement depuis des modèles Proxmox VE configurés (VM QEMU ou conteneur LXC) ;
- aucune clé Proxmox envoyée au navigateur.

## Important avant toute utilisation

C'est un socle de projet BTS, pas une plateforme cloud prête pour Internet. Testez d'abord sur un Proxmox de laboratoire avec des modèles non sensibles. Le compte API Proxmox doit avoir uniquement les droits nécessaires sur les modèles et les ressources gérées. N'exposez jamais l'API Proxmox directement aux élèves. Gardez la vérification TLS activée.

## Arborescence

- `public/` : seuls fichiers exposés par Apache/Nginx
- `app/` : code PHP non accessible directement depuis le web
- `database/schema.sql` : tables
- `scripts/create_user.php` : création d'un utilisateur depuis la ligne de commande
- `config/config.example.php` : configuration à copier vers `config/config.php`

## 1. Prérequis

Sur Debian/Ubuntu, installez Apache ou Nginx avec PHP, PHP-FPM si nécessaire, les extensions `pdo_mysql`, `curl`, `json`, `openssl`, et MySQL/MariaDB. Les commandes exactes dépendent de la configuration du serveur.

## 2. Installation

1. Placez le projet dans un répertoire comme `/var/www/classcloud`.
2. Configurez le document root du serveur web sur `/var/www/classcloud/public` uniquement.
3. Copiez `config/config.example.php` vers `config/config.php`.
4. Modifiez les identifiants SQL, l'URL de Proxmox, le nœud et les VMID des modèles.
5. Importez le schéma :

   `mysql -u classcloud_app -p classcloud < database/schema.sql`

6. Protégez la configuration et ne la rendez pas lisible par le serveur web public.
7. Créez les écoles dans MySQL, puis créez un utilisateur avec le script CLI :

   `php scripts/create_user.php admin@ecole.test "MotDePasseLongEtUnique" 1 admin`

   Le dernier argument est le rôle (`admin` ou `student`) et `1` est l'identifiant d'école existant.
8. Configurez Proxmox et testez avec un compte de laboratoire.

## 3. Configuration Proxmox

Dans `config/config.php`, renseignez :
- `base_url` : URL HTTPS de l'API, par exemple `https://proxmox.example.test:8006/api2/json`
- `token_id` : identifiant complet du token, par exemple `classcloud@pve!api`
- `token_secret` : secret du token
- `verify_tls` : doit rester `true` en production
- `node` : nom exact du nœud Proxmox
- `templates.vm` : VMID du modèle QEMU préparé
- `templates.lxc` : VMID du modèle LXC préparé

Le modèle VM doit être un modèle QEMU clonable. Le modèle LXC doit être un conteneur LXC clonable. Les modèles doivent être préparés par l'administrateur Proxmox. L'API token nécessite des permissions adaptées aux opérations de lecture, clonage et démarrage. Les permissions précises varient selon la version et la configuration Proxmox : commencez avec un rôle dédié minimal et vérifiez les journaux d'audit.

Le déploiement appelle l'API Proxmox. Selon le stockage et le modèle, le clonage peut prendre du temps. Cette première version attend la fin de la tâche avec un délai maximum ; un système de file de travaux serait préférable pour une production.

## 4. Sécurité et limites

- Un utilisateur ne peut consulter que les ressources de son école.
- Un étudiant ne peut agir que sur une ressource appartenant à son école.
- Le rôle admin dans cette version reste limité à son école ; il ne s'agit pas d'un administrateur global.
- Ne donnez jamais `root` ou un compte administrateur complet de Proxmox à l'application.
- Les quotas sont simples et ne remplacent pas une gestion de capacité globale.
- L'isolation logique dans la base ne configure pas à elle seule les VLAN ni le réseau Proxmox.
- Les IP sont laissées vides : il faut une stratégie IPAM et une configuration réseau adaptées avant de les attribuer.
- La suppression de ressources n'est pas implémentée.
- Pour une production : HTTPS, sauvegardes, limitation de débit, journaux d'audit, file de tâches, alertes, rotation des secrets et tests de sécurité sont nécessaires.

## 5. Intégration avec l'interface existante

Les pages dynamiques utilisent `.php` :
- `public/index.php`
- `public/dashboard.php`
- `public/deploy.php`

Remplacez les liens `.html` par les noms `.php`. L'ancien HTML peut servir de référence, mais ne doit pas être utilisé comme mécanisme d'authentification.

