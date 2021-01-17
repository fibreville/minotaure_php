<?php
function admin_only()
{
  if ($_SESSION['id'] != 1) {
    include "header.php";
    print '<span>Vous n\'êtes pas admin. <a href="index.php">Retournez en arrière !</a></span>';
    include "footer.php";
    die('</html>');
  }
}
function avoid_form_resending_on_refresh()
{
  if(!empty($_POST)){
    $_SESSION['savePost'] = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);;
    header('Refresh:0;');
    die;
  }
  elseif(isset($_SESSION['savePost'])){
    $_POST = $_SESSION['savePost'];
    unset($_SESSION['savePost']);
  }
}
function save_in_file($path, $content)
{
  file_put_contents($path, $content);
  chmod($path, 0664); # Make removable in cli tests
}
function delete_adventure($db, $tmp_path)
{
  $query = $db->query("TRUNCATE TABLE sondage");
  $query = $db->query("INSERT INTO sondage VALUES ('','','','','','','','','','','','')");
  $query = $db->query("DELETE FROM hrpg WHERE id > 1;");
  $query = $db->query("ALTER TABLE hrpg AUTO_INCREMENT = 2");
  $query = $db->query("TRUNCATE TABLE loot");
  unset($_SESSION['traitre']);
  unset($_SESSION['leader']);
  $_SESSION['settings'] = [];
  @unlink($tmp_path . '/game_timestamp.txt');
  @unlink($tmp_path . '/settings_timestamp.txt');
  @unlink($tmp_path . '/settings.txt');
}
function clean_adventure($db)
{
  $_SESSION['current_poll'] = FALSE;
  $db->query("UPDATE sondage SET choix=''");
  $db->query("UPDATE hrpg SET vote='0'");
  $db->query("UPDATE hrpg SET traitre='1' WHERE traitre='2'");
  $db->query("UPDATE hrpg SET leader='1' WHERE leader='2'");
}
function save_new_settings($post, $tmp_path)
{
  $_SESSION['settings']['adventure_name'] = $post['adventure_name'];
  $_SESSION['settings']['adventure_guide'] = $post['adventure_guide'];
  $_SESSION['settings']['image_url'] = $post['image_url'];
  $_SESSION['settings']['carac1_name'] = $post['carac1_name'];
  $_SESSION['settings']['carac1_group'] = $post['carac1_group'];
  $_SESSION['settings']['carac2_name'] = $post['carac2_name'];
  $_SESSION['settings']['carac2_group'] = $post['carac2_group'];
  save_in_file($tmp_path . '/settings.txt', serialize($_SESSION['settings']));
  save_in_file($tmp_path . '/settings_timestamp.txt', time());
}
function add_new_tags($db, $post)
{
  $i = 0;
  while ($i++ < 3) {
    if (empty($post['tag'.$i])) {
      continue;
    }
    $z = substr_count($post['tag'.$i], ",");
    $travail = explode(",", $post['tag'.$i]);
    $query = $db->prepare("SELECT id FROM hrpg WHERE hp > 0 and id > 1 ORDER BY RAND()");
    $query->execute();
    foreach ($query->fetchAll() as $key_id => $row) {
      $id_joueur = $row[0];
      $k = rand(0, $z);
      $item = $travail[$k];

      $db->query(
                  "UPDATE hrpg"
                  ." SET lastlog='".time()."',log='Vous avez un nouveau tag',tag$i='$item'"
                  ." WHERE id='$id_joueur'"
                );
    }
  }
}
function gen_victime_query_part($post)
{
  if (!empty($post['victimetag'])) {
    $victimetag_sql = '("' . implode('", "', explode(',', $post['victimetag'])) . '")';
    return "hp > 0 && (tag1 IN $victimetag_sql || tag2 IN $victimetag_sql || tag3 IN $victimetag_sql)";
  }
  if (!empty($post['victime_multiple']))
    return 'hp > 0 AND id IN(' . $post['victime_multiple'] . ')';
  if ($post['victime'] == '*')
    return 'hp > 0';
  if ($post['victime'] == 'carac1')
    return 'hp > 0 AND carac1 >= carac2';
  if ($post['victime'] == 'carac2')
    return 'hp > 0 AND carac2 >= carac1';
  return 'id = '.$post['victime'];
}
function update_events($db,$post)
{
  $users = $db->query(
            'SELECT '.$post['type'].',id,'.$post['penalite_type']
            .' FROM hrpg'
            .' WHERE id > 1 AND ' . gen_victime_query_part($post)
          );
  $loosers = $winners = $failures = [];
  foreach ($users->fetchAll(PDO::FETCH_ASSOC) as $key => $user) {

    // On tire un D6 + la difficulté allant de -2 à +2.
    if ($user[$post['type']] <= ($post['difficulte'] + rand(1, 6))) {
      // Défaite.
      $failures[] = 'pj-' . $user['id'];
      $loosers[] = $user['id'];
    }
    else {
      // Réussite.
      $winners[] = 'pj-' . $user['id'];
    }
  }

  if (!empty($loosers) && !empty($post['penalite']) && !empty($post['penalite_type'])) {
    $log = 'Vous avez raté l\'épreuve et perdu '.$post['penalite'].' '
          .($post['penalite_type'] == 'hp' ? 'pv' : ($settings[$post['penalite_type'] . '_name'])).'.';
    $db->query(
        'UPDATE hrpg'
        .' SET '
            .$post['penalite_type'].'=GREATEST('.$post['penalite_type'].'-'.$post['penalite'].',0),'
            .'lastlog="'.time().'",log="'.$log.'"'
        .' WHERE id IN ('.implode(',', $loosers).')'
    );
  }
  echo '<script>var data_failures = '.json_encode($failures).';var data_wins = '.json_encode($winners).';</script>';
  $sanction = '<span class=epreuve-header><b>'.count($winners).'</b> victoire(s) pour <b>'.count($loosers).'</b> défaite(s)';
  if (!empty($post['victimetag'])) {
    $sanction .= ' pour le groupe '.$post['victimetag'].'</span>';
  }
  $sanction .= '</span>';
  return $sanction;
}
function gen_loot_query_part($post)
{
  if (!empty($post['qui_multiple']))
    return 'id IN ('.$post['qui_multiple'].')';
  if ($post['qui'] == '*')
    return 'hp > 0 AND id > 1';
  if ($post['qui'] == 'carac1')
    return 'hp > 0 AND id > 1 AND carac1 >= carac2';
  if ($post['qui'] == 'carac2')
    return 'hp > 0 AND id > 1 AND carac2 >= carac1';
  if (!empty($post['qui']))
    return 'id ='.$post['qui'];
}
function update_loot($db, $post)
{
  $post['bonus'] = isset($post['bonus']) ? $post['bonus'] : 0;
  if ($post['bonus'] >= 0) {
    $post['bonus'] = '+' . $post['bonus'];
  }
  $condition_sql = gen_loot_query_part($post);
  if (empty($condition_sql))
    return;

  // Selection des PJS à qui donner le loot.
  $query_select = $db->query('SELECT id FROM hrpg WHERE '.$condition_sql);
  $ids = $query_select->fetchAll(PDO::FETCH_COLUMN);
  $query_select->closeCursor();

  // Mise à jour des stats des PJs concernés.
  $db->query(
        'UPDATE hrpg'
        .' SET lastlog="'.time().'",'
            .'log="Vous avez reçu un nouvel objet.",'
            .$post['propriete'].'='.$post['propriete'].$post['bonus'].' WHERE '.$condition_sql
      );

  if (empty($ids))
    return;

  $property_name = $post['propriete'] == 'hp' ? 'pv' : $settings[$post['propriete'].'_name'];

  // Ajout du loot dans chaque inventaire.
  $query = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
  foreach ($ids as $id) {
    $query->execute([
            ':idh' => $id,
            ':loot' => $post['loot'].' ('.$post['bonus'].' '.$property_name.')'
          ]);
  }
}
function survey_update($db, $post)
{
  $_SESSION['current_poll'] = TRUE;
  try {
    $query = $db->prepare(
                    'UPDATE sondage'
                    .' SET choix=:choix,'
                          .'c1=:c1,c2=:c2,c3=:c3,c4=:c4,c5=:c5,c6=:c6,c7=:c7,c8=:c8,c9=:c9,c10=:c10,'
                          .'choixtag=:choixtag'
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
            ':choixtag' => $post['choixtag'],
    ]);
  } catch (PDOException $e) {
    die("Erreur !: " . $e->getMessage() . "<br/>");
  }
}
function elect_player($db, $role)
{
  $db->query('UPDATE hrpg SET '.$role.' = 0,lastlog="'.time().'",log="Vous n\'êtes plus '.$role.'." WHERE '.$role.'=1');
  $query = $db->query("SELECT id, nom FROM hrpg WHERE hp > 0 AND id > 1 AND '.$role.' = 0 ORDER BY RAND() LIMIT 1");
  $elected = $query->fetch(PDO::FETCH_ASSOC);
  $db->query('UPDATE hrpg SET '.$role.'=1,lastlog="'.time().'",log="Vous êtes le nouveau '.$role.'." WHERE id="'.$elected['id'].'"');
  $_SESSION[$role] = $elected['nom'];
}
function make_election($db,$post)
{
  if (!isset($post['election']) or empty($post['random_choice']) && empty($post['random_tag']))
    return;

  if ($post['election'] == 'leader') {
    elect_player($db, 'leader');
  }
  elseif ($post['election'] == 'traitre') {
    elect_player($db, 'traitre');
  }

  if ($post['random_choice'] == 'random') {
    $where_add = '';
  }
  elseif ($post['random_choice'] == 'random_carac1') {
    $where_add = ' AND carac1 >= carac2';
  }
  elseif ($post['random_choice'] == 'random_carac2') {
    $where_add = ' AND carac1 < carac2';
  }
  elseif (!empty($post['random_tag'])) {
    $where_add = ' AND (tag1 LIKE "'.$post['random_tag'].'" || tag2 LIKE "'.$post['random_tag'].'" || tag3 LIKE "'.$post['random_tag'].'")';
  }
  else
    return;

  $row = $db->query('SELECT nom,id FROM hrpg WHERE hp > 0 AND id > 1'.$where_add.' ORDER BY RAND() LIMIT 1')->fetch();

  return $row[0].' (#'.$row[1].')';
}
?>