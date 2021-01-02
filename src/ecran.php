<html id="page-mj">
<?php include "header.php"; ?>
<?php
$id = $_SESSION['id'];

if ($id == 1) {
$action = $_GET['action'];
$sequence = $_POST['sequence'];
$choix = $_POST['choix'];
$adventure_name = $_POST['adventure_name'];
$adventure_guide = $_POST['adventure_guide'];

$c1 = $_POST['c1'];
$c2 = $_POST['c2'];
$c3 = $_POST['c3'];
$c4 = $_POST['c4'];
$c5 = $_POST['c5'];
$c6 = $_POST['c6'];
$c7 = $_POST['c7'];
$c8 = $_POST['c8'];
$c9 = $_POST['c9'];
$c10 = $_POST['c10'];
$choixtag = $_POST['choixtag'];

$choixrandom = $_POST['choixrandom'];

$loot = $_POST['loot'];
$propriete = $_POST['propriete'];
$qui = $_POST['qui'];
$victime = $_POST['victime'];
$victimetag = $_POST['victimetag'];
$type = $_POST['type'];
$difficulte = $_POST['difficulte'];
$penalite = $_POST['penalite'];
$sanction = $_POST['sanction'];
$tag1 = $_POST['tag1'];
$tag2 = $_POST['tag2'];
$tag3 = $_POST['tag3'];

$tags = [$tag1, $tag2, $tag3];
foreach ($tags as $key => $tag) {
  if ($tag != "") {
    $z = substr_count($tag, ",");
    $travail = explode(",", $tag);

    $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 ORDER BY RAND()");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $key => $row) {
      $id_joueur = $row[0];
      $k = rand(0, $z);
      $item = $travail[$k];

      $stmt2 = $db->prepare("UPDATE hrpg SET tag$key='$item' WHERE id='$id_joueur'");
      $stmt2->execute();
    }
  }
}

if ($type != "") {
  if ($victimetag != "") {
    $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE hp>0 && (tag1='$victimetag' || tag2='$victimetag' || tag3='$victimetag')");
    $sanction .= "<b>$l</b> victoires pour <b>$k</b> défaites pour le groupe $victimetag.";
  }
  else {
    $travail = explode(",", $victime);
    $modif = $_POST["penalite"];
    $modifquoi = $_POST["penalite_type"];

    if ($travail[1] != "") {
      //TODO : fix it
      $i = 0;
      while ($travail[$i] > 0) {
        $id_joueur = $travail[$i];
        $stmt = $db->prepare("SELECT $type,nom,id FROM hrpg WHERE id=$id_joueur");
        $stmt->execute();
        $i++;
      }
    }
    elseif ($victime == "*") {
      $specifity = 'AND hp>0';
    }
    elseif ($victime == "m") {
      $specifity = "AND hp>0 AND mind>=str";
    }
    elseif ($victime == "s") {
      $specifity = "AND hp>0 AND str>=mind";
    }
    else {
      $idh = $victime;
      $specifity = "AND id=$idh";
    }
  }
  $stmt = $db->prepare("SELECT $type,nom,id,$modifquoi FROM hrpg WHERE id>1 " . $specifity);
  $stmt->execute();
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $key => $row) {
    $valeur = $row[$type];
    $nom = utf8_encode($row['nom']);
    $id_joueur = $row['id'];
    $valeur_sanction = $row[$modifquoi];
    if (($valeur + rand(1, 6)) < ($difficulte + rand(1, 6))) {
      // defaite
      $sanction .= "<font color=red>$nom a échoué !</font>";
      $k++;
      $new_value = $valeur_sanction - $modif;
      $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$new_value WHERE id=$id_joueur");
      $stmt->execute();
    }
    else {
      $sanction .= "<font color=009900>$nom a réussi !</font>";
      $l++;
    }
  }
  $sanction .= "<b>$l</b> victoires pour <b>$k</b> défaites.";
}

