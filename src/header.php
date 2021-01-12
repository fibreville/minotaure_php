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
<body>
  <?php if (isset($_SESSION['nom'])) { ?>
  <nav id="account_actions">
    <a href="logout.php">Déconnexion de <?php echo $_SESSION['nom']; ?></a>
  </nav><?php
  } ?>
  <div class="page-wrapper">
