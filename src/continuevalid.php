<?php
session_start();
$_SESSION['current_timestamp'] = 0;
include "header.php";

$cleanPost = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
isset($cleanPost['nom']) ? $nom = strtolower($cleanPost['nom']) : $nom = "";

$stmt = $db->prepare("SELECT id,hp,wp,mdp FROM hrpg WHERE nom=:nom");
$stmt->execute([
  ':nom' => $nom,
]);
$row = $stmt->fetch();
$id = ""; $hp = ""; $mdp_hash = "";
if ($stmt->rowCount() > 0) {
  $id = $row[0];
  $hp = $row[1];
  $wp = $row[2];
  $mdp_hash = $row[3];
}

isset($cleanPost['pass']) ? $pass = $cleanPost['pass'] : $pass = "";
if ($mdp_hash == '') {
  $pass = password_hash($pass, PASSWORD_DEFAULT);
  $stmt = $db->prepare("UPDATE hrpg SET mdp=:pass WHERE id=:id");
  $stmt->execute([
    ':id' => $id,
    ':pass' => $pass,
  ]);
}
elseif (!password_verify($pass, $mdp_hash)) {
    $id = "";
    $hp = "";
}

if ($id != "") {
  $_SESSION['id'] = $id;
  $_SESSION['nom'] = $nom;
  if ($id == 1) {
    $stmt = $db->query("UPDATE hrpg SET active=0");
    $text = 'Votre grande aventure continue';
    $link = 'Accédez à l\'écran du MJ en cliquant <a href=ecran.php>ici</a>';
  }
  else {
    if ($hp <= 0) {
      $text = 'Votre personnage est mort ☠️. On en recrée un nouveau ?';
      $link = "Retourner au <a href=index.php>menu principal</a>";
      
    }
    elseif ($settings['willpower_on'] && $wp <= 0) {
      $text = 'Votre personnage a sombré 🌑️. On en recrée un nouveau ?';
      $link = "Retourner au <a href=index.php>menu principal</a>";
    }
    else {
      $stmt = $db->prepare("UPDATE hrpg SET active=1 WHERE id = :id");
      $stmt->execute([':id' => $id]);
      $text = 'Votre grande aventure continue';
      $link = 'Cliquez <a href=main.php>ici</a>';
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
