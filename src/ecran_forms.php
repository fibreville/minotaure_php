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
    SELECT hrpg.id,nom,carac2,carac1,hp,GROUP_CONCAT(t.name) tags,active
    FROM hrpg
    LEFT JOIN character_tag ct ON ct.id_player = hrpg.id
    LEFT JOIN tag t ON ct.id_tag = t.id
    WHERE hrpg.id > 1
    GROUP BY hrpg.id
    ORDER BY active DESC, hp <= 0 ASC, nom ASC");
$query->execute();
$players = $query->fetchAll(PDO::FETCH_ASSOC);
$nb_alive = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp > 0 AND id > 1")->fetchColumn();
$settings = $_SESSION['settings'];
?>

<script src="js/ajax_mj.js"></script>
<div class="wrapper-intro">
  <div class="intro">
    <h2><?php print $settings['adventure_name']; ?></h2>
    <?php print $settings['adventure_guide']; ?>
  </div>
  <div id="group-stats">
    <span><?php print $settings['role_leader']; ?> : <b class="pj-name"><?php print "$leader"; ?></b></span>
    <span><?php print $settings['role_traitre']; ?> : <b class="pj-name"><?php print "$traitre"; ?></b></span>
    <span>💛 Personnages encore en vie : <b><?php print $nb_alive . ' / ' . count($players); ?></b></span>
  </div>
