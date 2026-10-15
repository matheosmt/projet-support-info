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


- **Problématique** : l'école incite ses élèves à se former aussi en autonomie et de passer des certifications. Le problème est qu'il existe énormément de certifications dans l'informatique. Beaucoup d'élèves sont donc rapidement perdus : quel certification passer ? Comment faire ? Comment savoir si j'ai le niveau ? Quelles sont les certifications reconnues dans le milieu professionnel ? etc


- **Objectif** : mettre en place une app qui regroupe les certifications en fonction du domaine souhaité :
	- avoir une app avec les certifications regroupées par domaine 
	- avoir pour chaque certification un résumé + les prérequis pour la passer 
	- avoir un graphique avec des données telles que : combien sont certifiés avec cette certification, les nouvelles certifications...
	- mettre en place un groupe de certifications interessantes à avoir en fonction des formations et des parcours (exemple après un bts sio, après un bachelor cyber, après une école d'inge en cloud etc)
	- mettre en place un chat global sous chaque certification (sécurisé et filtrer) pour que chacun puissent poser ses questions et avoir l'avis d'expert (expert aws, expert cisco ou autre)
	- avoir une partie d'actualité dans le monde de l'informatique, les grands points en cyber, réseau, cloud, dev, data et ia.
	- Integrer une partie formation pour les certifications -> cours de l'école pour se former et avoir le niveau requis 
	- possibilité de creer un compte et de voir son avancé et ses certifications.
	- mettre en place des accès pour des devs et des admins avec des droits différents sur l'app.
	- mettre en place un SI sécurisé avec une base de données, isolées et une DMZ avec le serveur web pour le front 

- **Utilisateurs** : 
	- Directeur : avoir une vision globale des certifications du marché.
	- Élèves : se créer un compte + avoir accès à différentes ressources -> certif, formations, actualité, avis 
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
		- assurer la disponibilité de l'app
	- Administration / Support informatique 
		- gérer les droits en fonction des utilisateurs 
		- gérer les tickets fait par les utilisateurs (élèves)


- **Contraintes** :
	- développer une application web reliée à une base de données
	- documentation de tout le SI 
	- sécuriser les données des élèves ainsi que celle de l'école
	- gérer les accès aux ressources en fonction des utilisateurs 

