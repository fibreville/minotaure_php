<?php
include "connexion.php";
include "variables.php";
?>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta charset="UTF-8">
  <title><?php print $settings['adventure_name']; ?></title>
  <link rel="stylesheet" type="text/css" href="./css/style.css">
  <script src="js/main.js"></script>
  <script src="js/jquery-3.5.1.min.js"></script>
</head>
<body class="light">
  <nav id="account_actions">
    <span class="enableDarkMode" title="Dark mode" onclick="setTheme('dark')"></span>
    <span class="enableLightMode" title="Light mode" onclick="setTheme('light')"></span>
    <?php if (isset($_SESSION['nom'])){ ?><a href="logout.php">Déconnexion</a><?php } ?>
  </nav>
  <div class="page-wrapper">
