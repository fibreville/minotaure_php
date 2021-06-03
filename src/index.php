<?php
session_start();
$page_id = 'page-index';
include 'header.php';
if (isset($_GET['language'])) {
  $_SESSION['language'] = $_GET['language'];
  setlocale(LC_ALL, $_SESSION['language']);
  putenv("LANGUAGE=" . $_SESSION['language'] );
}
?>
<div class="index-wrapper">
  <h1>Minotaure</h1>
  <a href="new.php"><?php echo _("Création de personnage"); ?></a>
  <a href="continue.php"><?php echo _("Continuer la partie"); ?></a>
</div>
<div class="secondary-links">
  <div class="languages">
    <a href="index.php?language=en">EN</a>
    <a href="index.php?language=fr">FR</a>
  </div>
  <span><?php echo _('Une idée originale de <a href="https://twitter.com/FibreTigre" target="_blank"> FibreTigre</a>'); ?> </span>
  <span><?php echo _("Version communautaire"); ?> <a class="version" href="https://github.com/fibreville/atrpg" target="_blank"><?php print file_get_contents('./version.txt'); ?></a></span>
</div>
<?php include 'footer.php' ?>
