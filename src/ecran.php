<?php
session_start();
include "admin_tools.php";

admin_only();

$page_id = 'page-mj';
include "header.php";
file_put_contents($tmp_path . '/game_timestamp.txt', time());

if (isset($_GET['action'])) {
  $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);;

  // TRAITEMENT DE LA SUPPRESSION DE L'AVENTURE
  if ($_GET['action'] == "delete") {
    delete_adventure($db, $tmp_path);
  }
  // PARAMETRES AVENTURE.
  elseif ($_GET['action'] == 'settings') {
    save_new_settings($_POST, $tmp_path);
  }
  // TRAITEMENT DE L'AJOUT DE TAGS.
  elseif ($_GET['action'] == "tags") {
    add_new_tags($db, $_POST);
  }
  elseif ($_GET['action'] == "create_user") {
    $db->query(
      "INSERT INTO `hrpg`"
      . " (`nom`, `mdp`, `carac2`, `carac1`, `hp`, `leader`, `traitre`, `vote`, `tag1`, `tag2`, `tag3`, `log`, `lastlog`)"
      . " VALUES ('" . substr(md5(microtime()), rand(0, 26), 5) . "', '', '1', '1', '1', '0', '0', '0', '', '', '', NULL, NULL)"
    );
  }
  // TRAITEMENT DES EPREUVES.
  elseif ($_GET['action'] == "epreuve") {
    update_events($db, $_POST);
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
    make_election($db, $_POST);
  }
  else {
    die('Unknown action');
  }
}

include "ecran_forms.php";
include "footer.php";