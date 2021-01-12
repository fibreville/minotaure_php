<?php session_start(); ?>
<html id="page-mj">
<?php
include "header.php";

if ($_SESSION['id'] != 1)
{
  print $_SESSION['id'];
  print '<span>Vous n\'êtes pas admin. <a href="index.php">Retournez en arrière !</a></span>';
  include "footer.php";
  die('</html>');
}

$cleanPost = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$action = $_GET['action'];

// TRAITEMENT DE LA SUPPRESSION DE L'AVENTURE
if ($action == "delete") {
  $query = $db->query("TRUNCATE TABLE sondage");
  $query = $db->query("INSERT INTO sondage VALUES ('','','','','','','','','','','','')");
  $query = $db->query("DELETE FROM hrpg WHERE id > 1;");
  $query = $db->query("TRUNCATE TABLE loot");
  $query = $db->query("TRUNCATE TABLE settings");
}

elseif ($action == 'settings') {
  // PARAMETRES AVENTURE.
  $_SESSION['adventure_name'] = $cleanPost['adventure_name'];
  $_SESSION['adventure_guide'] = $cleanPost['adventure_guide'];
  if (empty($cleanPost['image_url'])) {
    unset($_SESSION['image_url']);
  }
  else {
    $_SESSION['image_url'] = $cleanPost['image_url'];
  }

  foreach ($settings_set as $setting_set) {
    if (!empty($cleanPost[$setting_set])) {
      $query = $db->prepare("DELETE FROM settings WHERE name = :name");
      $query->execute([':name' => $setting_set]);
      $query = $db->prepare("INSERT INTO settings (name, value) VALUES (:name, :value)");
      $query->execute([':name' => $setting_set, ':value' => $cleanPost[$setting_set]]);
      $settings[$setting_set] = $cleanPost[$setting_set];
    }
  }
}


//$nb_dead = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp < 1 AND id > 1")->fetchColumn();
//$nbhma = $db->query("SELECT COUNT(*) FROM hrpg WHERE mind >= str AND id > 1")->fetchColumn();
//$nbhstr = $db->query("SELECT COUNT(*) FROM hrpg WHERE mind <= str AND id > 1")->fetchColumn();
$query = $db->prepare("SELECT nom FROM hrpg WHERE leader > 0 AND hp > 0 AND id > 1");
$query->execute();
$row = $query->fetch();
$leader = $row[0];

$query = $db->prepare("SELECT nom FROM hrpg WHERE traitre > 0 AND hp > 0 AND id > 1");
$query->execute();
$row = $query->fetch();
$traitre = $row[0];

// TRAITEMENT DE L'AJOUT DE TAGS.
if ($action == "tags") {
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

        $query2 = $db->prepare("UPDATE hrpg SET tag$key='$item' WHERE id='$id_joueur'");
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
  $difficulte = (int)$cleanPost['difficulte'];
  $penalite_type = $cleanPost['penalite_type'];
  $penalite = $cleanPost['penalite'];
  $sanction = $cleanPost['sanction'];
  if ($victimetag != "") {
    $specifity = "AND hp > 0 && (tag1 = '$victimetag' || tag2 = '$victimetag' || tag3 = '$victimetag')";
  }
  else {
    if (!empty($victime_multiple)) {
      $specifity = 'AND hp > 0 AND id IN(' . $victime_multiple . ')';
    }
    elseif ($victime == "*") {
      $specifity = 'AND hp > 0';
    }
    elseif ($victime == "mind") {
      $specifity = "AND hp > 0 AND mind >= str";
    }
    elseif ($victime == "str") {
      $specifity = "AND hp > 0 AND str >= mind";
    }
    else {
      $idh = $victime;
      $specifity = "AND id = $idh";
    }
  }
  $query = $db->prepare("SELECT $type, id, $penalite_type FROM hrpg WHERE id > 1 " . $specifity);
  $query->execute();
  $nb_victories = $nb_defeats = 0;
  foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $key => $row) {
    $valeur = $row[$type];
    $id_joueur = $row['id'];
    $valeur_sanction = $row[$penalite_type];
    // On tire un D6 + la difficulté allant de -2 à +2.
    if ($valeur <= ($difficulte + rand(1, 6))) {
      // Défaite.
      $failures[] = 'pj-' . $id_joueur;
      $nb_defeats++;
      $new_value = max(0, $valeur_sanction - $penalite);
      $query = $db->prepare("UPDATE hrpg SET $penalite_type=$new_value WHERE id=$id_joueur");
      $query->execute();
    }
    else {
      // Réussite.
      $wins[] = 'pj-' . $id_joueur;
      $nb_victories++;
    }
  }
  ?>
  <script>
    var data_failures = <?php echo json_encode($failures); ?>;
    var data_wins = <?php echo json_encode($wins); ?>;
  </script>
  <?php
  $sanction .= "<span class=epreuve-header><b>$nb_victories</b> victoire(s) pour <b>$nb_defeats</b> défaite(s)";
  if ($victimetag != "") {
    $sanction .= " pour le groupe $victimetag</span>";
  }
  $sanction .= '</span>';
}

