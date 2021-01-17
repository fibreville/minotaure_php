<?php session_start(); ?>
<html id="page-mj">
<?php
include "header.php";

if ($_SESSION['id'] != 1) {
  print '<span>Vous n\'êtes pas admin. <a href="index.php">Retournez en arrière !</a></span>';
  include "footer.php";
  die('</html>');
}

$time = time();

$cleanPost = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$action = $_GET['action'];

if (isset($action)) {
  // mettre à jour le fichier servant à prévenir les PJ que leur fiche doit être rechargée.
  file_put_contents($tmp_path . '/game_timestamp.txt', time());
}

// TRAITEMENT DE LA SUPPRESSION DE L'AVENTURE
if ($action == "delete") {
  $query = $db->query("TRUNCATE TABLE sondage");
  $query = $db->query("INSERT INTO sondage VALUES ('','','','','','','','','','','','')");
  $query = $db->query("DELETE FROM hrpg WHERE id > 1;");
  $query = $db->query("ALTER TABLE hrpg AUTO_INCREMENT = 2");
  $query = $db->query("TRUNCATE TABLE loot");
  $_SESSION['settings'] = $settings = [];
  unset($_SESSION['traitre']);
  unset($_SESSION['leader']);
  unlink($tmp_path . '/game_timestamp.txt');
  unlink($tmp_path . '/settings_timestamp.txt');
  unlink($tmp_path . '/settings.txt');
}

elseif ($action == 'settings') {
  // PARAMETRES AVENTURE.
  $settings = $_SESSION['settings'];
  $settings['adventure_name'] = $cleanPost['adventure_name'];
  $settings['adventure_guide'] = $cleanPost['adventure_guide'];
  $settings['image_url'] = $cleanPost['image_url'];
  $settings['carac1_name'] = $cleanPost['carac1_name'];
  $settings['carac1_group'] = $cleanPost['carac1_group'];
  $settings['carac2_name'] = $cleanPost['carac2_name'];
  $settings['carac2_group'] = $cleanPost['carac2_group'];
  file_put_contents($tmp_path . '/settings.txt', serialize($settings));
  file_put_contents($tmp_path . '/settings_timestamp.txt', time());
}

