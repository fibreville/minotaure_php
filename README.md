[![Build Status](https://travis-ci.org/fibreville/atrpg.svg?branch=main)](https://travis-ci.org/fibreville/atrpg)
# atrpg

## 🇫🇷 Version française
*English below.*

### AVERTISSEMENT

⚠️ Malgré leur vigilance, les contributeurs du code de ATRPG ne garantissent pas l'absence de failles de sécurité. Utilisation à vos risques et périls.

### À quoi ça sert ?

Ce programme permet de récréer sur votre serveur le JDR « AT RPG » (Asynchronous Tactical Role-Playing Game), qui permet à des centaines de joueurs de participer à la même aventure en votant pour la prochaine action à faire.

L'idée et le code d'origine ont été fournies par [FibreTigre](https://www.twitch.tv/fibretigre) le 31/12/20. Rejoignez le discord de la communauté ici : [https://discord.gg/RAhph7z]

### Installation locale sans Docker

1. Il vous faut un serveur avec PHP et une base SQL
1. Importez la base de données du fichier `database.sql`
1. Renommez le fichier `src/connexion_example.php` en `src/connexion.php`
1. Modifiez-le pour permettre la connexion à la base de données

Exemple : si votre base de donnée est locale, qu'elle s'appelle `base1`, que votre identifiant est `toto` et votre mot de passe `titi`, écrivez :
```php
$db = new PDO('mysql:host=localhost;dbname=base1;', 'toto', 'titi');
```
5. Si vous utilisez la version 2.3 minimum, indiquez le chemin de vos fichiers temporaires à la place de /tmp (cette valeur par défaut marche sur beaucoup d'environnements, vous n'avez pas forcément besoin de la changer).
```php
$tmp_path = '/tmp';
```

Les fichiers dans `/src` n'ont pas besoin d'être modifiés.

### Installation locale avec Docker

Il faut avoir [docker](https://docs.docker.com/get-docker/), [docker-compose](https://docs.docker.com/compose/install/)
et [make](https://fr.wikipedia.org/wiki/Make).

1. Cloner le repo : `git clone https://github.com/fibreville/atrpg.git`
1. Lancer les conteneurs : `make up`
1. Lancer le navigateur et aller sur [http://127.0.0.1:8080]
1. Créer le premier compte qui sera le MJ
1. Faire que son PC soit accessible depuis Internet : Aller sur la box, faire que le port 8080 de la box soit envoyé sur le port 8080 du PC. L'URL pour internet sera alors `http://address-ip:8080`.

* Pour voir les logs : `make logs`
* Pour arrêter les conteneurs : `make down`
* Pour détruire conteneurs et données : `make reset`

### Pour jouer

- Le 1er compte créé sur le jeu est le compte admin (il peut avoir n'importe quel nom).
- Une fois ce compte créé et le joueur/MJ logué, il peut aller sur `ecran.php` et avoir accès à l'ensemble des commandes.
- Le mode de fonctionnement ensuite est détaillé ici : https://www.youtube.com/watch?v=XGU3_dczcNE


## 🇺🇸 English readme

### WARNING

⚠️ This software may contain vulnerabilities. Use at your own risk.

### Purpose

This program lets you create the "AT RPG" (Asynchronous Tactical Role-Playing Game) on your own server where hundreds of players can take decisions collectively by voting. It was designed to be used on Twitch.

The original source was provided by [FibreTigre](https://www.twitch.tv/fibretigre) on 12/31/2020.
Join his community's Discord (in French + 1 English channel): [https://discord.gg/RAhph7z]

### Install without Docker

1. Set up a PHP server with SQL
1. Import the `database.sql` file into you database (3 tables)
1. Rename `src/connexion_example.php` to `src/connexion.php`
1. Edit this file to grant access to the database

Example: if you use a local database named `base1` with login `foo` and password `bar`, change the 3rd line to:
```php
$db = new PDO('mysql:host=localhost;dbname=base1;', 'foo', 'bar');
```

5. Si vous utilisez la version 2.3 minimum, indiquez le chemin de vos fichiers temporaires à la place de /tmp (cette valeur par défaut marche sur beaucoup d'environnements, vous n'avez pas forcément besoin de la changer).
```php
$tmp_path = '/tmp';
```

Other files in `/src` can be used as is.

### Install using Docker

You will need [docker](https://docs.docker.com/get-docker/), [docker-compose](https://docs.docker.com/compose/install/)
and [make](https://fr.wikipedia.org/wiki/Make).

1. Clone the repository: `git clone https://github.com/fibreville/atrpg.git`
1. Run containers: `make up`
1. Open a browser and go to [http://127.0.0.1:8080]
1. Create the first user (the GM)
1. Make your PC accessible from the Public Internet: open your router's configuration in your browser and set your router to forward the port 8080 of your router to your PC. Anyone may now access your server at `http://address-ip:8080`.

* To check the logs, type in a terminal: `make logs`
* To stop the containers: `make down`
* To delete containers and data: `make reset`

### Tests unitaire (pour les developpeurs)

Installation de phpunit
```bash
./scripts/init_tests.sh
```
Lancement des tests
```bash
./phpunit tests/
```

### How to play

- The first account you create will be the admin (it can have any name)
- After logging in with this account, the user may go to `ecran.php` and access all commands
- *Et voilà !*
- Demo video (in French): https://www.youtube.com/watch?v=XGU3_dczcNE