// TRAITEMENT DU LOOT
elseif ($action == "loot") {
  $loot = $cleanPost['loot'];
  $propriete = $cleanPost['propriete'];
  $bonus = $cleanPost['bonus'];
  $qui = $cleanPost['qui'];

  $loot_expression = ($propriete . '=' . $propriete . '+' . $bonus);
  if (!empty($qui_multiple)) {
    $i = 0;
    $condition_sql = " WHERE id IN(" . explode(",", $qui_multiple) . ")";
  }
  elseif ($qui == "*") {
    $condition_sql = ' WHERE hp > 0 AND id > 1';
  }
  elseif ($qui == "mind") {
    $condition_sql = ' WHERE hp > 0 AND id > 1 AND mind >= str';
  }
  elseif ($qui == "str") {
    $condition_sql = ' WHERE hp > 0 AND id > 1 AND str >= mind';
  }
  else {
    $idh = $qui;
    $condition_sql = ' WHERE id =' . $idh;
  }
  // Selection des PJS à qui donner le loot.
  $query_select = $db->prepare("SELECT id FROM hrpg" . $condition_sql);
  $query_select->execute();

  // Mise à jour des stats des PJs concernés.
  $query_update = $db->prepare("UPDATE hrpg SET " . $loot_expression . $condition_sql);
  $query_update->execute();

  // Ajout du loot dans chaque inventaire.
  foreach ($query_select->fetchAll() as $key => $row) {
    $id_joueur = $row[0];
    $query = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
    $query->execute([
      ':idh' => $id_joueur,
      ':loot' => $loot,
    ]);
  }
}

