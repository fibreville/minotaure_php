<?php session_start(); ?>
<html id="page-index">
  <?php include 'header.php' ?>
  <div class="index-wrapper">
    <h1>AT RPG</h1>
    <a href="new.php">Création de personnage</a>
    <a href="continue.php">Reprendre une partie</a>
  </div>
  <div class="secondary-links">
    <span>Une idée originale de <a href="https://twitter.com/FibreTigre" target="_blank"> FibreTigre</a></span>
    <span>Version communautaire <a class="version" href="https://github.com/fibreville/atrpg" target="_blank"><?php print file_get_contents('./version.txt'); ?></a></span>
  </div>
  <?php include 'footer.php' ?>
</html>
