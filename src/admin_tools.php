<?php
/**
 * Contient toutes les fonctions utilisées par l'écran du MJ
 */

function admin_only() {
  if ($_SESSION['id'] != 1) {
    include "header.php";
    print '<span>Vous n\'êtes pas admin. <a href="index.php">Retournez en arrière !</a></span>';
    include "footer.php";
    die('</html>');
  }
}

function save_in_file($path, $content) {
  if (!file_exists($path)) {
    touch($path);
  }
  file_put_contents($path, $content);
  chmod($path, 0664); // Make the file removable by cli tests.
}

function delete_adventure($db, $tmp_path) {
  $db->query("TRUNCATE TABLE sondage");
  $db->query("INSERT INTO sondage VALUES ('','','','','','','','','','','','')");
  $db->query("DELETE FROM hrpg WHERE id > 1;");
  $db->query("DELETE FROM character_tag");
  $db->query("DELETE FROM tag");
  $db->query("ALTER TABLE hrpg AUTO_INCREMENT = 2");
  $db->query("TRUNCATE TABLE loot");
  unset($_SESSION['traitre']);
  unset($_SESSION['leader']);
  unset($_SESSION['default_raw_tags']);
  unset($_SESSION['default_tags_per_category']);
  unset($_SESSION['default_tags']);
  $_SESSION['settings'] = [];
  @unlink($tmp_path . '/game_timestamp.txt');
  @unlink($tmp_path . '/settings_timestamp.txt');
  @unlink($tmp_path . '/settings.txt');
}

function clean_adventure($db) {
  $_SESSION['current_poll'] = FALSE;
  $db->query("UPDATE sondage SET choix=''");
  $db->query("UPDATE hrpg SET vote='0'");
  $db->query("UPDATE hrpg SET traitre='1' WHERE traitre='2'");
  $db->query("UPDATE hrpg SET leader='1' WHERE leader='2'");
}

function save_new_settings($post, $tmp_path) {
  $_SESSION['settings']['adventure_name'] = $post['adventure_name'];
  $_SESSION['settings']['adventure_guide'] = $post['adventure_guide'];
  $_SESSION['settings']['carac1_name'] = $post['carac1_name'];
  $_SESSION['settings']['carac1_group'] = $post['carac1_group'];
  $_SESSION['settings']['carac2_name'] = $post['carac2_name'];
  $_SESSION['settings']['carac2_group'] = $post['carac2_group'];
  save_in_file($tmp_path . '/settings.txt', serialize($_SESSION['settings']));
  save_in_file($tmp_path . '/settings_timestamp.txt', time());
}

