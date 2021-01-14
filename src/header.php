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
</head>
<body class="light">
  <nav id="account_actions">
    <span href="dark.php" class="enableDarkMode" title="Dark mode" onclick="setBright('light','dark')"></span>
    <span href="light.php" class="enableLightMode" title="Light mode" onclick="setBright('dark','light')"></span>
    <?php if (isset($_SESSION['nom'])){ ?><a href="logout.php">Déconnexion</a><?php } ?>
  </nav>
  <div class="page-wrapper">
