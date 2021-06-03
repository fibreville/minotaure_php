<?php
session_start();
$page_id = 'page-new';
$_SESSION['current_timestamp'] = 0;
include 'header.php';

if ($_SESSION['settings']['lock_new']) {
  print '<h2>' . _('Création de personnage') . '</h2>';
  print '<div>' . _("L'aventure n'accueille pas de nouveaux personnages pour l'instant !") . '</div>';
}
else {
  ?>
  <form method="post" action="newcomplete.php">
    <h2><?php print _('Création de personnage'); ?></h2>
    <fieldset>
      <legend><?php print _('La base'); ?></legend>
      <span><label for="nom"><?php print _('Nom'); ?></label><input type="text" name="nom" id="nom" maxlength="15" required></span>
      <span><label for="pass"><?php print _('Mot de passe'); ?></label><input type="password" name="pass" id="pass" required></span>
    </fieldset>
    <?php if ($settings['same_stats_all'] == FALSE) : ?>
    <fieldset>
      <legend>Votre type de personnage</legend>
      <?php if ($_SESSION['settings']['carac3_name'] != ""): ?>
      <span>
        <input type="radio" name="stat" value="9_9_9"><?php print _("équilibré"); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="12_6_6"><?php print sprintf(_("orienté %s"), $settings['carac1_group']); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="6_12_6"><?php print sprintf(_("orienté %s"), $settings['carac2_group']); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="6_6_12"><?php print sprintf(_("orienté %s"), $settings['carac3_group']); ?>
      </span>
      <?php else: ?>
      <span>
        <input type="radio" name="stat" value="15_5_10"><?php print sprintf(_("très %s mais pas %s"), $settings['carac1_group'], $settings['carac2_group']); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="12_8_10"><?php print sprintf(_("plutôt %s mais peu %s"), $settings['carac1_group'], $settings['carac2_group']); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="10_10_10"><?php print _("équilibré"); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="8_12_10"><?php print sprintf(_("peu %s mais plutôt %s"), $settings['carac1_group'], $settings['carac2_group']); ?>
      </span>
      <span>
        <input type="radio" name="stat" value="5_15_10"><?php print sprintf(_("pas %s mais très %s"), $settings['carac1_group'], $settings['carac2_group']); ?>
      </span>
      <?php endif ?>
    </fieldset>
    <?php endif ?>
    <input class="submit-button" type="submit" value="<?php print _("Partir à l'aventure"); ?>">
  </form>
  <?php
}
print "<a href='index.php'>" . _('Retour') . "</a>";
include "footer.php";
?>
