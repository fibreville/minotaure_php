<?php
/**
 * Contient les fonctions utiles pour répondre aux appels AJAX des écrans MJ et PJ.
 * Pour le MJ : réception des sondages.
 * Pour les PJ : réception des modifications de leurs fichiers
 * Le fonctionnement actuel est basé sur un fichier qui contient le timestamp de la dernière action du MJ.
 * L'appel ajax des PJ est directement renvoyé si ce timestamp n'a pas changé depuis le dernier appel.
 */

session_start();
include "connexion.php";
include "variables.php";
$settings = $_SESSION['settings'];

if ($_GET['role'] == 'heartbeat') {
  if ($_SESSION['id'] == 1) {
    print isset($_SESSION['current_poll']) ? json_encode($_SESSION['current_poll']) : '[]';
  }
  else {
    print json_encode($_SESSION['current_timestamp'] == 0 || !isset($_SESSION['current_timestamp']) || $_SESSION['current_timestamp'] < $game_timestamp);
  }
}
elseif ($_GET['role'] == 'mj' && $_SESSION['id'] == 1) {
  $stmt = $db->prepare("SELECT nom,leader,vote FROM hrpg WHERE leader > 0 AND hp > 0");
  $stmt->execute();
  $row = $stmt->fetch();
  if ($stmt->rowCount() > 0) {
    $leader = $row[0];
    $leadvalue = $row[1];
    $leadvote = $row[2];
  }

  $stmt = $db->prepare("SELECT nom,traitre,vote FROM hrpg WHERE traitre > 0 AND hp > 0");
  $stmt->execute();
  $row = $stmt->fetch();
  if ($stmt->rowCount() > 0) {
    $traitre = $row[0];
    $traitrevalue = $row[1];
    $traitrevote = $row[2];
  }

  $stmt = $db->prepare("SELECT * FROM sondage");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $options = array_slice($row, 1, 10, TRUE);
  $choix = $row['choix'];
  $choixtag = $row['choixtag'];

  $sql_count_part1 = "SELECT id FROM hrpg";
  $sql_count_part2 = " WHERE hrpg.hp > 0 AND hrpg.id > 1 AND active = 1";
  if (!empty($choixtag)) {
    $sql_count_part1 .= " LEFT JOIN character_tag ct ON ct.id_player = hrpg.id";
    $data = $_SESSION['raw_default_tags'];
    $keys = explode(',', $choixtag);
    foreach ($keys as $key) {
      $choixtag_data[$key] = $data[$key];
    }
    $choixtag_sql = '("' . implode('", "', $keys) . '")';
    $sql_count_part2 .= " AND (ct.id_tag IN $choixtag_sql)";
  }
  $sql_count_part2 .= ' GROUP BY id';
  $stmt = $db->prepare($sql_count_part1 . $sql_count_part2);
  $stmt->execute();
  $nb_total = $stmt->rowCount();

  print "<table>";
  $query = $db->prepare("
    SELECT vote, COUNT(id) c
    FROM hrpg
    WHERE vote > 0
    AND id > 1
    AND active = 1
    GROUP BY vote
    ORDER BY c DESC, vote ASC");
  $query->execute();
  $votes = $query->fetchAll(PDO::FETCH_ASSOC);
  if ($query->rowCount() > 0) {
    $max_vote = $votes[0]['c'];
  }
  $pctot = 0;

  if ($choixtag != "") {
    print "Vote limité au groupe : " . implode(', ', $choixtag_data);
  }
  if (isset($leadvalue) && $leadvalue == 2) {
    print "<div class='poll-action leader-action'>" . $settings['role_leader'] . " $leader a utilisé son pouvoir et choisi : " . $options['c' . $leadvote] . "!</div>";
  }
  if (isset($traitrevalue) && $traitrevalue == 2) {
    print "<div class='poll-action traitor-action'>" . $settings['role_traitre'] . " $traitre a utilisé son pouvoir et annule un choix.</div>";
  }

  $vote_results_total = '<table>';
  foreach ($votes as $key => $vote) {
    $nb_votants = $vote['c'];
    
    $pc = 0;
    if ($nb_total > 0) $pc = round(($nb_votants * 100 / $nb_total), 2);
    $pctot += $nb_votants;
    $tmp_result = '';
    $classes = [];
    if ($nb_votants == $max_vote) {
      $classes[] = 'winner-vote';
    }
    if (isset($leadvalue) && $leadvalue == 2 && $vote['vote'] == $leadvote) {
       $classes[] = 'lead-vote';
    }
    if (isset($traitrevalue) && $traitrevalue == 2 && $vote['vote'] == $traitrevote) {
       $classes[] = 'traitor-vote';
    }
    $vote_results_line = "<tr class=\"" . implode(' ', $classes) . "\"><td>" . $options['c' . $vote['vote']] . " : </td><td>$nb_votants / $nb_total soit $pc %</td></tr>";
    print $vote_results_line;
    $vote_results_total .= $vote_results_line;
    unset($options['c' . $vote['vote']]);
  }
  foreach ($options as $option) {
    if ($option != '') {
      print "<td>" . $option . " : </td><td>aucun vote</td></tr>";
    }
  }
  print "</table>";
  $_SESSION['last_vote'] = $vote_results_total . '</table>';
  if ($nb_total > 0) {
    print "<div>Total votants : " . round(($pctot * 100 / $nb_total), 2) . "%</div>";
  }
  if ($pctot == 100) {
    $_SESSION['current_poll'] = FALSE;
  }
}
elseif ($_GET['role'] == 'pj') {
  $_SESSION['current_timestamp'] = time();
  $id = $_SESSION['id'];
  $query_player = $db->prepare("
    SELECT hrpg.*
    FROM hrpg
    WHERE hrpg.id=:id
  ");
  $query_player->execute([':id' => $id]);
  $row = $query_player->fetch(PDO::FETCH_ASSOC);
  $id = $row['id'];
  $nom = $row['nom'];
  $carac3 = $row['carac3'];
  $carac2 = $row['carac2'];
  $carac1 = $row['carac1'];
  $hp = $row['hp'];
  $wp = $row['wp'];
  $leader = $row['leader'];
  $vote = $row['vote'];
  $traitre = $row['traitre'];
  if (!isset($_SESSION['lastlog']) || $row['lastlog'] == NULL || $_SESSION['lastlog'] < $row['lastlog']) {
    $log = $row['log'];
    $_SESSION['lastlog'] = $row['lastlog'];
  }

  $query_tags = $db->prepare("
    SELECT tag.id, tag.name
    FROM tag
    LEFT JOIN character_tag ct ON ct.id_tag = tag.id
    WHERE ct.id_player=:id
  ");
  $query_tags->execute([':id' => $id]);
  $results_tags = $query_tags->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<h2>Votre personnage</h2>
<?php
$still_ok = true;
if ($hp <= 0) {
  $still_ok = false;
  $ko_icon = '☠';
  $ko_message = "Votre personnage $nom est mort.";
}
elseif ($settings['willpower_on'] && $wp <= 0) {
  $still_ok = false;
  $ko_icon = '🌑';
  $ko_message = "Votre personnage $nom a sombré.";
}

if ($still_ok) { ?>
  <div class="character">
    <div class="character-name"><?php print $nom; ?></div>
    <div class="character-tags">
      <?php
      foreach ($results_tags as $tag) {
        print '<div>' . $tag . '</div>';
      }
      ?>
    </div>
    <div class="character-stats">
      <div><?php print $settings['carac1_name']; ?> : <b><?php print $carac1; ?></b></div>
      <div><?php print $settings['carac2_name']; ?> : <b><?php print $carac2; ?></b></div>
      <?php if ($settings['carac3_name'] != "") { ?>
      <div><?php print $settings['carac3_name']; ?> : <b><?php print $carac3; ?></b></div>
      <?php } ?>
      <div>💛 Pts de vie : <b><?php print $hp; ?></b></div>
      <?php
        if ($settings['willpower_on'] != "") {
          print "<div>🌟 Pts de volonté : <b>" . $wp ."</b></div>";
        }
      ?>
    </div>
    <?php if ($leader > 0) { ?>
      <div class="pj-role">Vous êtes actuellement <b><?php print $settings['role_leader']; ?></b> !</div>
    <?php } ?>
    <?php if ($traitre > 0) { ?>
      <div class="pj-role">Vous êtes actuellement <b><?php print $settings['role_traitre']; ?></b> !</div>
    <?php } ?>
  </div>
  <?php
  $stmt = $db->prepare("SELECT * FROM sondage");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $choix = $row['choix'];
  $cX = array_splice($row, 1, 10);
  $choixtag = explode(',', $row['choixtag']);
  ?>
  <div class="poll-choice">
    <?php
    if ($choix != "" && $vote == 0 && ($row['choixtag'] == "" || array_intersect($choixtag, array_keys($results_tags)))) {
    ?>
      <div>Décision en cours : <b><?php print $choix; ?></b></div>
      <form action=main.php method=post>
        <?php
        $key = 1;
        foreach ($cX as $c) {
          if ($c != "") {
            print "<div><input type=radio name=choix value=\"$key\"><label>" . $c . "</label></div>";
          }
          $key++;
        }

        if ($leader == 1 || $traitre == 1) {
          print '<div class="powers">';
          if ($leader == 1) {
            print "<div><input type=checkbox name=lead value=1><label for=lead>👑 Utiliser mon pouvoir de " . $settings['role_leader'] . "</b></label></div>";
          }
          if ($traitre == 1) {
            print "<div><input type=checkbox name=traitre value=1><label for=traitre>🗡️ Utiliser mon pouvoir de " . $settings['role_traitre'] . " et annuler le vote choisi<label></div>";
          }
          print '</div>';
        }
        ?>
        <input type="submit" value="Votre choix est irrévocable">
      </form>
      <?php
    }
    elseif ($vote != 0) {
      ?>
      <div>Votre vote a bien été pris en compte</div>
      <?php
    }
    else {
      ?>
      <div>Pas de décision en cours</div>
      <?php
    }
    ?>
  </div>
  <?php if (isset($log) && !empty($log)): ?>
  <div class="log">
    <?php print $log; ?>
  </div>
  <?php endif; ?>
  <div class="loot">
    <?php
    $stmt = $db->prepare("SELECT quoi FROM loot WHERE idh=:id ORDER BY id DESC");
    $stmt->execute([':id' => $id]);
    $loot = $stmt->fetchAll();
    if (count($loot) == 0) {
      ?>
      <b>Vous ne possédez rien de spécial</b>
      <?php
    }
    else {
      ?>
      <b>Possessions :</b>
      <?php
      foreach ($loot as $key => $row) {
        $quoi = $row[0];
        print "<br />$quoi";
      }
    }
    ?>
  </div>
  <?php
  }
  else { ?>
    <div class="wakeup"><?php print $ko_icon; ?></div>
    <div><?php print $ko_message; ?></div>
<?php
  }
}
