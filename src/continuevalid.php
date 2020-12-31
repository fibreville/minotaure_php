<?php
session_start();
include "../connexion.php";

$nom = $_POST['nom'];
$pass = $_POST['pass'];

$nom = ucfirst(strtolower($nom));
$pass = $pass . substr($nom, 0, 3) . substr($nom, -1);
$pass = md5($pass);
$nom = utf8_decode($nom);
$pass = utf8_decode($pass);

$stmt = $db->prepare("SELECT id,hp FROM hrpg WHERE nom=:nom AND mdp=:pass");
$stmt->execute([
  ':nom' => $nom,
  ':pass' => $pass,
]);

$row = $stmt->fetch();
$id = $row[0];
$hp = $row[1];

if ($id != "" && $hp > 0) {
  $_SESSION['id'] = $id;
  $_SESSION['nom'] = $nom;
  $text = "Bonjour " . $nom . ".<br><br>Votre grande aventure continue.<br><br>Cliquez <a href=main.php style=color:#ff0000>ici</a>.";
}
elseif ($sante < 1 && $id != "") {
  $text = "Bonjour " . $nom . ".<br><br>Votre est mort :-( On en recrée un nouveau ? <br><br>Retourner au <a href=index.php>menu principal</a>";
}
else {
  $text = "Bonjour " . $nom . ".<br><br>Nous n'avons pas réussi à vous identifier :-( <br><br>Voulez-vous <a href=continue.php>recommencer</a> <br>ou retourner au <a href=index.php>menu principal</a> ?";
}

?>


<html>
<head>
  <title>AT RPG</title>
  <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Cinzel+Decorative">
  <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Libre+Baskerville">

</head>
<body>
<div align="center">
  <font style="font-family: 'Cinzel Decorative', Black;font-size: 90px;"><b>AT RPG</b></font>
  <br><br>
  <font style="font-family: 'Libre Baskerville', Black;font-size: 30px;">
    <br><br><br>
    <td align="center">
      <?php print "$text"; ?>
    </td>
    </tr>
    </table>
</body>
</html>