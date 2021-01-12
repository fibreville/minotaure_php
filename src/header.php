<?php
include "connexion.php";
// VARIABLES GENERALES.
$settings_set = ['carac1_name', 'carac2_name', 'carac1_group', 'carac2_group'];
$default_settings_set = [
  'carac1_name' => 'esprit',
  'carac2_name' => 'corps',
  'carac1_group' => 'malin',
  'carac2_group' => 'fort',
];
$query = $db->prepare("SELECT name, value FROM settings");
$query->execute();
$settings = $query->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($settings_set as $setting_set) {
  if (!isset($settings[$setting_set])) {
    $settings[$setting_set] = $default_settings_set[$setting_set];
  }
}
?>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta charset="UTF-8">
  <title>Maspero Blue</title>
  <link rel="stylesheet" type="text/css" href="./css/style.css">
</head>
<body>
  <?php if (isset($_SESSION['nom']))
  { ?>
  <nav id="account_actions">
    <a href="logout.php">Déconnexion de <?php echo $_SESSION['nom']; ?></a>
  </nav><?php
  } ?>
  <div class="page-wrapper">
    <h1>AT RPG</h1>
