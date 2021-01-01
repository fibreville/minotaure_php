<?php
$text = $_GET['text'];

if ($text == "erreur") {
    $erreur = "<font color=red>Ce héros avec ce mot de passe existe déjà ! Merci d'utiliser « Reprendre une partie ».<br>Si malheureusement vous êtes mort, nous vous invitons à créer un nouveau personnage.<br><br></font>";
}
?>
<html>
<head>
  <title>AT RPG</title>
  <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Cinzel+Decorative">
  <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Libre+Baskerville">
  <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
</head>
<body>
<div align=center>
  <font style="font-family: 'Cinzel Decorative', Black;font-size: 90px;"><b>AT RPG</b></font>
  <br><br><br>

  <form method="post" action="newcomplete.php">
    <tr>
      <font
              style="font-family: 'Libre Baskerville', Black;font-size: 15px;"><?php print $erreur; ?>
        Création de personnage.<br>&nbsp;<br>&nbsp;
        <table>
          <tr>
            <td>Nom :</td>
            <td><input type="text" name="nom"></td>
          </tr>
          <tr>
            <td>Mot de passe :</td>
            <td><input type="password" name="pass"></td>
          </tr>
          <tr>
            <td>Vous êtes un.e</td>
            <td><select name="genre">
                <option value="1">femme</option>
                <option value="2">homme</option>
                <option value="3">non binaire</option>
              </select>
            </td>
          </tr>
          <tr>
            <td valign="top">Votre type de personnage :</td>
            <td>
              <input type="radio" name="stat" value="51">super fort et super
              bête<br>
              <input type="radio" name="stat" value="42">plutôt fort et plutôt
              bête<br>
              <input type="radio" name="stat" value="24">plutôt malin et plutôt
              faiblard<br>
              <input type="radio" name="stat" value="15">super malin et et super
              faible<br>
            </td>
          </tr>
        </table>
        <br><br>
        <input type="submit" value="Partir à l'aventure"
               style="border-top:2px solid #666666;border-left:2px solid #666666;border-right:2px solid #000000;border-bottom:2px solid #000000;padding:10px 20px;font-size:	15px;background-color:#FFFFFF;font-weight:bold;color:#000000;font-family: 'Libre Baskerville', Black;">
</div>
</form>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> upstream/main