if ($loot != "") {
  $travail = explode(",", $qui);
  if ($travail[1] != "") {
    $modif = substr($propriete, 0, 2);
    $modifquoi = substr($propriete, 2, 1);

    if ($modifquoi == "h") {
      $modifquoi = "hp";
    }
    if ($modifquoi == "m") {
      $modifquoi = "mind";
    }
    if ($modifquoi == "s") {
      $modifquoi = "str";
    }

    $modif = $modifquoi . $modif;

    $i = 0;
    while ($travail[$i] > 0) {
      $id_joueur = $travail[$i];
      $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$id_joueur");
      $stmt->execute();
      $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
      $stmt->execute([
        ':idh' => $id_joueur,
        ':loot' => $loot,
      ]);

      $i++;
    }
  }
  elseif ($qui == "*") {
    $modif = substr($propriete, 0, 2);
    $modifquoi = substr($propriete, 2, 1);

    if ($modifquoi == "h") {
      $modifquoi = "hp";
    }
    if ($modifquoi == "m") {
      $modifquoi = "mind";
    }
    if ($modifquoi == "s") {
      $modifquoi = "str";
    }

    $modif = $modifquoi . $modif;

    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE hp>0");
    $stmt->execute();

    $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $key => $row) {
      $id_joueur = $row[0];

      $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
      $stmt->execute([
        ':idh' => $id_joueur,
        ':loot' => $loot,
      ]);
    }
  }
  elseif ($qui == "m") {
    $modif = substr($propriete, 0, 2);
    $modifquoi = substr($propriete, 2, 1);

    if ($modifquoi == "h") {
      $modifquoi = "hp";
    }
    if ($modifquoi == "m") {
      $modifquoi = "mind";
    }
    if ($modifquoi == "s") {
      $modifquoi = "str";
    }

    $modif = $modifquoi . $modif;

    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE hp>0 AND mind>=str");
    $stmt->execute();

    $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 AND id>1 AND mind>=str");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $key => $row) {
      $id_joueur = $row[0];

      $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
      $stmt->execute([
        ':idh' => $id_joueur,
        ':loot' => $loot,
      ]);
    }
  }
  elseif ($qui == "s") {
    $modif = substr($propriete, 0, 2);
    $modifquoi = substr($propriete, 2, 1);

    if ($modifquoi == "h") {
      $modifquoi = "hp";
    }
    if ($modifquoi == "m") {
      $modifquoi = "mind";
    }
    if ($modifquoi == "s") {
      $modifquoi = "str";
    }

    $modif = $modifquoi . $modif;

    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE hp>0 AND mind<str");
    $stmt->execute();

    $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 AND id>1 AND mind<str");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $key => $row) {
      $id_joueur = $row[0];

      $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
      $stmt->execute([
        ':idh' => $id_joueur,
        ':loot' => $loot,
      ]);

    }
  }
  else {
    $idh = $qui;
    $stmt = $db->prepare("INSERT INTO loot(idh,quoi) VALUES (:idh,:loot)");
    $stmt->execute([
      ':idh' => $idh,
      ':loot' => $loot,
    ]);

    // propriete = +1h / -1m / +1s

    $modif = substr($propriete, 0, 2);
    $modifquoi = substr($propriete, 2, 1);

    if ($modifquoi == "h") {
      $modifquoi = "hp";
    }
    if ($modifquoi == "m") {
      $modifquoi = "mind";
    }
    if ($modifquoi == "s") {
      $modifquoi = "str";
    }

    $modif = $modifquoi . $modif;

    $stmt = $db->prepare("UPDATE hrpg SET $modifquoi=$modif WHERE id=$idh");
    $stmt->execute();
  }
}

if ($action == "clean") {
  $stmt = $db->prepare("UPDATE sondage SET choix=''");
  $stmt->execute();
  $stmt = $db->prepare("UPDATE hrpg SET vote='0'");
  $stmt->execute();
  $stmt = $db->prepare("UPDATE hrpg SET traitre='1' WHERE traitre='2'");
  $stmt->execute();
  $stmt = $db->prepare("UPDATE hrpg SET leader='1' WHERE leader='2'");
  $stmt->execute();
}