function delete_tag_category($db, $category_id) {
  if (is_numeric($category_id)) {
    // Suppression des liaisons personnages / tags.
    $query = $db->prepare("
    DELETE character_tag 
    FROM character_tag
    LEFT JOIN tag ON tag.id = character_tag.id_tag
    WHERE tag.category = :id_category
  ");
    $query->execute([':id_category' => $category_id]);
    // Suppression des tags.
    $query = $db->prepare("DELETE FROM tag WHERE category = :id_category");
    $query->execute([':id_category' => $category_id]);
    unset($_SESSION['default_raw_tags']);
    unset($_SESSION['default_tags_per_category']);
    unset($_SESSION['default_tags']);
  }
}

/**
 * @param $db : the database active connection
 * @param $post : submitted post data. This array should contains zero to three
 * entries with the following keys : tag1, tag2, tag3.
 * Each entry should contain a JSON array following this structure :
 * [{"value":"taglabel"},{"value","taglabel3"},...]
 */
function add_new_tags($db, $post) {
  unset($_SESSION['default_raw_tags']);
  unset($_SESSION['default_tags_per_category']);
  unset($_SESSION['default_tags']);

  $i = 0;
  while ($i++ < 3) {
    $decoded = html_entity_decode($post['tag' . $i]);
    $decoded = json_decode($decoded, TRUE);

    if (!empty($decoded)) {
      foreach($decoded as &$tag) {
        $query = $db->prepare("INSERT INTO tag (name, category) VALUES (:name,:category_id)");
        $query->execute([':name' => $tag['value'], ':category_id' => $i]);
      }

      // Récupération des joueurs vivants.
      $query = $db->query("
        SELECT hrpg.id
        FROM hrpg
        WHERE hrpg.hp > 0 
        AND hrpg.id > 1
        ORDER BY RAND()
      ");
      $results_players = $query->fetchAll(PDO::FETCH_COLUMN);

      // Récupération des tags de la catégorie.
      if (!empty($results_players)) {
        $query = $db->prepare("
          SELECT id
          FROM tag
          WHERE category = :id
        ");
        $query->execute([':id' => $i]);
        $results_tags = $query->fetchAll(PDO::FETCH_COLUMN);
        $size = count($results_tags);

        // Assignation aléatoire d'un tag de la catégorie par personnage.
        $insertions = [];
        foreach ($results_players as $id_character) {
          $insertions[] = "('". $id_character ."','" . $results_tags[rand(0, $size - 1)] . "')";
        }
        $db->query("INSERT INTO character_tag (id_player,id_tag) VALUES " . implode(',', $insertions));
        // Mise à jour du log des joueurs concernés.
        $query = $db->prepare("
        UPDATE hrpg
        SET lastlog=:time,log='Vous avez un nouveau tag'
        WHERE id IN (" . implode(',', $results_players) . ")"
        );
        $query->execute([':time' => time()]);
      }
    }
  }
}

/**
 * @param string $type depends on the type of data passed in $data :
 * - 'all' for all players (default)
 * - 'tags' for an array of tag ids in $data
 * - 'players' for an array of player ids in $data
 * - 'carac1' for players with a strong carac1
 * - 'carac2' for players with a strong carac2
 * @param mixed $data array for tags or players $type, string otherwise
 *
 * @return string
 */
function generate_target_query_part($type = 'all', $data) {
  if ($type == 'tags' && !empty($data)) {
    return 'LEFT JOIN character_tag ct ON ct.id_player = hrpg.id' .
      ' WHERE ct.id_tag IN (' . implode(',', array_keys($data)) . ') AND hp > 0';
  }
  elseif ($type == 'players' && !empty($data)) {
    return 'WHERE id > 1 AND hp > 0 AND id IN(' . implode(',', array_keys($data)) . ')';
  }
  elseif ($type == 'all') {
    return 'WHERE id > 1 AND hp > 0';
  }
  elseif ($type == 'carac1') {
    return 'WHERE id > 1 AND hp > 0 AND carac1 > 14';
  }
  elseif ($type == 'carac2') {
    return 'WHERE id > 1 AND hp > 0 AND carac2 > 14';
  }
  return FALSE;
}

function update_events($db, $post) {
  $data = [];
  if (!empty($post['victimetag'])) {
    $data = decode_tags($post['victimetag']);
    $type_target = 'tags';
  }
  elseif (!empty($post['victime_multiple'])) {
    $data = decode_tags($post['victime_multiple']);
    $type_target = 'players';
  }
  else {
    $type_target = $post['victime'];
  }

  $users = $db->query(
    'SELECT ' . $post['type'] . ',id,' . $post['penalite_type']
    . ' FROM hrpg '
    . generate_target_query_part($type_target, $data)
  );
  $loosers = $winners = $failures = [];
  foreach ($users->fetchAll(PDO::FETCH_ASSOC) as $key => $user) {
    // On tire un D20. 1 = échec systématique, 20 = réussite systématique.
    $critical_die = rand(1, 20);
    // On tire un D20 + la difficulté allant de -10 à +10.
    if ($critical_die == 1 || ($critical_die != 20 && $user[$post['type']] <= ($post['difficulte'] + rand(1, 20)))) {
      // Défaite.
      $failures[] = 'pj-' . $user['id'];
      $loosers[] = $user['id'];
    }
    else {
      // Réussite.
      $success[] = 'pj-' . $user['id'];
      $winners[] = $user['id'];
    }
  }

  if (!empty($loosers)) {
    $log = 'Vous avez raté l\'épreuve';
    if (!empty($post['reward'])) {
      $log .= ' et perdu ' . $post['penalite'] . ' '
        . ($post['penalite_type'] == 'hp' ? 'pv' : ($_SESSION['settings'][$post['penalite_type'] . '_name']));
    }

    $db->query(
      'UPDATE hrpg'
      . ' SET '
      . $post['penalite_type'] . '=GREATEST(' . $post['penalite_type'] . '-' . $post['penalite'] . ',0),'
      . 'lastlog="' . time() . '",log="' . $log . '."'
      . ' WHERE id IN (' . implode(',', $loosers) . ')'
    );
  }
  if (!empty($winners)) {
    $log = 'Vous avez réussi l\'épreuve';
    if (!empty($post['reward'])) {
      $log .= ' et gagné ' . $post['reward'] . ' '
      . ($post['reward_type'] == 'hp' ? 'pv' : ($_SESSION['settings'][$post['reward_type'] . '_name']));
    }

    $db->query(
      'UPDATE hrpg'
      . ' SET '
      . $post['reward_type'] . '=GREATEST(' . $post['reward_type'] . '+' . $post['reward'] . ',0),'
      . 'lastlog="' . time() . '",log="' . $log . '."'
      . ' WHERE id IN (' . implode(',', $winners) . ')'
    );
  }

  // On renvoie deux tableaux d'ids de PJ ayant échoué / réussi, à exploiter par le front.
  $sanction = '<div class=epreuve-cr><b>' . count($winners) . '</b> victoire(s) pour <b>' . count($loosers) . '</b> défaite(s)';
  if (isset($tags)) {
    $sanction .= ' pour le groupe ' . implode(', ', $tags);
  }
  $sanction .= '</div>';
  $_SESSION['sanction'] = $sanction;
  return '<script>data_failures = ' . json_encode($failures) . ', data_wins = ' . json_encode($success) . ';</script>';
}

function gen_loot_query_part($post) {
  $str = '';
  if (!empty($post['qui_multiple'])) {
    $data = decode_tags($post['qui_multiple']);
    $keys = implode(',', array_keys($data));
    $str =  'WHERE id IN (' . $keys . ')';
  }
  else {
    if (!empty($post['qui_tags'])) {
      $tags = decode_tags($post['qui_tags']);
      $str = 'LEFT JOIN character_tag ct ON ct.id_player = hrpg.id';
      $str .= ' WHERE ct.id_tag IN (' . implode(',', array_keys($tags)) . ')';
    }

    if (!empty($str)) {
      $str .= ' AND ';
    }
    else {
      $str = ' WHERE ';
    }
    if ($post['qui'] == 'all') {
      $str .= 'hp > 0 AND id > 1';
    }
    elseif ($post['qui'] == 'carac1') {
      $str .= 'hp > 0 AND id > 1 AND carac1 > 14';
    }
    elseif ($post['qui'] == 'carac2') {
      $str .= 'hp > 0 AND id > 1 AND carac2 > 14';
    }
  }
  return $str;
}

function update_loot($db, $post) {
  $post['bonus'] = isset($post['bonus']) ? $post['bonus'] : 0;
  if ($post['bonus'] > 0) {
    $post['bonus'] = '+' . $post['bonus'];
  }
  $condition_sql = gen_loot_query_part($post);
  if (empty($condition_sql)) {
    return;
  }

  // Selection des PJS à qui donner le loot.
  $query_select = $db->query('SELECT id FROM hrpg ' . $condition_sql);
  $ids = $query_select->fetchAll(PDO::FETCH_COLUMN);
  if (empty($ids)) {
    return;
  }
  if ($post['bonus'] != 0) {
    // Mise à jour des stats des PJs concernés.
    $db->query(
      'UPDATE hrpg'
      . ' SET lastlog="' . time() . '",'
      . 'log="Vous avez reçu un nouvel objet.",'
      . $post['propriete'] . '=' . $post['propriete'] . $post['bonus'] . ' WHERE id IN (' . implode(',', $ids) . ')'
    );
    $property = $post['bonus'] . ' ' . ($post['propriete'] == 'hp' ? 'pv' : $_SESSION['settings'][$post['propriete'] . '_name']);
  }
  else {
    $property = 'aucun effet';
  }

  // Ajout du loot dans chaque inventaire.
  $query = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
  foreach ($ids as $id) {
    $query->execute([
      ':idh' => $id,
      ':loot' => $post['loot'] . ' (' . $property . ')',
    ]);
  }
}

function poll_update($db, $post) {
  $_SESSION['current_poll'] = TRUE;
  if (!empty($post['choixtag'])) {
    $choixtag = implode(',', array_keys(decode_tags($post['choixtag'])));
  }
  else {
    $choixtag = '';
  }

  try {
    $query = $db->prepare(
      'UPDATE sondage'
      . ' SET choix=:choix,'
      . 'c1=:c1,c2=:c2,c3=:c3,c4=:c4,c5=:c5,c6=:c6,c7=:c7,c8=:c8,c9=:c9,c10=:c10,'
      . 'choixtag=:choixtag'
    );
    $query->execute([
      ':choix' => $post['choix'],
      ':c1' => $post['c1'],
      ':c2' => $post['c2'],
      ':c3' => $post['c3'],
      ':c4' => $post['c4'],
      ':c5' => $post['c5'],
      ':c6' => $post['c6'],
      ':c7' => $post['c7'],
      ':c8' => $post['c8'],
      ':c9' => $post['c9'],
      ':c10' => $post['c10'],
      ':choixtag' => $choixtag,
    ]);
  } catch (PDOException $e) {
    die("Erreur !: " . $e->getMessage() . "<br/>");
  }
}

function elect_player($db, $role) {
  $db->query('UPDATE hrpg SET ' . $role . ' = 0,lastlog="' . time() . '",log="Vous n\'êtes plus ' . $role . '." WHERE ' . $role . '=1');
  $query = $db->query("SELECT id, nom FROM hrpg WHERE hp > 0 AND id > 1 AND active = 1 AND '.$role.' = 0 ORDER BY RAND() LIMIT 1");
  $elected = $query->fetch(PDO::FETCH_ASSOC);
  $db->query('UPDATE hrpg SET ' . $role . '=1,lastlog="' . time() . '",log="Vous êtes le nouveau ' . $role . '." WHERE id="' . $elected['id'] . '"');
  $_SESSION[$role] = $elected['nom'];
}

function make_election($db, $post) {
  if (empty($post['name'])) {
    return;
  }

  if ($post['name'] == 'leader') {
    elect_player($db, 'leader');
  }
  elseif ($post['name'] == 'traitre') {
    elect_player($db, 'traitre');
  }
}

function random_player($db, $post) {
  $query_str = 'SELECT id FROM hrpg ';
  $where_add = '';
  if (!empty($post['random_tag'])) {
    $tags = decode_tags($post['random_tag']);
    $query_str .= 'LEFT JOIN character_tag ct ON ct.id_player = hrpg.id';
    $where_add =  ' AND ct.id_tag IN (' . implode(',', array_keys($tags)) . ')';
  }

  if ($post['random_choice'] == 'random_carac1') {
    $where_add .= ' AND carac1 > 14';
  }
  elseif ($post['random_choice'] == 'random_carac2') {
    $where_add .= ' AND carac2 > 14';
  }
  $limit = (is_numeric($post['limit']) && !empty($post['limit'])) ? $post['limit'] : 1;

  $query = $db->query($query_str . ' WHERE hp > 0 AND id > 1 AND active = 1' . $where_add . ' ORDER BY RAND() LIMIT ' . $limit);
  $rows = $query->fetchAll(PDO::FETCH_COLUMN);
  if (empty($rows)) {
    return '<script>players_chosen = false;</script>';
  }
  else {
    return '<script>players_chosen = ' . json_encode($rows) . ';</script>';
  }
}

function get_default_tags($db) {
  // Tags list creation
  $query_tags = $db->query("SELECT category, id, name FROM tag ORDER BY category,name");
  return $query_tags->fetchALL(PDO::FETCH_ASSOC);
}

function decode_tags($data) {
  $decoded = html_entity_decode($data);
  $decoded = json_decode($decoded, TRUE);
  $mapped = [];
  foreach($decoded as $value) {
    $mapped[$value['code']] = $value['value'];
  }
  return $mapped;
}