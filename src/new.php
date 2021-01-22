<?php
session_start();
$page_id = 'page-new';
$_SESSION['current_timestamp'] = 0;
include 'header.php';
$text = $_GET['text'];

if ($text == "erreur") {
  $erreur = "<div>Ce héros existe déjà ! Merci d'utiliser « Reprendre une partie ».<br>
  Si malheureusement vous êtes mort, nous vous invitons à créer un nouveau personnage.</div>";
}
?>
<form method="post" action="newcomplete.php">
  <?php print $erreur; ?>
  <h2>Création de personnage</h2>
  <fieldset>
    <legend>La base</legend>
    <span><label for="nom">Nom</label><input type="text" name="nom" id="nom" maxlength="15" required></span>
    <span><label for="pass">Mot de passe</label><input type="password" name="pass" id="pass" required></span>
  </fieldset>
  <fieldset>
    <legend>Votre type de personnage</legend>
    <span><input type="radio" name="stat" value="51"><?php print 'très ' . $settings['carac1_group'] . ' mais pas ' . $settings['carac2_group']; ?></span>
    <span><input type="radio" name="stat" value="42"><?php print 'plutôt ' . $settings['carac1_group'] . ' mais peu ' . $settings['carac2_group']; ?></span>
    <span><input type="radio" name="stat" value="24"><?php print 'peu ' . $settings['carac1_group'] . ' mais plutôt ' . $settings['carac2_group']; ?></span>
    <span><input type="radio" name="stat" value="15"><?php print 'pas ' . $settings['carac1_group'] . ' mais très ' . $settings['carac2_group']; ?></span>
  </fieldset>
  <input class="submit-button" type="submit" value="Partir à l'aventure">
</form>
<?php
include "footer.php";
?>