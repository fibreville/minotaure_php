<?php
session_start();
$_SESSION['current_timestamp'] = 0;
include "header.php";

$cleanPost = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$nom = strtolower($cleanPost['nom']);
$pass = $cleanPost['pass'];
$pass = $pass . substr($nom, 0, 3) . substr($nom, -1);
$pass = md5($pass);

$stmt = $db->prepare("SELECT id,hp FROM hrpg WHERE nom=:nom AND mdp=:pass");
$stmt->execute([
  ':nom' => $nom,
  ':pass' => $pass,
]);
$row = $stmt->fetch();
$id = $row[0];
$hp = $row[1];

if ($id != "") {
  $_SESSION['id'] = $id;
  $_SESSION['nom'] = $nom;
  if ($id == 1) {
    $text = 'Votre grande aventure continue';
    $link = 'Accédez à l\'écran du MJ en cliquant <a href=ecran.php>ici</a>';
  }
  else {
    if ($hp > 0) {
      $stmt = $db->prepare("UPDATE hrpg SET active=1 WHERE id = :id");
      $stmt->execute([':id' => $id]);
      $text = 'Votre grande aventure continue';
      $link = 'Cliquez <a href=main.php>ici</a>';
    }
    else {
      $text = 'Votre personnage est mort ☠️. On en recrée un nouveau ?';
      $link = "Retourner au <a href=index.php>menu principal</a>";
    }
  }
}
else {
  $link = "Voulez-vous <a href=continue.php>recommencer</a> <br>ou retourner au <a href=index.php>menu principal</a>";
}
?>
<div>
  <?php if ($id == ""): ?>
    <div class="hello">Bonjour, nous n'avons pas réussi à vous identifier 😢 !</div>
  <?php else: ?>
    <div class="hello">Bonjour <span class="pj-name"><?php echo $nom; ?>.</span></div>
    <div><?php echo $text; ?></div>
  <?php endif; ?>
  <div><?php echo $link; ?></div>
</div>
<?php include "footer.php"; ?>