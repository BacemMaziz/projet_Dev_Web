ActuMeteo - Site de prévision météo pour la France

Description

ActuMeteo est un site web dédié à la prévision météorologique en France, offrant des informations détaillées sur la météo par région, département et commune. Le site intègre des APIs météorologiques (comme WeatherAPI) et NASA pour fournir des données en temps réel, ainsi qu'une interface utilisateur interactive avec une carte des régions françaises, des bannières dynamiques, et des options de personnalisation (mode jour/nuit, gestion des cookies).

Auteurs





Bacem Maziz



Hamlat Arslane

Année





2024

URL du site





ActuMeteo (URL fictive, à remplacer par l'URL réelle si disponible)

Fonctionnalités principales





Prévisions météo : Affichage des prévisions météo par commune, avec vues générale et détaillée (par heure).



Géolocalisation : Détection automatique de la localisation via l'API ipinfo.io.



Carte interactive : Navigation par régions et départements avec des cartes statiques.



Personnalisation : Mode jour/nuit et gestion des préférences de cookies.



Statistiques : Suivi des visites par ville (via villeconsult.csv) et affichage dans statistiques.php.



API NASA : Contenu additionnel dans developper.php utilisant des données de la NASA.



Contact : Formulaire de contact dans contact.php.

Structure du projet

Le projet est organisé comme suit :





Dossier include/ :





header.inc.php : En-tête commun avec gestion du thème (jour/nuit).



footer.inc.php : Pied de page avec animations (flocons de neige) et compteur de visites.



functions.php : Fonctions utilitaires, incluant les appels aux APIs météo et la gestion des données.



Dossier Images/ :





Contient la carte de France (france.png) et les cartes des régions (region-*.png).



Dossier picture/ :





Images des bannières dynamiques qui changent à chaque actualisation.



Pages PHP :





index.php : Page d'accueil avec carte interactive, géolocalisation, et prévisions météo.



statistiques.php : Statistiques des villes les plus visitées.



developper.php : Page utilisant les APIs de la NASA.



contact.php : Formulaire de contact.



Fichiers CSS :





jour.css : Style pour le thème clair.



nuit.css : Style pour le thème sombre.



Fichier JavaScript :





script.js : Animations et interactions (menu hamburger, carte interactive, etc.).



Fichiers CSV :





nbvisite.csv : Compteur global des visites.



v_departement_2024.csv : Liste des départements.



v_region_2024.csv : Liste des régions.



villeconsult.csv : Données des visites par ville pour les statistiques.

Prérequis





Serveur web avec PHP (version 7.4 ou supérieure recommandée).



Accès à Internet pour les appels aux APIs (WeatherAPI, ipinfo.io, NASA).



Clés API valides pour WeatherAPI et NASA.

Installation
