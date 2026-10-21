# Sécurité applicative

## Déjà présent dans le prototype

- cookies de session HttpOnly + SameSite ;
- régénération d'identifiant de session à la connexion ;
- PDO avec requêtes préparées ;
- échappement HTML côté rendu ;
- token CSRF sur les actions POST ;
- contrôle de rôle pour l'administration ;
- mots de passe stockés sous forme de hash `password_hash()` ;
- secret TOTP et vérification sur fenêtre courte pour la 2FA de démonstration.

## À compléter

- politique CSP, HSTS, `Secure` obligatoire en production ;
- rate limiting et verrouillage progressif des tentatives ;
- anti-brute-force / anti-credential stuffing ;
- modération et anti-spam des discussions ;
- audit des droits et séparation des responsabilités ;
- journalisation vers SIEM ;
- scans SAST/DAST et dépendances verrouillées ;
- tests d'intrusion avant ouverture aux élèves.
