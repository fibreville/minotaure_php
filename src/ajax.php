<?php
session_start();
include "connexion.php";

if ($_GET['role'] == 'mj' && $_SESSION['id'] == 1) {
  $stmt = $db->prepare("SELECT nom,leader,vote FROM hrpg WHERE leader>0 AND hp>0");
  $stmt->execute();
  $row = $stmt->fetch();
  $leader = $row[0];
  $leadvalue = $row[1];
  $leadvote = $row[2];

  $stmt = $db->prepare("SELECT nom,traitre,vote FROM hrpg WHERE traitre>0 AND hp > 0 AND id > 1");
  $stmt->execute();
  $row = $stmt->fetch();
  $traitre = $row[0];
  $traitrevalue = $row[1];
  $traitrevote = $row[2];

  $stmt = $db->prepare("SELECT * FROM sondage");
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $options = array_slice($row, 1, 10, TRUE);
  $choix = $row['choix'];
  $choixtag = $row['choixtag'];

  $stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM hrpg 
    WHERE hp > 0 
    AND id > 1 
    AND (tag1 = :choixtag OR tag2 = :choixtag OR tag3 = :choixtag)
  ");
  $stmt->execute([':choixtag' => $choixtag]);
  $nb_total = $stmt->fetchColumn();

  print "<table>";
  $pctot = 0;
  $query = $db->prepare("
    SELECT vote, COUNT(id) c 
    FROM hrpg 
    WHERE vote > 0
    GROUP BY vote 
    ORDER BY c DESC");
  $query->execute([':choixtag' => $choixtag]);
  $votes = $query->fetchAll(PDO::FETCH_ASSOC);
  $max_vote = $votes[0]['c'];
  $pctot = 0;

  foreach ($votes as $key => $vote) {
    $nb_votants = $vote['c'];
    $pc = round(($nb_votants * 100 / $nb_total), 2);
    $pctot += $pc;
    $tmp_result = '';
    if ($nb_votants == $max_vote) {
      print '<tr style="color: green">';
    }
    else {
      print '<tr>';
    }
    print "<td>" . $options['c' . $vote['vote']] . " : </td><td>$nb_votants / $nb_total soit $pc %</td></tr>";
    unset($options['c' . $vote['vote']]);
  }
  foreach ($options as $option) {
    if ($option != '') {
      print "<td>" . $option . " : </td><td>aucun vote</td></tr>";
    }
  }
  print "</table>";
  print "<div>Total votants : $pctot %</div>";
  if ($choixtag != "") {
    print "(vote limité à $choixtag)";
  }
  if ($leadvalue == 2) {
    print "<b>Le leader $leader a utilisé son pouvoir et choisi le choix $leadvote !</b>";
  }
  if ($traitrevalue == 2) {
    print "<b>Le traitre $traitre a utilisé son pouvoir et choisi d'annuler le choix $traitrevote !</b>";
  }
}
elseif ($_GET['role'] == 'pj') {
  $id = $_SESSION['id'];
  $stmt = $db->prepare("SELECT * FROM hrpg WHERE id=:id");
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $id = $row['id'];
  $nom = $row['nom'];
  $hf = $row['hf'];
  $str = $row['str'];
  $mind = $row['mind'];
  $hp = $row['hp'];
  $leader = $row['leader'];
  $vote = $row['vote'];
  $tags = [$row['tag1'], $row['tag2'], $row['tag3']];
  $traitre = $row['traitre'];
  ?>
  <div>
  <h2>Votre aventurier</h2>
  <?php
  $genre = "Homme";
  $dead_name = 'mort';
  if ($hf == 1) {
    $genre = "Femme";
    $dead_name = 'morte';
  }
  if ($hf == 3) {
    $genre = "Non Binaire";
    $dead_name = 'mort.e';
  }
  if ($hp > 0) { ?>
    <div class="character">
      <div><?php print $nom; ?></div>
      <div class="character-tags">
        <div><?php print $genre; ?></div>
        <?php
        foreach ($tags as $tag) {
          if ($tag != "" && $tag != " ") {
            print '<div>' . $tag . '</div>';
          }
        }
        ?>
      </div>
      <div class="character-stats">
        <div>💪 Force : <b><?php print $str; ?></b></div>
        <div>🧠 Esprit : <b><?php print $mind; ?></b></div>
        <div>💛 Points de vie : <b><?php print $hp; ?></b></div>
      </div>
      <?php if ($leader == 1) { ?>
        <div>Vous êtes actuellement <b>Leader</b> 👑 !</div>
      <?php } ?>
      <?php if ($traitre == 1) { ?>
        <div>Vous êtes actuellement <b>Traitre</b> 🗡️!</div>
      <?php } ?>
    </div>
    <?php
    $stmt = $db->prepare("SELECT * FROM sondage");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $choix = $row['choix'];
    $cX = array_splice($row, 1, 10);
    $choixtag = $row['choixtag'];
    ?>
    <div class="poll-choice">
      <?php
      if ($choix != "" && $vote == 0 && ($choixtag == "" || in_array($choixtag, $tags))) {
      ?>
      <div>Décision en cours : <b><?php print $choix; ?></b></div>
      <form action=main.php method=post>
        <?php
        $key = 1;
        foreach ($cX as $c) {
          if ($c != "") {
            print "<div><input type=\"radio\" name=\"choix\" value=\"$key\"><label>" . $c . "</label></div>";
          }
          $key++;
        }
        if ($leader == 1) {
          print "<input type=checkbox name=lead value=1><label for=lead>Utiliser mon pouvoir de leader</label>";
        }

        if ($traitre == 1) {
          print "<input type=checkbox name=traitre value=1><label for=traitre>Utiliser mon pouvoir de traitre et annuler le vote choisi<label>";
        }
        print '<input type="submit" value="Votre choix est irrévocable"></form>';
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
          print "<br>- $quoi";
        }
      }
      ?>
    </div>
    <?php
    }
  else {
  ?>
    <div class="wakeup">☠</div>
    <div><?php print "$nom est $dead_name"; ?></div>
  <?php
  }
}