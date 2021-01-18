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

  $stmt = $db->query("SELECT nom, id, hp FROM hrpg WHERE traitre = 1");
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
      <span>👑 Leader du groupe : <b class="pj-name"><?php print "$leader"; ?></b></span>
      <span>🗡️ Traître du groupe : <b class="pj-name"><?php print "$traitre"; ?></b></span>
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
        <?php if (!empty($_SESSION['designe'])) { ?>
          <div><b>Le joueur désigné est :</b>
            <div><?php print $_SESSION['designe']; ?></div>
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
              <option value="*">⭐ Tout le monde</option>
              <option value="carac1">Chaque personnage <?php print $settings['carac1_group'] ?></option>
              <option value="carac2">Chaque personnage <?php print $settings['carac2_group'] ?></option>
              <?php
              foreach ($list_players as $key_player => $player) {
                print '<option value="'.$key_player.'">'.ucfirst($player).'</option>';
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
        <div class="epreuve-cr"><?php print $_SESSION['sanction']; ?></div>
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