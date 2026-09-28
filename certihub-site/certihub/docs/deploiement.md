# Déploiement

## Option locale simple

PHP 8.1+ avec `pdo_sqlite` activé :

```bash
php -S localhost:8000 -t public
```

La base SQLite de démonstration est initialisée automatiquement.

## Option recommandée pour la production

Utiliser PHP-FPM + Nginx + MySQL/MariaDB ou PostgreSQL dans des réseaux séparés.

Variables :

```text
CERTIHUB_DSN=mysql:host=db.internal;dbname=certihub;charset=utf8mb4
CERTIHUB_DB_USER=certihub_app
CERTIHUB_DB_PASS=<secret>
```

Accorder au compte applicatif uniquement les permissions nécessaires sur le schéma applicatif. Le serveur de base ne doit pas être joignable depuis Internet.

## Extension PHP nécessaire

Pour le prototype SQLite : `pdo_sqlite`. Pour la production MySQL : `pdo_mysql`.