</div>
<div class="wrapper-main">
  <div id="tabs">
    <div class="first-item" data-target="group" class="active">🧍 Groupe</div>
    <div data-target="elections">👑 Rôle</div>
    <div data-target="target">🎲 Tirage</div>
    <div data-target="poll">🗳️ Sondage</div>
    <div data-target="epreuve">🤹 Épreuve</div>
    <div data-target="loot">🎁 Loot</div>
    <div data-target="tags">🏷️ Tags</div>
    <div data-target="settings">⚙️ Paramètres</div>
    <div class="debug" data-target="debug">Debug</div>
    <div class="last-item"><a class='no-color' href="ecran.php">🔃 Recharger</a></div>
  </div>
  <div id="choices">
    <div id="elections" class="active">
      <h3>Nommer des personnages clefs.</h3>
      <form method="post" action="ecran.php?action=election">
        <button name="name" value="leader" type="submit">Nommer <?php print $settings['role_leader']; ?></button>
        <button name="name" value="traitre" type="submit">Nommer <?php print $settings['role_traitre']; ?></button>
      </form>
    </div>
    <!-- FORMULAIRE DESIGNATION -->
    <div id="target" class="active">
      <h3>Désigner un personnage pour votre histoire (par exemple avant une épreuve).</h3>
      <form method="post" action="ecran.php?action=target#target">
        <span class="wrapper-penalite">
          <label for="limit">Combien ?</label>
          <input type="number" id="limit" name="limit" value="1" min="1" max="999999999">
        </span>
        <span class="wrapper-penalite">
          <label for="random_choice">Parmi un groupe</label>
          <select id="random_choice" name="random_choice">
            <option value="random">Tout le monde</option>
            <option value="random_carac1"><?php print $settings['carac1_group']; ?></option>
            <option value="random_carac2"><?php print $settings['carac2_group']; ?></option>
          </select>
        </span>
        <span class="wrapper-between">
          ET
        </span>
        <span class="wrapper-penalite">
          <label for="random_tag" >Ayant au moins un des tags :</label>
          <input class="tag-whitelist" type="text" id="random_tag" name="random_tag" placeholder="nomdutag" maxlength="250">
        </span>
        <input type="submit" value="Tirer au sort">
      </form>
    </div>
    <div id="poll">
      <h3>Sonder le groupe pour connaitre la décision de la majorité.</h3>
      <?php
      $query = $db->prepare("SELECT choix FROM sondage");
      $query->execute();
      $row = $query->fetch();
      $choix = $row[0];

      if ($choix != "") {
        print "<span class='poll-label'>$choix</span>";
        print "<div id='poll_results'><table><tr><td>En attente des votes.</td></tr></table></div>";
        print "<a class='submit-button' href='ecran.php?action=clean'>Terminer le sondage</a>";
      }
      else {
        ?>
        <!-- FORMULAIRE DE SONDAGE-->
        <form method="post" action="ecran.php?action=poll#poll">
          <fieldset class="poll-choices">
            <legend>Choix</legend>
            <input autocomplete="off" class="poll-label" required type="text" name="choix" maxlength="250" placeholder="Intitulé du sondage">
            <input autocomplete="off" placeholder="Choix 1" type="text" name="c1" maxlength="250">
            <input autocomplete="off" placeholder="Choix 2" class="last-visible" type="text" name="c2" maxlength="250">
            <input autocomplete="off" placeholder="Choix 3" type="text" name="c3" maxlength="250">
            <input autocomplete="off" placeholder="Choix 4" type="text" name="c4" maxlength="250">
            <input autocomplete="off" placeholder="Choix 5" type="text" name="c5" maxlength="250">
            <input autocomplete="off" placeholder="Choix 6" type="text" name="c6" maxlength="250">
            <input autocomplete="off" placeholder="Choix 7" type="text" name="c7" maxlength="250">
            <input autocomplete="off" placeholder="Choix 8" type="text" name="c8" maxlength="250">
            <input autocomplete="off" placeholder="Choix 9" type="text" name="c9" maxlength="250">
            <input autocomplete="off" placeholder="Choix 10" type="text" name="c10" maxlength="250">
            <div class="poll-plus">+</div>
          </fieldset>
          <fieldset class="poll-limit">
            <legend>Limiter à</legend>
            <input class="tag-whitelist" type="text" name="choixtag" id="choixtag" maxlength="250" placeholder="Entrez un tag">
          </fieldset>
          <input type="submit" value="Délibérer">
        </form>
        <?php
      }
      ?>
    </div>

    <div id="epreuve">
      <h3>Faire passer un test à des personnages.</h3>
      <!-- FORMULAIRE DES EPREUVES-->
      <form method="post" action="ecran.php?action=epreuve">
        <fieldset>
          <legend>Test</legend>
          <span class="wrapper-penalite">
            <label for="type">Type</label>
            <select name="type" id="type">
              <option value="carac1"><?php print $settings['carac1_name'] ?></option>
              <option value="carac2"><?php print $settings['carac2_name'] ?></option>
            </select>
          </span>
        <span class="wrapper-penalite">
            <label for="difficulte">Difficulté</label>
            <select name="difficulte" id="difficulte">
              <option value="-8">Trivial (-8)</option>
              <option value="-6">Aisé (-6)</option>
              <option value="-4">Facile (-4)</option>
              <option value="-2">Assez facile (-2)</option>
              <option value="0" selected>Normal (0)</option>
              <option value="2">Assez difficile (+2)</option>
              <option value="4">Difficile (+4)</option>
              <option value="6">Ardu (+6)</option>
              <option value="8">Cauchemardesque (+8)</option>
            </select>
          </span>
        </fieldset>
        <fieldset>
          <legend>Conséquences</legend>
          <span class="wrapper-penalite">
            <label for="penalite">En cas d'échec (-)</label>
            <select name="penalite_type" id="penalite">
              <option value="hp">Santé</option>
              <option value="carac1"><?php print $settings['carac1_name'] ?></option>
              <option value="carac2"><?php print $settings['carac2_name'] ?></option>
            </select>
            <input type="number" name="penalite" value="0" min="0" max="999999999">
          </span>
          <span class="wrapper-penalite">
            <label for="reward_type">En cas de réussite (+)</label>
            <select name="reward_type" id="reward_type">
              <option value="hp">Santé</option>
              <option value="carac1"><?php print $settings['carac1_name'] ?></option>
              <option value="carac2"><?php print $settings['carac2_name'] ?></option>
            </select>
            <input type="number" name="reward" value="0" min="0" max="999999999">
          </span>
        </fieldset>
        <fieldset>
          <legend>Qui ?</legend>
          <span class="wrapper-penalite">
            <label for="victime">Par groupe de personnages</label>
            <select class='pj-name' name="victime" id="victime">
              <option value="all">Tout le monde</option>
              <option value="carac1">Chaque personnage <?php print $settings['carac1_group'] ?></option>
              <option value="carac2">Chaque personnage <?php print $settings['carac2_group'] ?></option>
            </select>
          </span>
          <span class="wrapper-penalite">
            <label for="victime_multiple"><strong>Ou</strong> par personnage</label>
            <input class="player-whitelist" placeholder="nom du personnage" type="text" name="victime_multiple" id="victime_multiple" maxlength="250">
          </span>
          <span class="wrapper-penalite">
            <label for="victimetag"><strong>Ou</strong> par Tag</label>
            <input class="tag-whitelist" type="text" name="victimetag" placeholder="Entrez un tag"  id="victimetag" maxlength="250">
          </span>
        </fieldset>
        <input type="submit" value="ÉPROUVER">
      </form>
    </div>

    <div id="loot">
      <h3>Attribuer un objet à des personnages.</h3>
      <!-- FORMULAIRE DU LOOT-->
      <form method="post" action="ecran.php?action=loot">
        <fieldset>
          <legend>Quoi</legend>
          <span class="wrapper-penalite">
            <label for="loot_input">Nom de l'objet</label>
            <input required type="text" name="loot" id="loot_input" maxlength="250">
          </span>
          <span class="wrapper-penalite">
            <label for="propriete">Effet</label>
            <select name="propriete" id="propriete">
              <option value="" selected>Choisir</option>
              <option value="hp">💛 Vie</option>
              <option value="carac1"><?php print $settings['carac1_name']; ?></option>
              <option value="carac2"><?php print $settings['carac2_name']; ?></option>
            </select>
            <input type="number" value="0" name="bonus" placeholder="bonus" min="-999999999" max="999999999">
          </span>
        </fieldset>
        <fieldset>
          <legend>À qui (groupe)</legend>
          <span class="wrapper-penalite">
            <label for="qui">Un groupe de personnage</label>
            <select name="qui" id="qui">
              <option value="all">Tout le monde</option>
              <option value="carac1">Chaque personnage <?php print $settings['carac1_group'] ?></option>
              <option value="carac2">Chaque personnage <?php print $settings['carac2_group'] ?></option>
              <?php
              foreach ($list_players as $key_player => $player) {
                print "<option value='$key_player'>$player</option>";
              }
              ?>
            </select>
            <label for="qui_tags"><strong>ayant</strong> au moins un des tags</label>
            <input class="tag-whitelist" type="text" name="qui_tags" placeholder="Entrez un tag"  id="qui_tags" maxlength="250">
          </span>
        </fieldset>
        <fieldset>
          <legend>À qui (individus)</legend>
          <span class="wrapper-penalite">
            <label for="qui_multiple"><strong>Ou</strong> des personnages spécifiques</label>
            <input type="text" class="player-whitelist" name="qui_multiple" id="qui_multiple" placeholder="Entrez un nom">
          </span>
        </fieldset>
        <input type="submit" value="Donner">
      </form>
    </div>

    <div id="tags">
      <!-- FORMULAIRE DES TAGS-->
      <h3>Attribuer aléatoirement des étiquettes à la population.</h3>
      <form method="post" action="ecran.php?action=tags">
        <span><label for="tag1">Catégorie 1</label><a href="ecran.php?action=delete_tags&category=1">Supprimer</a></span>
        <input type="text" id="tag1" name="tag1" maxlength="250" placeholder="Entrez des mots-clefs">
        <span><label for="tag2">Catégorie 2</label><a href="ecran.php?action=delete_tags&category=2">Supprimer</a></span>
        <input type="text" id="tag2" name="tag2" maxlength="250" placeholder="Entrez des mots-clefs">
        <span><label for="tag3">Catégorie 3</label><a href="ecran.php?action=delete_tags&category=3">Supprimer</a></span>
        <input type="text" id="tag3" name="tag3" maxlength="250" placeholder="Entrez des mots-clefs">
        <input type="submit" value="Attribuer">
      </form>
    </div>
    <div id="settings">
      <!-- FORMULAIRE DES PARAMETRES DE LA PARTIE-->
      <h3>Changer les réglages de votre aventure.</h3>
      <form method="post" action="ecran.php?action=settings">
        <fieldset>
          <legend>Intro</legend>
          <label for="adventure_name">Nom de l'aventure</label>
          <input type="text" name="adventure_name" id="adventure_name" maxlength="250" value="<?php print $settings['adventure_name']; ?>">
          <label for="adventure_guide">Adresse ip ou url pour rejoindre</label>
          <textarea type="textarea" name="adventure_guide" size=5 id="adventure_guide" maxlength="250"><?php print $settings['adventure_guide']; ?></textarea>
          <label for="same_stats_all">Mêmes stats pour tout le monde</label>
          <input type="checkbox" name="same_stats_all" id="same_stats_all" <?php print ($settings['same_stats_all'] ? 'checked' : ''); ?>>
          <label for="random_tags">Tags distribués aléatoirement</label>
          <input type="checkbox" name="random_tags" id="random_tags" <?php print ($settings['random_tags'] ? 'checked' : ''); ?>>
        </fieldset>
        <fieldset>
          <legend>1ère caractéristique</legend>
          <label for="carac1_name">Nom</label>
          <input type="text" placeholder="esprit" name="carac1_name" id="carac1_name" value="<?php print $settings['carac1_name']; ?>">
          <label for="carac1_group">Un personnage fort dans cette carac est :</label>
          <input type="text" placeholder="malin" name="carac1_group" id="carac1_group" value="<?php print $settings['carac1_group']; ?>">
        </fieldset>
        <fieldset>
          <legend>2ème caractéristique</legend>
          <label for="carac2_name">Nom</label>
          <input type="text" placeholder="corps" name="carac2_name" id="carac2_name" value="<?php print $settings['carac2_name']; ?>">
          <label for="carac2_group">Un personnage fort dans cette carac est :</label>
          <input type="text" placeholder="fort" name="carac2_group" id="carac2_group" value="<?php print $settings['carac2_group']; ?>">
        </fieldset>
        <fieldset>
          <legend>Rôles</legend>
          <label for="role_leader">Nom de rôle de leader</label>
          <input type="text" name="role_leader" id="role_leader" maxlength="250" value="<?php print $settings['role_leader']; ?>">
          <label for="role_traitre">Nom de rôle de traître</label>
          <input type="text" name="role_traitre" id="role_traitre" maxlength="250" value="<?php print $settings['role_traitre']; ?>">
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
  <?php print $_SESSION['sanction']; ?>
  <div id="group">
    <?php
    $available_colors = ['#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'];
    $taken_colors = [];

    $list_players = [];
    $list_players_for_js = [];
    foreach ($players as $key => $row) {
      $id_joueur = $row['id'];
      $nom = $row['nom'];
      $carac2 = $row['carac2'];
      $carac1 = $row['carac1'];
      $hp = $row['hp'];
      $tags = explode(',', $row['tags']);
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
      $alive .= ($row['active'] == 0) ? ' inactive' : '';
      if ($hp <= 0) {
        $list_players[$id_joueur] = $nom . ' (☠️)';
        $list_players_for_js[] = ['value' => $nom . ' (☠️)', 'code' => $id_joueur];
      }
      else {
        $list_players[$id_joueur] = $nom;
        $list_players_for_js[] = ['value' => $nom, 'code' => $id_joueur];
      }

      print '<script>tags_players = ' . json_encode($list_players_for_js) . '</script>';

      $aptitude = '';
      if ($carac2 > 14 && $carac1 > 14) {
        $aptitude = $settings['carac1_group'] . ' et ' . $settings['carac2_group'];
      }
      elseif ($carac1 > 14) {
        $aptitude = $settings['carac1_group'];
      }
      elseif ($carac2 > 14) {
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
            <span class='life'>Vie: $hp</span>
            </div>";
      }
      print '</div>';
    }
    ?>
  </div>
</div>