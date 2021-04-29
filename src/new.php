<?php
session_start();
$page_id = 'page-new';
$_SESSION['current_timestamp'] = 0;
include 'header.php';
isset($_GET['text']) ? $text = $_GET['text'] : $text = "";

if ($_SESSION['settings']['lock_new']) {
  print "<h2>Création de personnage</h2>";
  print "<br />";
  print "L'aventure n'accueille pas de nouveaux personnages pour l'instant !<br />";
  print "<br />";
}
else {
  ?>
  <form method="post" action="newcomplete.php">
    <h2>Création de personnage</h2>
    <fieldset>
      <legend>La base</legend>
      <span><label for="nom">Nom</label><input type="text" name="nom" id="nom" maxlength="15" required></span>
      <span><label for="pass">Mot de passe</label><input type="password" name="pass" id="pass" required></span>
    </fieldset>
    <?php if ($settings['same_stats_all'] == FALSE) : ?>
    <fieldset>
      <legend>Votre type de personnage</legend>
      <?php if ($_SESSION['settings']['carac3_name'] != "") { ?>
      <span>
        <input type="radio" name="stat" value="9_9_9">équilibré
      </span>
      <span>
        <input type="radio" name="stat" value="12_6_6">orienté <?php print $settings['carac1_group']; ?>
      </span>
      <span>
        <input type="radio" name="stat" value="6_12_6">orienté <?php print $settings['carac2_group']; ?>
      </span>
      <span>
        <input type="radio" name="stat" value="6_6_12">orienté <?php print $settings['carac3_group']; ?>
      </span>
      <?php
      }
      else {
      ?>
      <span>
        <input type="radio" name="stat" value="15_5_10"><?php print 'très ' . $settings['carac1_group'] . ' mais pas ' . $settings['carac2_group']; ?>
      </span>
      <span>
        <input type="radio" name="stat" value="12_8_10"><?php print 'plutôt ' . $settings['carac1_group'] . ' mais peu ' . $settings['carac2_group']; ?>
      </span>
      <span>
        <input type="radio" name="stat" value="10_10_10">équilibré
      </span>
      <span>
        <input type="radio" name="stat" value="8_12_10"><?php print 'peu ' . $settings['carac1_group'] . ' mais plutôt ' . $settings['carac2_group']; ?>
      </span>
      <span>
        <input type="radio" name="stat" value="5_15_10"><?php print 'pas ' . $settings['carac1_group'] . ' mais très ' . $settings['carac2_group']; ?>
      </span>
      <?php
      }
      ?>

    </fieldset>
    <?php endif ?>
    <input class="submit-button" type="submit" value="Partir à l'aventure">
  </form>
  <?php
}
print "<br />";
print "<a href='index.php'>Retour</a>";

include "footer.php";
?>
