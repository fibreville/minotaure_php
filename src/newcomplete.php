<?php
session_start();
include "../connexion.php";

$nom = $_POST['nom'];
$genre = $_POST['genre'];
$pass = $_POST['pass'];
$stat = $_POST['stat'];

if ($nom == "" || $pass == "") {
    $probleme = 1;
}

$nom = substr(ucfirst(strtolower(strip_tags($nom))), 0, 12);

if (($stat[0] + $stat[1]) != 6) {
    $stat = 22;
}

$pass = $pass . substr($nom, 0, 3) . substr($nom, -1);

$pass = md5($pass);

$nom = utf8_decode($nom);
$pass = utf8_decode($pass);

$stmt = $db->prepare("SELECT id FROM hrpg WHERE nom=:nom AND mdp=:pass");
$stmt->execute([
    ':nom' => $nom,
    ':pass' => $pass,
]);

$row = $stmt->fetch();
$id = $row[0];

if ($id != "") {
    $probleme = 1;
}
if ($probleme == 1) {
    ?>
  <html>
  <head>
    <meta http-equiv="refresh" content="0;URL=new.php?text=erreur">
  </head>
  <body>
  </body>
  </html>
    <?php
} else {
    $hp = 5 + rand(0, 5);
    $str = $stat[0];
    $mind = $stat[1];

    $stmt = $db->prepare("SELECT tag1 FROM hrpg WHERE hp>0 ORDER BY RAND()");
    $stmt->execute();
    $row = $stmt->fetch();
    $tag1 = $row[0];

    $stmt = $db->prepare("SELECT tag2 FROM hrpg WHERE hp>0 ORDER BY RAND()");
    $stmt->execute();
    $row = $stmt->fetch();
    $tag2 = $row[0];

    $stmt = $db->prepare("SELECT tag3 FROM hrpg WHERE hp>0 ORDER BY RAND()");
    $stmt->execute();
    $row = $stmt->fetch();
    $tag3 = $row[0];

    if ($tag1 == "") {
        $tag1 = " ";
    }
    if ($tag2 == "") {
        $tag2 = " ";
    }
    if ($tag3 == "") {
        $tag3 = " ";
    }

    try {
        $stmt = $db->prepare("INSERT INTO hrpg (nom,mdp,hf,str,mind,hp,tag1,tag2,tag3) VALUES(:nom,:pass,:genre,:str,:mind,:hp,:tag1,:tag2,:tag3)");
        $stmt->execute([
            ':nom' => $nom,
            ':pass' => $pass,
            ':genre' => $genre,
            ':str' => $str,
            ':mind' => $mind,
            ':hp' => $hp,
            ':tag1' => $tag1,
            ':tag2' => $tag2,
            ':tag3' => $tag3,
        ]);

        $id = $db->lastInsertId();
    }
    catch (Exception $e) {
        die($e->getMessage());
    }

    $_SESSION['id'] = $id;
    $nom = utf8_encode($nom);
    $pass = utf8_encode($pass);
    ?>

  <html>
  <head>
    <title>AT RPG</title>
    <link rel="stylesheet" type="text/css"
          href="https://fonts.googleapis.com/css?family=Libre+Baskerville">
  </head>
  <body>
  <div align="center">
    <table border="0" align="center" width=700>
      <tr>
        <td align="left">
          <font
                  style="font-family: 'Libre Baskerville', Black;font-size: 20px;"><?php print $nom; ?>
            entre en scène.</font>
          <br>
          <br>
          <br>
          <font
                  style="font-family: 'Libre Baskerville', Black;font-size: 15px;">
            Bienvenue dans notre grande aventure.
            <br><br>
            <a href="main.php">C'est parti.</a>
        </td>
      </tr>
    </table>
    </font>
  </body>
  </html>
    <?php
}
?>