if ($choix != "") {
  try {
    $stmt = $db->prepare("UPDATE sondage SET choix=:choix,c1=:c1,c2=:c2,c3=:c3,c4=:c4,c5=:c5,c6=:c6,c7=:c7,c8=:c8,c9=:c9,c10=:c10,choixtag=:choixtag");
    $stmt->execute([
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
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
  }
}

if ($sequence != "") {
  $stmt = $db->prepare("INSERT INTO epopee(text) VALUES (:sequence)");
  $stmt->execute([
    ':sequence' => $sequence,
  ]);
}

$designe = "(pas de tirage en cours)";

if ($action == "random") {
  $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND id>1 ORDER BY RAND() LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
}
if ($action == "randomm") {
  $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND id>1 AND mind>=str ORDER BY RAND() LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
}

if ($choixrandom != "") {
  $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND id>1 AND (tag1 LIKE '$choixrandom' || tag2 LIKE '$choixrandom' || tag3 LIKE '$choixrandom') ORDER BY RAND() LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
}

if ($action == "randoms") {
  $stmt = $db->prepare("SELECT nom,id FROM hrpg WHERE hp>0 AND mind<str AND id>1 ORDER BY RAND() LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $designe = utf8_encode($row[0]) . " (#" . $row[1] . ")";
}

if ($action == "leader") {
  $stmt = $db->prepare("UPDATE hrpg SET leader=0");
  $stmt->execute();
  $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 AND id>1 ORDER BY RAND() LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $id_leader = $row[0];
  $stmt = $db->prepare("UPDATE hrpg SET leader=1 WHERE id='$id_leader'");
  $stmt->execute();
}

if ($action == "traitre") {
  $stmt = $db->prepare("UPDATE hrpg SET traitre=0");
  $stmt->execute();
  $stmt = $db->prepare("SELECT id FROM hrpg WHERE hp>0 AND id>1 ORDER BY RAND() LIMIT 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $id_traitre = $row[0];
  $stmt = $db->prepare("UPDATE hrpg SET traitre=1 WHERE id='$id_traitre'");
  $stmt->execute();
}

$nbhv = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp>0 AND id>1")->fetchColumn();
$nbhm = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp<1 AND id>1")->fetchColumn();
$nbhma = $db->query("SELECT COUNT(*) FROM hrpg WHERE mind>=str")
  ->fetchColumn();
$nbhstr = $db->query("SELECT COUNT(*) FROM hrpg WHERE mind<str AND id>1")
  ->fetchColumn();
$stmt = $db->prepare("SELECT nom,leader,vote FROM hrpg WHERE leader>0 AND hp>0 AND id>1");
$stmt->execute();
$row = $stmt->fetch();
$leader = utf8_encode($row[0]);
$leadvalue = utf8_encode($row[1]);
$leadvote = utf8_encode($row[2]);

$stmt = $db->prepare("SELECT nom,traitre,vote FROM hrpg WHERE traitre>0 AND hp>0 AND id>1");
$stmt->execute();
$row = $stmt->fetch();
$traitre = utf8_encode($row[0]);
$traitrevalue = utf8_encode($row[1]);
$traitrevote = utf8_encode($row[2]);
?>
<script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous">
</script>
<script src="js/ajax_mj.js"></script>
<h2><?php print (isset($adventure_name) ? $adventure_name : 'Notre Aventure'); ?></h2>
<div><?php print (isset($adventure_guide) ? $adventure_guide : "Rejoindre l'Aventure : maspero.blue/rpg/ ou taper !aventure"); ?></div>
<div class="wrapper-main">
  <?php
  $stmt = $db->prepare("
    SELECT id,nom,hf,str,mind,hp,tag1,tag2,tag3 
    FROM hrpg 
    WHERE id > 1
    ORDER BY hp <= 0, nom");
  $stmt->execute();
  $players = $stmt->fetchAll();
  ?>
  <div class="wrapper-left">
    <div id="group-stats">
      <span>Leader du groupe : <b><?php print "$leader"; ?></b></span>
      <span>Traître du groupe : <b><?php print "$traitre"; ?></b></span>
      <span>Joueurs encore en vie : <b><?php print $nbhv . ' / ' . count($players); ?></b></span>
    </div>
    <div id="group">
      <?php
      $list_players = [];
      foreach ($players as $key => $row) {
        $id_joueur = $row[0];
        $nom = utf8_encode($row[1]);
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
        if ($hp == 0) {
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

        if ($str < $mind) {
          $aptitude = "malin";
        }
        if ($str > $mind) {
          $aptitude = "fort";
        }
        if ($str == $mind) {
          $aptitude = "malin et fort";
        }

        print "
            <div class=\"$alive\">
              <b>$nom</b>
              <div class='tags'>
                <span class='genre-$hf'>$genre</span>
                $str_tags
              </div>";

        if ($hp > 0) {
          print "<div class='stats'>
                <span>$aptitude</span>
                <span>Esprit : $mind</span>
                <span>Corps : $str</span>
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
      <div id="elections">
        <b>Nominations :</b>
        <a href="ecran.php?action=leader">Nommer un nouveau leader</a>
        <a href="ecran.php?action=traitre">Nommer un nouveau traitre</a>
        <a href="ecran.php?action=random">Designer quelqu'un au hasard</a>
        <a href="ecran.php?action=randomm">Designer un malin au hasard</a>
        <a href="ecran.php?action=randoms">Designer un fort au hasard</a>
        <form method=post action=ecran.php>
          Designer un tag au hasard :
          <input type="text" name="choixrandom" size="15">
          <input type="submit" value="OK">
        </form>
        <span><b>Le joueur désigné est :</b> <?php print "$designe"; ?></span>
      </div>
      <div id="poll">
        <h3>Sondage</h3>
        <?php
        $stmt = $db->prepare("SELECT choix FROM sondage");
        $stmt->execute();
        $row = $stmt->fetch();
        $choix = $row[0];

        if ($choix != "") {
          print "$choix<table id='poll_results'><tr><td>En attente des votes.</td></tr></table>";
          print "<a href=ecran.php?action=clean>Terminer le sondage</a>";
        }
        else {
          ?>
          <form method=post action=ecran.php#poll>
            <input type="text" name="choix" size="30" placeholder="Intitulé des choix">
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
            Limiter à : <input type="text" name="choixtag" size="15">
            <input type="submit" value="DÉLIBERER">
          </form>
          <?php
        }
        ?>
      </div>
      <div id="epreuve">
        <h3>Épreuves</h3>
        <form method=post action=ecran.php#epreuve>
          <label for="type">Type</label>
          <select name="type">
            <option value="mind">Esprit</option>
            <option value="str">Corps</option>
          </select>
          <label for="difficulte">Difficulté</label><input type="number" name="difficulte" size="10">
          <label for="penalite">Pénalité</label>
          <span>
            <input type="number" name="penalite" size="10">
            <select name="penalite_type">
              <option value="mind">Esprit</option>
              <option value="str">Corps</option>
              <option value="hp">Santé</option>
            </select>
          </span>
          <label for="victime">Qui</label><input type="text" name="victime" size="10">
          <label for="victimetag">Tag</label><input type="text" name="victimetag" size="10">
          <input type="submit" value="EPROUVER">
        </form>
        <div><?php print $sanction; ?></div>
      </div>
      <div id="loot">
        <form method=post action=ecran.php>
          <h3>Loot</h3>
          <label for="loot">Quoi</label><input type="text" name="loot" size="30">
          <label for="propriete">Effet</label><input type="text" name="propriete" size="10">
          <label for="qui">À qui</label>
          <select type="text" name="qui">
            <option value=*>Tout le monde</option>
            <?php
            foreach($list_players as $key_player => $player) {
              print "<option value=$key_player>$player</option>";
            }
            ?>
          </select>
          <label for="qui_multiple">Avancé (séparez des ID joueurs par des virgules)</label><input type="text" name="qui_multiple" size="10">
          <input type="submit" value="DONNER">
        </form>
      </div>
      <div id="tags">
        <h3>Tags</h3>
        <input type="text" name="tag1" size="30">
        <input type="text" name="tag2" size="30">
        <input type="text" name="tag3" size="30">
        <input type="submit" value="DONNER">
        </form>
      </div>
      <div id="settings">
        <h3>Paramètres</h3>
        <form method=post action=ecran.php>
          <label for="adventure_name">Nom de l'aventure</label>
          <input type="text" name="adventure_name" size="10">
          <label for="adventure_guide">Adresse ip ou url pour rejoindre</label>
          <input type="text" name="adventure_guide" size="10">
          <input type="submit" value="Enregistrer">
        </form>
      </div>
    </div>
  </div>
  <div class="wrapper-right">
    <div id="tabs">
      <div data-target="elections">Nommer / désigner</div>
      <div data-target="poll">Déclenchez un sondage</div>
      <div data-target="epreuve">Déclencher une épreuve</div>
      <div data-target="loot">Attribuer du loot</div>
      <div data-target="tags">Attribuer des tags</div>
      <div data-target="settings">Paramétrer l'aventure</div>
    </div>
  </div>
  <?php
  }
  ?>
  <?php include "footer.php"; ?>
</html>

