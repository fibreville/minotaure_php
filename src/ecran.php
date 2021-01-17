<?php
session_start();
include "admin_tools.php";

admin_only();
avoid_form_resending_on_refresh();

// mettre à jour le fichier servant à prévenir les PJ que leur fiche doit être rechargée.
if (!isset($_GET['action'])) {
  $page_id = 'page-mj';
  include "header.php";
  include "ecran_forms.php";
  include "footer.php";
  die;
}
include "connexion.php";
include "variables.php";
file_put_contents($tmp_path . '/game_timestamp.txt', time());
// TRAITEMENT DE LA SUPPRESSION DE L'AVENTURE
if ($_GET['action'] == "delete") {
  delete_adventure($db, $tmp_path);
}

// PARAMETRES AVENTURE.
elseif ($_GET['action'] == 'settings') {
  save_new_settings($_POST, $tmp_path);
  $settings = $_SESSION['settings'];
}

// TRAITEMENT DE L'AJOUT DE TAGS.
elseif ($_GET['action'] == "tags") {
  add_new_tags($db, $_POST);
}


elseif ($_GET['action'] == "create_user") {
  $db->query(
      "INSERT INTO `hrpg`"
      ." (`nom`, `mdp`, `carac2`, `carac1`, `hp`, `leader`, `traitre`, `vote`, `tag1`, `tag2`, `tag3`, `log`, `lastlog`)"
      ." VALUES ('".substr(md5(microtime()),rand(0,26),5)."', '', '1', '1', '1', '0', '0', '0', '', '', '', NULL, NULL)"
    );
}

// TRAITEMENT DES EPREUVES.
elseif ($_GET['action'] == "epreuve") {
  $sanction = update_events($db, $_POST);
}

// TRAITEMENT DU LOOT
elseif ($_GET['action'] == "loot") {
  update_loot($db, $_POST);
}

elseif ($_GET['action'] == "clean") {
  clean_adventure($db);
}

// TRAITEMENT DU SONDAGE.
elseif ($_GET['action'] == "poll") {
  survey_update($db, $_POST);
}

// TRAITEMENT DES NOMINATIONS.
elseif ($_GET['action'] == 'election') {
  $designe = make_election($db,$_POST);
}

else {
  die('Unknown action');
}

header('Location: ecran.php');
?>