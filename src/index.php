<?php
session_start();
$page_id = 'page-index';
include 'header.php';
?>
<div class="index-wrapper">
  <div class="title-wrapper">
    <img class="logo" src="./img/minotaure_logo.svg">
    <h1>minotaure</h1>
  </div>
  <div>
    <a href="new.php"><?php echo _("Création de personnage"); ?></a>
    <a href="continue.php"><?php echo _("Continuer la partie"); ?></a>
  </div>
</div>
<div class="secondary-links">
  <div class="languages">
    <a href="index.php?language=en_GB">EN</a>
    <a href="index.php?language=fr_FR">FR</a>
  </div>
  <span><?php echo _('Une idée originale de <a href="https://twitter.com/FibreTigre" target="_blank"> FibreTigre</a>'); ?> </span>
  <span><?php echo _("Version communautaire"); ?> <a class="version" href="https://github.com/fibreville/atrpg" target="_blank"><?php print file_get_contents('./version.txt'); ?></a></span>
</div>
<?php include 'footer.php' ?>
