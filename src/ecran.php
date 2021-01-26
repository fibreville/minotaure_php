<?php
session_start();
require_once "admin_tools.php";
require_once "connexion.php";

admin_only();
$page_id = 'page-mj';

file_put_contents($tmp_path . '/game_timestamp.txt', time());
unset($_SESSION['sanction']);
unset($_SESSION['designe']);

if (isset($_GET['action'])) {
  require "variables.php";
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
    $chars = "0123456789abcdef";
    $rand_string = "";
    for ($i = 0; $i < 32; $i++) {
        $rand_string .= $chars[rand(0, strlen($chars) - 1)];
    }
    $hash = password_hash($rand_string, PASSWORD_DEFAULT);

    $db->query(
      "INSERT INTO `hrpg`"
      . " (`nom`, `mdp`, `carac2`, `carac1`, `hp`, `leader`, `traitre`, `vote`, `tag1`, `tag2`, `tag3`, `log`, `lastlog`)"
      . " VALUES ('" . $hash . "', '', '1', '1', '1', '0', '0', '0', '', '', '', NULL, NULL)"
    );
  }
  // TRAITEMENT DES EPREUVES.
  elseif ($_GET['action'] == "epreuve") {
    print update_events($db, $_POST);
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

require "header.php";
require "ecran_forms.php";
require "footer.php";