// TRAITEMENT DE L'AJOUT DE TAGS.
elseif ($action == "tags") {
  $tag1 = $cleanPost['tag1'];
  $tag2 = $cleanPost['tag2'];
  $tag3 = $cleanPost['tag3'];
  $tags = ['1' => $tag1, '2' => $tag2, '3' => $tag3];
  foreach ($tags as $key => $tag) {
    if ($tag != "") {
      $z = substr_count($tag, ",");
      $travail = explode(",", $tag);
      $query = $db->prepare("SELECT id FROM hrpg WHERE hp > 0 and id > 1 ORDER BY RAND()");
      $query->execute();
      foreach ($query->fetchAll() as $key_id => $row) {
        $id_joueur = $row[0];
        $k = rand(0, $z);
        $item = $travail[$k];

        $query2 = $db->prepare("
            UPDATE hrpg
            SET lastlog='$time',log='Vous avez un nouveau tag',tag$key='$item'
            WHERE id='$id_joueur'");
        $query2->execute();
      }
    }
  }
}

// TRAITEMENT DES EPREUVES.
elseif ($action == "epreuve") {
  $victime = $cleanPost['victime'];
  $victimetag = $cleanPost['victimetag'];
  $victime_multiple = $cleanPost['victime_multiple'];
  $type = $cleanPost['type'];
  $difficulte = (int) $cleanPost['difficulte'];
  $penalite_type = $cleanPost['penalite_type'];
  $penalite = $cleanPost['penalite'];
  if ($victimetag != "") {
    $victimetag_sql = '("' . implode('", "', explode(',', $victimetag)) . '")';
    $specifity = "AND hp > 0 && (tag1 IN $victimetag_sql || tag2 IN $victimetag_sql || tag3 IN $victimetag_sql)";
  }
  else {
    if (!empty($victime_multiple)) {
      $specifity = 'AND hp > 0 AND id IN(' . $victime_multiple . ')';
    }
    elseif ($victime == "*") {
      $specifity = 'AND hp > 0';
    }
    elseif ($victime == "carac1") {
      $specifity = "AND hp > 0 AND carac1 >= carac2";
    }
    elseif ($victime == "carac2") {
      $specifity = "AND hp > 0 AND carac2 >= carac1";
    }
    else {
      $idh = $victime;
      $specifity = "AND id = $idh";
    }
  }
  $query = $db->prepare("SELECT $type, id, $penalite_type FROM hrpg WHERE id > 1 " . $specifity);
  $query->execute();
  $loosers = $winners = [];
  foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $key => $row) {
    $valeur = $row[$type];
    $id_joueur = $row['id'];

    // On tire un D6 + la difficulté allant de -2 à +2.
    if ($valeur <= ($difficulte + rand(1, 6))) {
      // Défaite.
      $failures[] = 'pj-' . $id_joueur;
      $loosers[] = $id_joueur;
    }
    else {
      // Réussite.
      $winners[] = 'pj-' . $id_joueur;
    }
  }
  $label_penalite = $cleanPost['penalite_type'] == 'hp' ? 'pv' : ($settings[$cleanPost['penalite_type'] . '_name']);
  $log = "Vous avez raté l''épreuve et perdu $penalite $label_penalite.";

  if (!empty($loosers) && $penalite) {
    $query = $db->prepare("
        UPDATE hrpg
        SET $penalite_type=GREATEST($penalite_type-$penalite,0),lastlog='$time',log='$log'
        WHERE id IN (" . implode(',', $loosers) . ")");
    $query->execute();
  }
  ?>
  <script>
    var data_failures = <?php echo json_encode($failures); ?>;
    var data_wins = <?php echo json_encode($winners); ?>;
  </script>
  <?php
  $nb_failures = count($loosers);
  $nb_victories =
  $sanction = "<span class=epreuve-header><b>" . count($winners) . "</b> victoire(s) pour <b>" . count($loosers) . "</b> défaite(s)";
  if ($victimetag != "") {
    $sanction .= " pour le groupe $victimetag</span>";
  }
  $sanction .= '</span>';
}

// TRAITEMENT DU LOOT
if ($action == "loot") {
  $loot = $cleanPost['loot'];
  $propriete = $cleanPost['propriete'];
  $qui_multiple = $cleanPost['qui_multiple'];
  $bonus = isset($cleanPost['bonus']) ? $cleanPost['bonus'] : 0;
  if ($bonus > 0) {
    $bonus = '+' . $bonus;
  }
  $qui = $cleanPost['qui'];

  if ($bonus != 0){
	$loot_expression = (", " . $propriete . '=' . $propriete . $bonus);
  }
  if (!empty($qui_multiple)) {
    $condition_sql = " WHERE id IN (" . $qui_multiple . ")";
  }
  elseif ($qui == "*") {
    $condition_sql = ' WHERE hp > 0 AND id > 1';
  }
  elseif ($qui == "carac1") {
    $condition_sql = ' WHERE hp > 0 AND id > 1 AND carac1 >= carac2';
  }
  elseif ($qui == "carac2") {
    $condition_sql = ' WHERE hp > 0 AND id > 1 AND carac2 >= carac1';
  }
  elseif (!empty($qui)) {
    $condition_sql = ' WHERE id =' . $qui;
  }
  else {
    $condition_sql = '';
  }
  if (!empty($condition_sql)) {
    // Selection des PJS à qui donner le loot.
    $query_select = $db->prepare("SELECT id FROM hrpg" . $condition_sql);
    $query_select->execute();
    $ids = $query_select->fetchAll(PDO::FETCH_COLUMN);

    // Mise à jour des stats des PJs concernés.
    $query_update = $db->prepare("
        UPDATE hrpg
        SET lastlog='$time',log='Vous avez reçu un nouvel objet.'" . $loot_expression . $condition_sql);
    $query_update->execute();

    // Ajout du loot dans chaque inventaire.
    if ($bonus == 0) {
      $property_name = 'aucun effet';
    }
    elseif ($propriete == 'hp') {
      $property_name = 'pv';
    }
    else {
      $property_name = $settings[$propriete . '_name'];
    }
    if (!empty($ids)) {
      foreach ($ids as $id) {
        $query = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
        $query->execute([
                ':idh' => $id,
                ':loot' => "$loot ($bonus $property_name)",
        ]);
      }
    }
  }
}

elseif ($action == "clean") {
  $_SESSION['current_poll'] = FALSE;
  $query = $db->prepare("UPDATE sondage SET choix=''");
  $query->execute();
  $query = $db->prepare("UPDATE hrpg SET vote='0'");
  $query->execute();
  $query = $db->prepare("UPDATE hrpg SET traitre='1' WHERE traitre='2'");
  $query->execute();
  $query = $db->prepare("UPDATE hrpg SET leader='1' WHERE leader='2'");
  $query->execute();
}

// TRAITEMENT DU SONDAGE.
elseif ($action == "poll") {
  $_SESSION['current_poll'] = TRUE;
  $choixtag = $cleanPost['choixtag'];
  $choixrandom = $cleanPost['choixrandom'];

  $choix = $cleanPost['choix'];
  $c1 = $cleanPost['c1'];
  $c2 = $cleanPost['c2'];
  $c3 = $cleanPost['c3'];
  $c4 = $cleanPost['c4'];
  $c5 = $cleanPost['c5'];
  $c6 = $cleanPost['c6'];
  $c7 = $cleanPost['c7'];
  $c8 = $cleanPost['c8'];
  $c9 = $cleanPost['c9'];
  $c10 = $cleanPost['c10'];

  try {
    $query = $db->prepare("UPDATE sondage SET choix=:choix,c1=:c1,c2=:c2,c3=:c3,c4=:c4,c5=:c5,c6=:c6,c7=:c7,c8=:c8,c9=:c9,c10=:c10,choixtag=:choixtag");
    $query->execute([
            ':choix' => $choix,
            ':c1' => $c1,
            ':c2' => $c2,
            ':c3' => $c3,
            ':c4' => $c4,
            ':c5' => $c5,
            ':c6' => $c6,
            ':c7' => $c7,
            ':c8' => $c8,
            ':c9' => $c9,
            ':c10' => $c10,
            ':choixtag' => $choixtag,
    ]);
  } catch (PDOException $e) {
    die("Erreur !: " . $e->getMessage() . "<br/>");
  }
}

// TRAITEMENT DES NOMINATIONS.
elseif ($action == 'election') {
  $designe = '';
  $election = $cleanPost['election'];
  $random_tag = $cleanPost['random_tag'];
  $random_choice = $cleanPost['random_choice'];

  if ($election) {
    if ($election == "leader") {
      $query = $db->prepare("UPDATE hrpg SET leader = 0,lastlog='$time',log='Vous n''êtes plus leader.' WHERE leader=1");
      $query->execute();
      $query = $db->prepare("SELECT id, nom FROM hrpg WHERE hp > 0 AND id > 1 AND leader = 0 ORDER BY RAND() LIMIT 1");
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $id_leader = $row['id'];
      $query = $db->prepare("UPDATE hrpg SET leader=1,lastlog='$time',log='Vous êtes le nouveau leader.' WHERE id='$id_leader'");
      $query->execute();
    }
    elseif ($election == "traitre") {
      $query = $db->prepare("UPDATE hrpg SET traitre = 0,lastlog='$time',log='Vous n''êtes plus traître.' WHERE traitre=1");
      $query->execute();
      $query = $db->prepare("SELECT id, nom FROM hrpg WHERE hp > 0 AND id > 1 AND traitre = 0 ORDER BY RAND() LIMIT 1");
      $query->execute();
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $id_traitre = $row['id'];
      $query = $db->prepare("UPDATE hrpg SET traitre=1,lastlog='$time',log='Vous êtes le nouveau traître.' WHERE id='$id_traitre'");
      $query->execute();
    }
  }
  if (!empty($random_choice) || !empty($random_tag)) {
    if ($random_choice == "random") {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp > 0 AND id > 1 ORDER BY RAND() LIMIT 1");
    }
    elseif ($random_choice == "random_carac1") {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp > 0 AND id > 1 AND carac1 >= carac2 ORDER BY RAND() LIMIT 1");
    }
    elseif ($random_choice == "random_carac2") {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp > 0 AND carac1 < carac2 AND id > 1 ORDER BY RAND() LIMIT 1");
    }
    elseif (!empty($random_tag)) {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp > 0 AND id > 1 AND (tag1 LIKE '$random_tag' || tag2 LIKE '$random_tag' || tag3 LIKE '$random_tag') ORDER BY RAND() LIMIT 1");
    }

    if (isset($query_select)) {
      $query_select->execute();
      $row = $query_select->fetch();
      $designe = $row[0] . " (#" . $row[1] . ")";
    }
  }
}
?>
<script src="js/ajax_mj.js"></script>

<div class="wrapper-intro">
  <div class="intro">
    <h2><?php print $settings['adventure_name']; ?></h2>
    <?php print $settings['adventure_guide']; ?>
  </div>
  <div class="intro-image"><img src="<?php print $settings['image_url']; ?>"/></div>
</div>
<div class="wrapper-main">
  <?php
  $stmt = $db->prepare("SELECT nom, id, hp FROM hrpg WHERE leader = 1");
  $stmt->execute([]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (empty($row)) {
    $leader = 'aucun';
    $id_leader = 0;
  }
  elseif ($row['hp'] <= 0) {
    $leader = 'mort (' . $row['nom'] . ')';
    $id_leader = 0;
  }
  else {
    $leader = $row['nom'];
    $id_leader = $row['id'];
  }

  $stmt = $db->prepare("SELECT nom, id, hp FROM hrpg WHERE traitre = 1");
  $stmt->execute([]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (empty($row)) {
    $traitre = 'aucun';
    $id_traitre = 0;
  }
  elseif ($row['hp'] <= 0) {
    $traitre = 'mort (' . $row['nom'] . ')';
    $id_traitre = 0;
  }
  else {
    $traitre = $row['nom'];
    $id_traitre = $row['id'];
  }

  $query = $db->prepare("
  SELECT id,nom,carac2,carac1,hp,tag1,tag2,tag3
  FROM hrpg
  WHERE id > 1
  ORDER BY hp <= 0, nom");
  $query->execute();
  $players = $query->fetchAll(PDO::FETCH_ASSOC);
  $nb_alive = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp > 0 AND id > 1")->fetchColumn();
  ?>
  <div class="wrapper-left">
    <div id="group-stats">
      <span>👑 Leader du groupe : <b><?php print "$leader"; ?></b></span>
      <span>🗡️ Traître du groupe : <b><?php print "$traitre"; ?></b></span>
      <span>💛 Joueurs encore en vie : <b><?php print $nb_alive . ' / ' . count($players); ?></b></span>
    </div>
    <div id="group">
      <?php
      $available_colors = ['#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'];
      $taken_colors = [];

      $list_players = [];
      foreach ($players as $key => $row) {
        $id_joueur = $row['id'];
        $nom = $row['nom'];
        $carac2 = $row['carac2'];
        $carac1 = $row['carac1'];
        $hp = $row['hp'];
        $tags = [$row['tag1'], $row['tag2'], $row['tag3']];
        $str_tags = '';
        foreach ($tags as $tag) {
          if ($tag != '' && $tag != ' ') {
            if (isset($taken_colors[$tag])) {
              $color = $taken_colors[$tag];
            }
            else {
              $color = $taken_colors[$tag] = array_pop($available_colors);
            }
            $str_tags .= "<span><span class='tag-bullet' style=background-color:$color></span>$tag</span>";
          }
        }
        $alive = ($hp <= 0 ? 'not-' : '') . 'alive';
        if ($hp <= 0) {
          $list_players[$id_joueur] = $nom . ' (☠️)';
        }
        else {
          $list_players[$id_joueur] = $nom;
        }

        $aptitude = '';
        if ($carac2 > 3 && $carac1 > 3) {
          $aptitude = $settings['carac1_group'] . ' et ' . $settings['carac2_group'];
        }
        elseif ($carac1 > 3) {
          $aptitude = $settings['carac1_group'];
        }
        elseif ($carac2 > 3) {
          $aptitude = $settings['carac2_group'];
        }

        print "<div id=pj-$id_joueur class=\"$alive\">
        <b class='pj-name'>$nom</b>
        <div class='tags'>
          $str_tags
        </div>";

        if ($hp > 0) {
          print "<div class='stats'>";
          if (!empty($aptitude)) { print "<span>$aptitude</span>"; }
          print "
            <span>" . $settings['carac1_name'] . ": $carac1</span>
            <span>" . $settings['carac2_name'] . ": $carac2</span>
            <span>Vie: $hp</span>
            <span>Joueur #$id_joueur</span>
            </div>";
        }
        print '</div>';
      }
      ?>
    </div>
  </div>
  <div class="wrapper-center">
    <div id="choices">
      <!-- FORMULAIRE ELECTIONS-->
      <div id="elections" class="active">
        <h3>Nominations</h3>
        <div class="instructions">
          Nommez des personnages clefs ou
          désignez un personnage pour votre histoire (par exemple avant une épreuve).
        </div>
        <form method=post action=ecran.php?action=election>
          <fieldset>
            <legend>👇 Nommer</legend>
            <select name="election">
              <option value="">Choisir</option>
              <option value="leader">👑 Nommer un nouveau leader</option>
              <option value="traitre">🗡️ Nommer un nouveau traitre</option>
            </select>
          </fieldset>
          <fieldset>
            <legend>🎲 Désigner au hasard</legend>
            <label for="random_choice">Parmi</label>
            <select name="random_choice">
              <option value="">Choisir</option>
              <option value="random">Un personnage</option>
              <option value="random_carac1">Un personnage <?php print $settings['carac1_group']; ?></option>
              <option value="random_carac2">Un personnage <?php print $settings['carac2_group']; ?></option>
            </select>
            <span>ou ayant le tag :</span>
            <input type="text" name="random_tag" placeholder="nomdutag" size="15">
          </fieldset>
          <input type="submit" value="OK">
        </form>
        <?php if (!empty($designe)) { ?>
          <div><b>Le joueur désigné est :</b>
            <div><?php print "$designe"; ?></div>
          </div>
        <?php } ?>
      </div>
      <div id="poll">
        <h3>Sondage</h3>
        <div class="instructions">Sondez la population pour connaitre la décision de la majorité.</div>
        <?php
        $query = $db->prepare("SELECT choix FROM sondage");
        $query->execute();
        $row = $query->fetch();
        $choix = $row[0];

        if ($choix != "") {
          print "<span class='poll-label'>$choix</span>";
          print "<div id='poll_results'><table><tr><td>En attente des votes.</td></tr></table></div>";
          print "<a class='submit-button' href=ecran.php?action=clean>Terminer le sondage</a>";
        }
        else {
          ?>
          <!-- FORMULAIRE DE SONDAGE-->
          <form method=post action=ecran.php?action=poll#poll>
            <input type="text" name="choix" size="30" placeholder="Intitulé du sondage">
            <input type="text" name="c1" size="30">
            <input type="text" name="c2" size="30">
            <input type="text" name="c3" size="30">
            <input type="text" name="c4" size="30">
            <input type="text" name="c5" size="30">
            <input type="text" name="c6" size="30">
            <input type="text" name="c7" size="30">
            <input type="text" name="c8" size="30">
            <input type="text" name="c9" size="30">
            <input type="text" name="c10" size="30">
            <label for="choixtag">Limiter à : </label>
            <input type="text" name="choixtag" size="15" placeholder="nomtag">
            <input type="submit" value="DÉLIBERER">
          </form>
          <?php
        }
        ?>
      </div>

      <div id="epreuve">
        <h3>Épreuves</h3>
        <div class="instructions">Faites passer un test à tout ou partie de la population.</div>
        <!-- FORMULAIRE DES EPREUVES-->
        <form method=post action=ecran.php?action=epreuve#epreuve>
          <span class="wrapper-penalite">
            <label for="type">Type</label>
            <select name="type">
              <option value="carac1"><?php print $settings['carac1_name'] ?></option>
              <option value="carac2"><?php print $settings['carac2_name'] ?></option>
            </select>
          </span>
          <span class="wrapper-penalite">
            <label for="difficulte">Difficulté</label>
            <select name="difficulte">
              <option value="-2">Trivial (-2)</option>
              <option value="-1">Facile (-1)</option>
              <option value="0" selected>Normal (0)</option>
              <option value="1">Difficile (+1)</option>
              <option value="2">Ardu (+2)</option>
            </select>
          </span>
          <span class="wrapper-penalite">
            <label for="penalite">Pénalité</label>
            <select name="penalite_type">
              <option value="hp">Santé</option>
              <option value="carac1"><?php print $settings['carac1_name'] ?></option>
              <option value="carac2"><?php print $settings['carac2_name'] ?></option>
            </select>
            <input type="number" name="penalite" size="2">
          </span>
          <fieldset>
            <legend>Qui ?</legend>
            <label for="victime">par groupe de personnages</label>
            <select class='pj-name' name="victime">
              <option value=*>⭐ Tout le monde</option>
              <option value=carac1>Chaque personnage <?php print $settings['carac1_group'] ?></option>
              <option value=carac2>Chaque personnage <?php print $settings['carac2_group'] ?></option>
              <?php
              foreach ($list_players as $key_player => $player) {
                print "<option value=$key_player>" . ucfirst($player) . "</option>";
              }
              ?>
            </select>
            <label for="victime_multiple">ou par id joueur</label>
            <input placeholder="1,4,9..." type="text" name="victime_multiple" size="10">
            <label for="victimetag">ou par Tag</label>
            <input type="text" name="victimetag" size="10">
          </fieldset>
          <input type="submit" value="ÉPROUVER">
        </form>
        <div class="epreuve-cr"><?php print $sanction; ?></div>
      </div>

      <div id="loot">
        <!-- FORMULAIRE DU LOOT-->
        <form method=post action=ecran.php?action=loot>
          <h3>Loot</h3>
          <div class="instructions">Attribuez un objet à toute ou partie de la population.</div>
          <label for="loot">Quoi</label>
          <input type="text" name="loot" size="30">
          <fieldset>
            <legend>Effet</legend>
            <span class="wrapper-penalite">
              <select name="propriete">
                <option value=hp>💛 Vie</option>
                <option value=carac1><?php print $settings['carac1_name']; ?></option>
                <option value=carac2><?php print $settings['carac2_name']; ?></option>
              </select>
              <input type="number" name="bonus" placeholder="bonus" size="10">
            </span>
          </fieldset>

          <label for="qui">À qui</label>
          <select name="qui">
            <option value=>Choisir</option>
            <option value=*>⭐ Tout le monde</option>
            <option value=carac1>Chaque personnage <?php print $settings['carac1_group'] ?></option>
            <option value=carac2>Chaque personnage <?php print $settings['carac2_group'] ?></option>
            <?php
            foreach ($list_players as $key_player => $player) {
              print "<option value='$key_player'>$player</option>";
            }
            ?>
          </select>
          <label for="qui_multiple">Plusieurs personnages</label>
          <input type="text" name="qui_multiple" size="10" placeholder="1,4,9...">
          <input type="submit" value="DONNER">
        </form>
      </div>

      <div id="tags">
        <!-- FORMULAIRE DES TAGS-->
        <h3>Tags</h3>
        <div class="instructions">Entrez une liste de tags dans une des 3 cases pour les attributer aléatoirement à la
          population.
        </div>
        <form method=post action=ecran.php?action=tags>
          <input type="text" name="tag1" size="30" placeholder="tag1,tag2,tag3...">
          <input type="text" name="tag2" size="30" placeholder="tag1,tag2,tag3...">
          <input type="text" name="tag3" size="30" placeholder="tag1,tag2,tag3...">
          <input type="submit" value="Attribuer">
        </form>
      </div>
      <div id="settings">
        <!-- FORMULAIRE DES PARAMETRES DE LA PARTIE-->
        <h3>Paramètres</h3>
        <div class="instructions">Changez les réglages de votre aventure avant ou pendant votre stream.</div>
        <form method="post" action="ecran.php?action=settings">
          <fieldset>
            <legend>Intro</legend>
            <label for="adventure_name">Nom de l'aventure</label>
            <input type="text" name="adventure_name" size="10" value="<?php print $settings['adventure_name']; ?>">
            <label for="adventure_guide">Adresse ip ou url pour rejoindre</label>
            <input type="text" name="adventure_guide" size="10" value="<?php print $settings['adventure_guide']; ?>">
            <label for="adventure_guide">Image url</label>
            <input type="text" name="image_url" size="10" value="<?php print $settings['image_url']; ?>">
          </fieldset>
          <fieldset>
            <legend>1ère caractéristique</legend>
            <label for="carac1_name">Nom</label>
            <input type="text" placeholder="esprit" name="carac1_name" value="<?php print $settings['carac1_name']; ?>">
            <label for="carac1_group">Un personnage fort dans cette carac est :</label>
            <input type="text" placeholder="malin" name="carac1_group"
                   value="<?php print $settings['carac1_group']; ?>">
          </fieldset>
          <fieldset>
            <legend>2ème caractéristique</legend>
            <label for="carac2_name">Nom</label>
            <input type="text" placeholder="corps" name="carac2_name" value="<?php print $settings['carac2_name']; ?>">
            <label for="carac2_group">Un personnage fort dans cette carac est :</label>
            <input type="text" placeholder="fort" name="carac2_group" value="<?php print $settings['carac2_group']; ?>">
          </fieldset>
          <input type="submit" value="Enregistrer">

          <div class="delete-game">
            <span id="delete-game" class="submit-button">🗑️ Détruire l'aventure</span>
            <a id="delete-game-confirm" class="submit-button" href="ecran.php?action=delete">Confirmez</a>
          </div>
        </form>
      </div>
      <div id="debug">
        <span>GAME TIMESTAMP : <?php print date('H:i:s', $game_timestamp); ?></span>
      </div>
    </div>
  </div>
  <div class="wrapper-right">
    <div id="tabs">
      <div data-target="elections">Tirage au sort</div>
      <div data-target="poll">Sondage</div>
      <div data-target="epreuve">Épreuve</div>
      <div data-target="loot">Loot</div>
      <div data-target="tags">Tags</div>
      <div data-target="settings">Paramètres</div>
      <div class="debug" data-target="debug">Debug</div>
      <div><a class='no-color' href="ecran.php">Recharger</a></div>
    </div>
  </div>
  <?php include "footer.php"; ?>
</html>