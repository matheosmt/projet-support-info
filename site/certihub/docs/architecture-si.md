# Architecture SI proposée

```text
                         INTERNET
                             |
                    [Pare-feu / WAF]
                             |
                         [DMZ VLAN]
                             |
                     [Reverse proxy]
                             |
                    [Serveur Web PHP]
                             |
                  (flux DB strictement sortant)
                             v
                     [VLAN APP PRIVÉ]
                             |
                      [API / Services]
                             |
                     [VLAN BDD PRIVÉ]
                             |
                     [MySQL/PostgreSQL]

  VLAN élèves       VLAN admin       VLAN supervision
      |                 |                   |
    postes          bastion/VPN        SIEM/monitoring
```

## Segmentation

- DMZ : reverse proxy + serveur web exposé.
- VLAN APP : logique métier, non exposé directement à Internet.
- VLAN BDD : base isolée, aucune route utilisateur vers ce VLAN.
- VLAN ADMIN : postes d'administration et bastion.
- VLAN SUPERVISION : monitoring, logs et alertes.

## Accès

Les élèves se connectent via HTTPS depuis Internet. Le VPN sert aux ressources internes de l'école. Les administrateurs passent par un bastion et MFA.

## Données sensibles

Les mots de passe doivent être hachés avec Argon2id/bcrypt. Les sauvegardes doivent être chiffrées. Les logs doivent éviter les secrets, tokens et données personnelles inutiles.
