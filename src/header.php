<?php
session_start();
require_once "connexion.php";
require "variables.php";
?>
<!DOCTYPE html>
<html<?php if (isset($page_id)){ echo ' id="'.$page_id.'"'; } ?> lang="fr">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title><?php print $settings['adventure_name']; ?></title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/jquery-3.5.1.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/jQuery.tagify.min.js"></script>
  </head>
  <body>
    <nav id="account_actions">
      <div class="theme-switch">
        <span class="enableDarkMode" title="<?php print _("Mode sombre"); ?>" onclick="setTheme('dark')"></span>
        <span class="enableLightMode" title="<?php print _("Mode clair"); ?>" onclick="setTheme('light')"></span>
      </div>
      <?php if (isset($_SESSION['nom'])){ ?><a href="logout.php"><?php print _("Déconnexion"); ?></a><?php } ?>
    </nav>
    <div class="page-wrapper">
