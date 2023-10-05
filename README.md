# Minotaure PHP
# DEPRECATED

## 🇫🇷 Version française
*English below.*

### AVERTISSEMENT

⚠️ Cette version de Minotaure n'est plus maintenue.
Nous vous encourageons à utiliser la [nouvelle application Minotaure](https://github.com/uurshin/minotaure) et à y contribuer vos développements.

### À quoi ça sert ?

Ce programme, installé sur un serveur, permet à des centaines de joueurs de participer à la même aventure en votant notamment pour la prochaine action à faire.

L'idée et le code d'origine ont été fournies par [FibreTigre](https://www.twitch.tv/fibretigre) le 31/12/2020 sous le nom de ATRPG. Il a ensuite été repris et amélioré ici par les bénévoles. Il a été renommé "MINOTAURE" officiellement le 28/03/2021.

Découvrez des streams utilisant MINOTAURE sur le site [minotau.re](https://minotau.re).

Rejoignez le [discord](https://discord.gg/RAhph7z) de la communauté FibreTigre ou bien le discord dédié [ici](https://discord.gg/zgRfSEC2).

### Installation locale sans Docker

1. Il vous faut un serveur avec PHP et une base SQL
1. Importez la base de données du fichier `database.sql`
1. Renommez le fichier `src/connexion_example.php` en `src/connexion.php`
1. Modifiez-le pour permettre la connexion à la base de données

Exemple : si votre base de donnée est locale, qu'elle s'appelle `base1`, que votre identifiant est `toto` et votre mot de passe `titi`, écrivez :
```php
$host = 'localhost'; // Host name.
$dbname = 'base1'; // Name.
$port = 3306; // Port.
$login = 'toto'; // Login.
$mdp = 'titi'; // Password.
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

### Ma langue ne fonctionne pas

Si vous utilisez une autre langue que le francais et que vous rencontrez le message "Langue indisponible" en cliquant sur la langue sur la page d'accueil, il est possible que votre serveur soit configuré avec d'autres valeur que nos valeurs par défaut. Vous pouver relier chaque bouton à la langue installée sur votre serveur en indiquant sa valeur en face de chaque clef de language. 
Exemple : si votre serveur a la langue en_NZ.UTF8 installée vous pouvez l'indiquer en face de la clef "en" pour dire que l'anglais utilise en_NZ.UTF8. Pour savoir les langues installées sur votre serveur, tappez "locale -a" dans votre console.
```php
$languages = [
  'en' => 'en_NZ.UTF8',
];
```
Si votre langue n'apparait pas sur la page d'accueil, c'est que nous n'avons pas encore traduit Minotaure dans cette langue. Vous pouvez créer une issue sur github pour signifier votre intérêt pour cette langue.

### Pour jouer

- Le 1er compte créé sur le jeu est le compte admin (il peut avoir n'importe quel nom).
- Une fois ce compte créé et le joueur/MJ logué, il peut aller sur `ecran.php` et avoir accès à l'ensemble des commandes.
- Le mode de fonctionnement ensuite est détaillé ici : https://www.youtube.com/watch?v=XGU3_dczcNE

## 🇺🇸 English readme

### WARNING

### AVERTISSEMENT

⚠️ This version of Minotaure is no longer maintained.
We encourage you to use the [new Minotaure app](https://github.com/uurshin/minotaure) and to contribute your code there.

### Purpose

This program, installed on a server, allow several hundred players to live the same adventure by voting on the next action to do for the group, among other things.

The original code was publicly delivered by [FibreTigre](https://www.twitch.tv/fibretigre) on the 31/12/2020 under the name ATRPG. It has been developed here since by volunteers. It was officially renamed "MINOTAURE" on the 28/03/2021.

Discover streams that use Minotaure on the official website [minotau.re](https://minotau.re).

Join the FibreTigre [discord](https://discord.gg/RAhph7z) community (in French + 1 English channel).

### Install without Docker

1. Set up a PHP server with SQL
1. Import the `database.sql` file into you database (3 tables)
1. Rename `src/connexion_example.php` to `src/connexion.php`
1. Edit this file to grant access to the database

Example: if you use a local database named `base1` with login `foo` and password `bar`, change the 3rd line to:
```php
$host = 'localhost'; // Host name.
$dbname = 'base1'; // Name.
$port = 3306; // Port.
$login = 'toto'; // Login.
$mdp = 'titi'; // Password.
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

### My language doesn't work

If you use a language other than French and you encounter the message "Language not available" when clicking on the language on the home page, your server may be configured with values other than our default values. You can link each button to the language installed on your server by indicating its value in front of each language key.
Example: if your server has the en_NZ.UTF8 language installed, you can indicate in front of the "en" key to say that English uses en_NZ.UTF8. To find out the languages installed on your server, type "locale -a" in your console. 
```php
$languages = [
  'en' => 'en_NZ.UTF8',
];
```

### How to play

- The first account you create will be the admin (it can have any name)
- After logging in with this account, the user may go to `ecran.php` and access all commands
- *Et voilà !*
- Demo video (in French): https://www.youtube.com/watch?v=XGU3_dczcNE