if ($action == "clean") {
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
if ($action == "poll") {
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
if ($action == 'election') {
  $designe = '';
  $election = $cleanPost['election'];
  $random_tag = $cleanPost['random_tag'];
  $random_choice = $cleanPost['random_choice'];

  if ($election) {
    if ($election == "leader") {
      $query = $db->prepare("UPDATE hrpg SET leader = 0");
      $query->execute();
      $query = $db->prepare("SELECT id, nom FROM hrpg WHERE hp > 0 AND id > 1 AND id <> :leader ORDER BY RAND() LIMIT 1");
      $query->execute([':leader' => $leader ? $leader : 1]);
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $id_leader = $row['id'];
      $query = $db->prepare("UPDATE hrpg SET leader=1 WHERE id='$id_leader'");
      $query->execute();
      $leader = $row['nom'];
    }
    elseif ($election == "traitre") {
      $query = $db->prepare("UPDATE hrpg SET traitre = 0");
      $query->execute();
      $query = $db->prepare("SELECT id, nom FROM hrpg WHERE hp > 0 AND id > 1 AND id <> :traitre ORDER BY RAND() LIMIT 1");
      $query->execute([':traitre' => $traitre ? $traitre : 1]);
      $row = $query->fetch(PDO::FETCH_ASSOC);
      $id_traitre = $row['id'];
      $query = $db->prepare("UPDATE hrpg SET traitre=1 WHERE id='$id_traitre'");
      $query->execute();
      $traitre = $row['nom'];
    }
  }
  if (!empty($random_choice) || !empty($random_tag)) {
    if ($random_choice == "random") {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND id>1 ORDER BY RAND() LIMIT 1");
    }
    elseif ($random_choice == "random_mind") {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND id>1 AND mind >= str ORDER BY RAND() LIMIT 1");
    }
    elseif ($random_choice == "random_str") {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND mind < str AND id > 1 ORDER BY RAND() LIMIT 1");
    }
    elseif (!empty($random_tag)) {
      $query_select = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND id>1 AND (tag1 LIKE '$random_tag' || tag2 LIKE '$random_tag' || tag3 LIKE '$random_tag') ORDER BY RAND() LIMIT 1");
    }

    if (isset($query_select)) {
      $query_select->execute();
      $row = $query_select->fetch();
      $designe = $row[0] . " (#" . $row[1] . ")";
    }
  }
}
?>
<script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous">
</script>
<script src="js/ajax_mj.js"></script>

<div class="wrapper-intro">
  <div class="intro">
    <h2><?php print (isset($_SESSION['adventure_name']) ? $_SESSION['adventure_name'] : 'Notre Aventure'); ?></h2>
    <?php print (isset($_SESSION['adventure_guide']) ? $_SESSION['adventure_guide'] : "Rejoindre l'Aventure : maspero.blue/rpg/ ou taper !aventure"); ?>
  </div>
  <div class="intro-image"><img src="<?php print (isset($_SESSION['image_url']) ? $_SESSION['image_url']  : './img/logo.png'); ?>" /></div>
</div>
<div class="wrapper-main">
  <?php
  $query = $db->prepare("
    SELECT id,nom,hf,str,mind,hp,tag1,tag2,tag3
    FROM hrpg
    WHERE id > 1
    ORDER BY hp <= 0, nom");
  $query->execute();
  $players = $query->fetchAll();
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
      $list_players = [];
      foreach ($players as $key => $row) {
        $id_joueur = $row[0];
        $nom = $row[1];
        $hf = $row[2];
        $str = $row[3];
        $mind = $row[4];
        $hp = $row[5];
        $tags = [$row[6], $row[7], $row[8]];
        $str_tags = '';
        foreach ($tags as $tag) {
          if ($tag != '' && $tag != ' ') {
            $str_tags .= '<span>' . $tag . '</span>';
          }
        }
        $alive = ($hp <= 0 ? 'not-' : '') . 'alive';
        if ($hp <= 0) {
          $list_players[$id_joueur] = $nom . ' (☠️)';
        }
        else {
          $list_players[$id_joueur] = $nom;
        }

        switch ($hf) {
          case 1:
            $genre = "Femme";
            break;
          case 2:
            $genre = "Homme";
            break;
          default:
            $genre = "Non Binaire";
            break;
        }

        if ($str > 3 && $mind > 3) {
          $aptitude = $settings['carac1_group'] . ' et ' . $settings['carac2_group'];
        }
        elseif ($mind> 3) {
          $aptitude = $settings['carac1_group'];
        }
        elseif ($str > 3) {
          $aptitude = $settings['carac2_group'];
        }

        print "<div id=pj-$id_joueur class=\"$alive\">
          <b>$nom</b>
          <div class='tags'>
            <span class='genre-$hf'>$genre</span>
            $str_tags
          </div>";

        if ($hp > 0) {
          print "<div class='stats'>
                      <span>$aptitude</span>
                      <span>" . $settings['carac1_name'] . ": $mind</span>
                      <span>" . $settings['carac2_name'] . ": $str</span>
                      <span>Vie: $hp</span>
                      <span>Joueur #$id_joueur</span>
                    </div>
                  ";
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
              <option value="random_mind">Un personnage <?php print $settings['carac1_group']; ?></option>
              <option value="random_str">Un personnage <?php print $settings['carac2_group']; ?></option>
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
        <!-- FORMULAIRE DES EPREUVES-->
        <form method=post action=ecran.php?action=epreuve#epreuve>
          <span class="wrapper-penalite">
            <label for="type">Type</label>
            <select name="type">
              <option value="mind"><?php print $settings['carac1_name'] ?></option>
              <option value="str"><?php print $settings['carac2_name'] ?></option>
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
              <option value="mind"><?php print $settings['carac1_name'] ?></option>
              <option value="str"><?php print $settings['carac2_name'] ?></option>
            </select>
            <input type="number" name="penalite" size="10">
          </span>
          <fieldset>
            <legend>Qui ?</legend>
            <label for="victime">par groupe de personnages</label>
            <select name="victime">
              <option value=*>⭐ Tout le monde</option>
              <option value=mind>Chaque personnage <?php print $settings['carac1_group'] ?></option>
              <option value=str>Chaque personnage <?php print $settings['carac2_group'] ?></option>
              <?php
              foreach ($list_players as $key_player => $player) {
                print "<option value=$key_player>$player</option>";
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
                <option value=mind><?php print $settings['carac1_name']; ?></option>
                <option value=str><?php print $settings['carac2_name']; ?></option>
              </select>
              <input type="number" name="bonus" placeholder="bonus" size="10">
            </span>
          </fieldset>

          <label for="qui">À qui</label>
          <select name="qui">
            <option value=*>⭐ Tout le monde</option>
            <option value=mind>Chaque personnage <?php print $settings['carac1_group'] ?></option>
            <option value=str>Chaque personnage <?php print $settings['carac2_group'] ?></option>
            <?php
            foreach ($list_players as $key_player => $player) {
              print "<option value=$key_player>$player</option>";
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
        <div class="instructions">Entrez une liste de tags dans une des 3 cases pour les attributer aléatoirement à la population.</div>
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
        <form method=post action=ecran.php?action=settings>
          <fieldset>
            <legend>Intro</legend>
            <label for="adventure_name">Nom de l'aventure</label>
            <input type="text" name="adventure_name" size="10" value="<?php print $_SESSION['adventure_name']; ?>">
            <label for="adventure_guide">Adresse ip ou url pour rejoindre</label>
            <input type="text" name="adventure_guide" size="10" value="<?php print $_SESSION['adventure_guide']; ?>">
            <label for="adventure_guide">Image url</label>
            <input type="text" name="image_url" size="10" value="<?php print $_SESSION['image_url']; ?>">
          </fieldset>
          <fieldset>
            <legend>1ère caractéristique</legend>
            <label for="carac1_name">Nom</label>
            <input type="text" placeholder="esprit" name="carac1_name" value="<?php print $settings['carac1_name']; ?>">
            <label for="carac1_group">Un personnage fort dans cette carac est :</label>
            <input type="text" placeholder="malin" name="carac1_group" value="<?php print $settings['carac1_group']; ?>">
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
      <div><a href="ecran.php">Recharger</a></div>
    </div>
  </div>
<?php include "footer.php"; ?>
</html>