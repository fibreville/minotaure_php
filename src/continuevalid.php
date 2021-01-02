<html>
  <?php
  include "header.php";

  $cleanPost = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
  $nom = $cleanPost['nom'];
  $pass = $cleanPost['pass'];

  $nom = utf8_decode(ucfirst(strtolower($nom)));
  $pass = $pass . substr($nom, 0, 3) . substr($nom, -1);
  $pass = utf8_decode(md5($pass));

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
    if ($hp > 0) {
      $text = 'Votre grande aventure continue';
      $link = 'Cliquez <a class="link_primary" href=main.php>ici</a>';
    }
    else {
      $text = 'Votre personnage est mort ☠️. On en recrée un nouveau ?';
      $link = "Retourner au <a href=index.php>menu principal</a>";
    }
  }
  else {
    $text = "Nous n'avons pas réussi à vous identifier :-(";
    $link = "Voulez-vous <a href=continue.php>recommencer</a> <br>ou retourner au <a href=index.php>menu principal</a>";
  }
  ?>
  <div>
    <div>Bonjour <?php echo $nom; ?>.</div>
    <div><?php echo $text; ?></div>
    <div><?php echo $link; ?></div>
  </div>
  <?php include "footer.php"; ?>
</html>