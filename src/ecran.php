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

  // SUPPRESSION DE L'AVENTURE
  if ($_GET['action'] == "delete") {
    delete_adventure($db, $tmp_path);
  }
  // PARAMETRES DE L'AVENTURE
  elseif ($_GET['action'] == 'settings') {
    save_new_settings($_POST, $tmp_path);
  }
  // AJOUT DE TAGS.
  elseif ($_GET['action'] == "tags") {
    add_new_tags($db, $_POST);
  }
  // SUPPRESSION DE TAGS.
  elseif ($_GET['action'] == "delete_tags" && isset($_GET['category'])) {
    delete_tag_category($db, $_GET['category']);
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
      . " (`nom`, `mdp`, `carac3`, `carac2`, `carac1`, `hp`, `wp`, `leader`, `traitre`, `vote`, `log`, `lastlog`, `status`)"
      . " VALUES ('" . $hash . "', '', '1', '1', '1', '0', '0', '0', '', '', '', NULL, NULL, 1)"
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
    poll_update($db, $_POST);
  }
  // TRAITEMENT DES NOMINATIONS.
  elseif ($_GET['action'] == 'election') {
    make_election($db, $_POST);
  }
  // TRAITEMENT DES NOMINATIONS.
  elseif ($_GET['action'] == 'target') {
    print random_player($db, $_POST);
  }
  else {
    die('Unknown action');
  }
}

if (!isset($_SESSION['default_tags'])) {
  $raw_tags = get_default_tags($db);
  $default_tags = $default_tags_per_category = $raw_default_tags = [];
  foreach ($raw_tags as $tag) {
    $raw_default_tags[$tag['id']] = $tag['name'];
    $default_tags_per_category[$tag['category']][] = $default_tags[] = ['value' => $tag['name'], 'code' => $tag['id']];
  }
  $_SESSION['raw_default_tags'] = $raw_default_tags;
  $_SESSION['default_tags'] = $default_tags;
  $_SESSION['default_tags_per_category'] = $default_tags_per_category;
}
print '<script>
var default_tags_per_category = ' . json_encode($_SESSION['default_tags_per_category']) . ';
var default_tags = ' . json_encode($_SESSION['default_tags']) . ';
</script>';

require "header.php";
require "ecran_forms.php";
require "footer.php";
