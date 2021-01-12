<?php
session_start();
include "connexion.php";

$nom = $_POST['nom'];
$genre = $_POST['genre'];
$pass = $_POST['pass'];
$stat = $_POST['stat'];
$probleme = null;

if (empty($nom) || empty($pass)) {
  $probleme = 'Veuillez remplir le champ : ' . (empty($nom) ? 'nom' : 'mot de passe');
}

$nom = substr(ucfirst(strtolower(strip_tags($nom))), 0, 12);
$pass = $pass . substr($nom, 0, 3) . substr($nom, -1);
$pass = md5($pass);
$stmt = $db->prepare("SELECT id FROM hrpg WHERE nom=:nom");
$stmt->execute([
  ':nom' => $nom,
]);

$row = $stmt->fetch();
$id = $row[0];
if (!empty($id)) {
  $probleme = 'Ce nom est déjà utilisé. Veuillez en choisir un autre.';
}

if (!empty($probleme)) {
  die('<html><head><meta http-equiv="refresh" content="0;URL=new.php?text='.urlencode($probleme).'"></head><body></body></html>');
}
else {
  if (empty($stat)) {
    $carac2 = array_rand([1,2,4,5], 1);
    $carac1 = 6 - $carac2;
  }
  else {
    $carac2 = $stat[0];
    $carac1 = $stat[1];
  }
  $hp = 5 + rand(0, 5);
  $stmt = $db->prepare("SELECT tag1 FROM hrpg WHERE hp > 0 AND id > 1 ORDER BY RAND()");
  $stmt->execute();
  $row = $stmt->fetch();
  $tag1 = $row[0];

  $stmt = $db->prepare("SELECT tag2 FROM hrpg WHERE hp > 0 AND id > 1 ORDER BY RAND()");
  $stmt->execute();
  $row = $stmt->fetch();
  $tag2 = $row[0];

  $stmt = $db->prepare("SELECT tag3 FROM hrpg WHERE hp > 0 AND id > 1 ORDER BY RAND()");
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
    $stmt = $db->prepare("INSERT INTO hrpg (nom,mdp,hf,carac2,carac1,hp,tag1,tag2,tag3) VALUES(:nom,:pass,:genre,:carac2,:carac1,:hp,:tag1,:tag2,:tag3)");
    $stmt->execute([
      ':nom' => $nom,
      ':pass' => $pass,
      ':genre' => $genre,
      ':carac2' => $carac2,
      ':carac1' => $carac1,
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
  $_SESSION['nom'] = $nom;
?>

<html>
  <?php include 'header.php'; ?>
  <div>
    <div><?php print $nom; ?> entre en scène.</div>
    <div>Bienvenue dans notre grande aventure.</div>
    <div><a href="main.php">C'est parti.</a></div>
  </div>
  <?php include "footer.php"; ?>
</html>
<?php } ?>