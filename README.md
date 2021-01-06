# atrpg

## Français

### Danger !

⚠️ Le logiciel contient plusieurs failles de sécurité et les mots de passe ne sont pas secret du tout ! Utilisation à vos risques et périls.

### A quoi ça sert ?

Ce programme permet de récréer sur votre serveur le JDR « AT RPG » (Asynchronous Tactical RPG), qui permet de jouer à plein sur un serveur.

L'idée et le code d'origine ont été fournies par [Fibretigre](https://www.twitch.tv/fibretigre) le 31/12/20. Rejoignez
le discord de la communauté sur : [discord server](https://discord.gg/RAhph7z).

### Installation sans Docker

- il vous faut un serveur avec PHP et une base SQL.
- importez la base de données du fichier La structure des 3 tables est dans « database »

Le fichier `connexion_example.php` est à modifier pour indiquer votre connexion à la base de données.
Exemple, si votre base de donnée est locale, que votre base s'appelle `base1`, que votre identifiant
est `toto` et votre mot de passe `titi`, indiquez :

```
$db = new PDO('mysql:host=localhost;dbname=base1;', 'toto', 'titi');
```

Les fichiers dans /src n'ont pas besoin d'être modifiés.

### Installation avec Docker

Il faut avoir [docker](https://docs.docker.com/get-docker/), [docker-compose](https://docs.docker.com/compose/install/)
et [make](https://fr.wikipedia.org/wiki/Make).

1. Cloner le repo : `git clone https://github.com/fibreville/atrpg.git`
1. Lancer les conteneurs : `make up`
1. Lancer le navigateur et aller sur [http://127.0.0.1:8080]
1. Créer le premier compte qui sera le MJ
1. Faire que son PC soit accessible depuis Internet: Aller sur la box, faire que le port 8080 de la box soit envoyé sur le port 8080 du PC. L'URL pour internet sera alors http://address-ip:8080 .

* Pour voir les logs : `make logs`
* Pour arrêter les conteneurs : `make down`
* Pour détruire conteneurs et données : `make reset`

### Pour jouer

- Le 1er compte créé sur le jeu est le compte admin (il peut avoir n'importe quel nom).
- Une fois ce compte créé et le joueur/MJ logué, il peut aller sur `ecran.php` et avoir accès à l'ensemble des commandes.
- Le mode de fonctionnement ensuite est détaillé lors d'une partie ici : https://www.youtube.com/watch?v=bUFo1yhHT7E

## In english

TODO

