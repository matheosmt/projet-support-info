#Support

- **Entreprise** : Ecole en informatique


- **Activité** : Former des étudiants aux métiers de l'informatique. Propose différents parcours avec différents diplômes. L'école pousse ses élèves à faire des projets perso en dehors des cours pour monter en compétences et de passer des certifications par la suite pour avoir un bon CV à la sortie d'école et donc avoir un plus comparé aux autres étudiants.


- **Organisation** (11 employés) :
	- 1 Directeur : directeur de l'entreprise
	- 4 Résponsable de formation : participe au bon déroulement des formations de l'école, gère les problématique des étudiants, gère la communication élèves/prof
	- 1 Pôle Student service (4 personne): gèrent l'orientation des élèves, conseillent les élèves, répond aux besoins des élèvent durant leur vie à l'école
	- 1 Comptables : gérent les factures, gestion de la tresorerie, gère les contrats de travail etc
	- 2 Community manager : gère la visibilité de l'école sur les RS, suivent les stats de l'agence
	- Élèves 


- **Problématique** : l'école incite ses élèves à se former aussi en autonomie. Le problème est que beaucoup ne savent pas comment faire et où le faire. Comment monter une infra externe ? Sur quoi ? Gratuitement ?


- **Objectif** : mettre en place un cloud pour les élèves dans lequel ils peuvent monter une infra et monter en compétences dessus (un AWS ou AZURE gratuit) :
	- avoir une app pour lancer des VM, containers, routeurs, switchs, base de données, pare-feu etc
	- pouvoir tester son infra dans un environnement de test sécurisé
	- avoir accès à de nombreuses images iso 
	

- **Utilisateurs** : 
	- Directeur : avoir une vision globale des outils du marché.
	- Élèves : se créer un compte + avoir accès à différentes ressources -> monter en compétences
	- Student service : proposer des parcours type pour les élèves
	- Community manager : point à mettre en avant comparé aux autres écoles


- **Fonctionnalités** :
	- Site internet :
		- développer un frontend avec toutes les fonctionnalités disponibles dessus (vues au dessus)
	- Sécurité :
		- segmenter le réseau (DMZ et VLANs)
		- monitorer le réseau
		- mettre en place une double authentification + VPN (pour que les élèves puissent y accéder même en dehors de l'école)
	- Disponibilité :
		- assurer la haute disponibilité de l'app
	- Administration / Support informatique 
		- gérer les droits en fonction des utilisateurs 
		- gérer les tickets fait par les utilisateurs (élèves)


- **Contraintes** :
	- développer une application web reliée à une base de données
	- documentation de tout le SI 
	- sécuriser les données des élèves ainsi que celle de l'école
	- gérer les accès aux ressources en fonction des utilisateurs 

