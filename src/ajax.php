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
    // Le nombre de votes de l'option la plus plébiscitée.
    $max_vote = $votes[0]['c'];
  }
  $pctot = 0;

  if ($choixtag != "") {
    print _("Vote limité au groupe : ") . implode(', ', $choixtag_data);
  }
  if (isset($leadvalue) && $leadvalue == 2) {
    print "<div class='poll-action leader-action'>" . sprintf(_('%s %s a utilisé son pouvoir et choisi : %s !'), $settings['role_leader'], $leader, $options['c' . $leadvote]). "</div>";
  }
  if (isset($traitrevalue) && $traitrevalue == 2) {
    print "<div class='poll-action traitor-action'>" . sprintf(_('%s %s a utilisé son pouvoir et annule un choix !'), $settings['role_traitre'], $traitre) . "</div>";
  }

  $vote_results_total = '<table>';
  foreach ($votes as $key => $vote) {
    $nb_votants = $vote['c'];
    
    $pc = 0;
    if ($nb_total > 0) $pc = round(($nb_votants * 100 / $nb_total), 2);
    $pctot += $nb_votants;
    $tmp_result = '';
    $classes = [];
    // Cette option est la plus plébiscitée (égalité possible).
    if (isset($max_vote) && $nb_votants == $max_vote) {
      $classes[] = 'winner-vote';
    }
    // Cette option a été choisie par le leader.
    if (isset($leadvalue) && $leadvalue == 2 && $vote['vote'] == $leadvote) {
       $classes[] = 'lead-vote';
    }
    // Cette option a été choisie par le traître.
    if (isset($traitrevalue) && $traitrevalue == 2 && $vote['vote'] == $traitrevote) {
       $classes[] = 'traitor-vote';
    }
    $vote_results_line = "<tr class=\"" . implode(' ', $classes) . "\">
      <td>" . $options['c' . $vote['vote']] . " : </td>
      <td>" . sprintf(_("%s / %s soit %s %%"), $nb_votants, $nb_total, $pc) . "</td>
    </tr>";
    print $vote_results_line;
    $vote_results_total .= $vote_results_line;
    unset($options['c' . $vote['vote']]);
  }
  foreach ($options as $option) {
    if ($option != '') {
      print "<td>" . $option . " : </td><td>" . _("aucun vote") . "</td></tr>";
    }
  }
  print "</table>";
  $_SESSION['last_vote'] = $vote_results_total . '</table>';
  if ($nb_total > 0) {
    print "<div>" . sprintf(_("Total votants : %s %%"), round(($pctot * 100 / $nb_total), 2)) . "</div>";
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

<h2><?php print _('Votre personnage'); ?></h2>
<?php
$still_ok = true;
if ($hp <= 0) {
  $still_ok = false;
  $ko_icon = '☠';
  $ko_message = sprintf(_("Votre personnage %s est mort."), $nom);
}
elseif ($settings['willpower_on'] && $wp <= 0) {
  $still_ok = false;
  $ko_icon = '🌑';
  $ko_message = sprintf(_("Votre personnage %s a sombré."), $nom);
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
      <div>💛 <?php print _("Vie : ") ?><b><?php print $hp; ?></b></div>
      <?php
        if ($settings['willpower_on'] != "") {
          print "<div>🌟 " . _("Volonté : ")  . "<b>" . $wp ."</b></div>";
        }
      ?>
    </div>
    <?php if ($leader > 0) { ?>
      <div class="pj-role"><?php print sprintf(_("Vous êtes actuellement <b>%s</b> !"), $settings['role_leader']); ?></div>
    <?php } ?>
    <?php if ($traitre > 0) { ?>
      <div class="pj-role"><?php print sprintf(_("Vous êtes actuellement <b>%s</b> !"), $settings['role_traitre']); ?></div>
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
      <div><?php print _("Décision en cours : "); ?><b><?php print $choix; ?></b></div>
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
            print "<div>
              <input type=checkbox name=lead value=1>
              <label for=lead>👑" . sprintf(_("Utiliser mon pouvoir de %s", $settings['role_leader'])) . "</label>
            </div>";
          }
          if ($traitre == 1) {
            print "<div>
              <input type=checkbox name=traitre value=1>
              <label for=traitre>🗡" . sprintf(_("Utiliser mon pouvoir de %s", $settings['role_traitre'])) . "</label>
            </div>";
          }
          print '</div>';
        }
        ?>
        <input type="submit" value="<?php print _("Votre choix est irrévocable"); ?>">
      </form>
      <?php
    }
    elseif ($vote != 0) {
      ?>
      <div><?php print _("Votre vote a bien été pris en compte"); ?></div>
      <?php
    }
    else {
      ?>
      <div><?php print _("Pas de décision en cours"); ?></div>
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
      <b><?php print _("Vous ne possédez rien de spécial"); ?></b>
      <?php
    }
    else {
      ?>
      <b><?php print _("Possessions : "); ?></b>
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
