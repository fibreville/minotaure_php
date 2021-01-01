# atrpg

### **En français** :

#### A quoi ça sert ?

Ce programme permet de récréer sur votre serveur le JDR « AT RPG » (Asynchronous Tactical RPG), qui permet de jouer à
plein sur un serveur.  
L'idée et le code d'origine ont été fournies par [Fibretigre](https://www.twitch.tv/fibretigre) le 31/12/20. Rejoignez
le discord de la communauté sur : [discord server](https://discord.gg/RAhph7z)

#### Installation sans Docker :

- il vous faut un serveur avec PHP et une base SQL.
- importez la base de données du fichier La structure des 3 tables est dans « database »

Le fichier connexion_example.php est à modifier pour indiquer votre connexion à la base de données.
Exemple, si votre base de donnée est locale, que votre base s'appelle base1, que votre identifiant 
est toto et votre mot de passe titi, indiquez :

$db = new PDO('mysql:host=localhost;dbname=base1;', 'toto', 'titi');

Les fichiers dans /src n'ont pas besoin d'être modifiés.

#### Installation avec Docker :

TODO

#### Pour jouer :

- le 1er compte créé sur le jeu est le compte admin (il peut avoir n'importe quel nom).
- une fois ce compte créé et le joueur/MJ logué, il peut aller sur main.php et avoir accès à l'ensemble des commandes.
- le mode de fonctionnement ensuite est détaillé lors d'une partie ici : https://www.youtube.com/watch?v=bUFo1yhHT7E

### **In english** :

TODO

