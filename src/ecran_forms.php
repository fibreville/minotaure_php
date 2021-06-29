<?php
$stmt = $db->prepare("SELECT nom, id, hp FROM hrpg WHERE leader = 1");
$stmt->execute([]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (empty($row)) {
  $leader = _('aucun');
  $id_leader = 0;
}
elseif ($row['hp'] <= 0) {
  $leader = sprintf(_('mort (%s)'), $row['nom']);
  $id_leader = 0;
}
else {
  $leader = $row['nom'];
  $id_leader = $row['id'];
}

$stmt = $db->query("SELECT nom, id, hp FROM hrpg WHERE traitre = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (empty($row)) {
  $traitre = _('aucun');
  $id_traitre = 0;
}
elseif ($row['hp'] <= 0) {
  $traitre = sprintf(_('mort (%s)'), $row['nom']);
  $id_traitre = 0;
}
else {
  $traitre = $row['nom'];
  $id_traitre = $row['id'];
}

$query = $db->prepare("
    SELECT hrpg.id,nom,carac3,carac2,carac1,hp,wp,GROUP_CONCAT(t.name) tags,active
    FROM hrpg
    LEFT JOIN character_tag ct ON ct.id_player = hrpg.id
    LEFT JOIN tag t ON ct.id_tag = t.id
    WHERE hrpg.id > 1
    GROUP BY hrpg.id
    ORDER BY active DESC, hp <= 0 ASC, nom ASC");
$query->execute();
$players = $query->fetchAll(PDO::FETCH_ASSOC);
$nb_alive = $db->query("SELECT COUNT(*) FROM hrpg WHERE hp > 0 AND wp > 0 AND id > 1")->fetchColumn();
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
    <span><?php print _('Personnages encore en jeu : '); ?><b><?php print $nb_alive . ' / ' . count($players); ?></b></span>
  </div>
</div>
<div class="wrapper-main">
  <div id="tabs">
    <div class="first-item" data-target="group" class="active">🧍 <?php print _('Groupe'); ?></div>
    <div data-target="elections">👑 <?php print _("Rôle"); ?></div>
    <div data-target="target">🎲 <?php print _("Tirage"); ?></div>
    <div data-target="poll">🗳️ <?php print _("Sondage"); ?></div>
    <div data-target="epreuve">🤹 <?php print _("Épreuve"); ?></div>
    <div data-target="loot">🎁 <?php print _("Loot"); ?></div>
    <div data-target="tags">🏷️ <?php print _("Tags"); ?></div>
    <div data-target="settings">⚙️ <?php print _("Paramètres"); ?></div>
    <div class="debug" data-target="debug"><?php print _("Debug"); ?></div>
    <div class="last-item"><a class='no-color' href="ecran.php">🔃 <?php print _("Recharger"); ?></a></div>
  </div>
  <div id="choices">
    <div id="elections" class="active">
      <h3><?php print _("Nommer des personnages clefs."); ?></h3>
      <form method="post" action="ecran.php?action=election">
        <button name="name" value="leader" type="submit"><?php print sprintf(_("Nommer %s"), $settings['role_leader']); ?></button>
        <button name="name" value="traitre" type="submit"><?php print sprintf(_("Nommer %s"), $settings['role_traitre']); ?></button>
      </form>
      <form method="post" action="ecran.php?action=destitution" style="margin-top: 10px">
        <button name="name" value="leader" type="submit"><?php print sprintf(_("Destituer %s"), $settings['role_leader']); ?></button>
        <button name="name" value="traitre" type="submit"><?php print sprintf(_("Destituer %s"), $settings['role_traitre']); ?></button>
      </form>
    </div>
    <!-- FORMULAIRE DESIGNATION -->
    <div id="target" class="active">
      <h3><?php print _("Désigner un personnage pour votre histoire (par exemple avant une épreuve)."); ?></h3>
      <form method="post" action="ecran.php?action=target#target">
        <span class="wrapper-penalite">
          <label for="limit"><?php print _("Combien ?"); ?></label>
          <input type="number" id="limit" name="limit" value="1" min="1" max="999999999">
        </span>
        <span class="wrapper-penalite">
          <label for="random_choice"><?php print _("Parmi un groupe"); ?></label>
          <select id="random_choice" name="random_choice">
            <option value="random"><?php print _("Tout le monde"); ?></option>
            <option value="random_carac1"><?php print ucfirst($settings['carac1_group']); ?></option>
            <option value="random_carac2"><?php print ucfirst($settings['carac2_group']); ?></option>
            <?php if ($settings['carac3_name'] != "") {
              print "<option value=\"random_carac3\">" . ucfirst($settings['carac3_group']) . "</option>";
            } ?>
          </select>
        </span>
        <span class="wrapper-between">
          ET
        </span>
        <span class="wrapper-penalite">
          <label for="random_tag" ><?php print _("Ayant au moins un des tags :"); ?></label>
          <input class="tag-whitelist" type="text" id="random_tag" name="random_tag" placeholder="nomdutag" maxlength="250">
        </span>
        <input type="submit" value="<?php print _("Tirer au sort"); ?>">
        <div class="warning">⚠️ <?php print _("Aucun personnage remplissant ces critères n'a été trouvé."); ?></div>
      </form>
    </div>
    <div id="poll">
      <h3><?php print _("Sonder le groupe pour connaitre la décision de la majorité."); ?></h3>
      <?php
      $query = $db->prepare("SELECT choix FROM sondage");
      $query->execute();
      $row = $query->fetch();
      $choix = $row[0];

      if ($choix != "") {
        print "<span class='poll-label'>$choix</span>";
        print "<div id='poll_results'><table><tr><td>" . _("En attente des votes . ") . "</td></tr></table></div>";
        print "<a class='submit-button' href='ecran.php?action=clean'>" . _("Terminer le sondage") . "</a>";
      }
      else {
        ?>
        <!-- FORMULAIRE DE SONDAGE-->
        <form method="post" action="ecran.php?action=poll#poll">
          <fieldset class="poll-choices">
            <legend><?php print _("Choix"); ?></legend>
            <input autocomplete="off" class="poll-label" required type="text" name="choix" maxlength="250" placeholder="<?php print _("Intitulé du sondage"); ?>">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '1'); ?>" type="text" name="c1" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '2'); ?>" class="last-visible" type="text" name="c2" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '3'); ?>" type="text" name="c3" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '4'); ?>" type="text" name="c4" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '5'); ?>" type="text" name="c5" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '6'); ?>" type="text" name="c6" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '7'); ?>" type="text" name="c7" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '8'); ?>" type="text" name="c8" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '9'); ?>" type="text" name="c9" maxlength="250">
            <input autocomplete="off" placeholder="<?php print sprintf(_("Choix %s"), '10'); ?>" type="text" name="c10" maxlength="250">
            <div class="poll-plus">+</div>
          </fieldset>
          <fieldset class="poll-limit">
            <legend><?php print _("Limiter à"); ?></legend>
            <input class="tag-whitelist" type="text" name="choixtag" id="choixtag" maxlength="250" placeholder="<?php print _("Entrez un tag"); ?>">
          </fieldset>
          <input type="submit" value="<?php print _("Délibérer"); ?>">
          <?php if (isset($_SESSION['last_vote'])): ?>
            <fieldset>
              <legend><?php print _('Rappel du dernier sondage'); ?></legend>
              <?php print $_SESSION['last_vote']; ?>
            </fieldset>
          <?php endif ?>
        </form>
        <?php
      }
      ?>
    </div>

    <div id="epreuve">
      <h3><?php print _('Faire passer un test à des personnages.'); ?></h3>
      <!-- FORMULAIRE DES EPREUVES-->
      <form method="post" action="ecran.php?action=epreuve">
        <fieldset>
          <legend>Test</legend>
          <span class="wrapper-penalite">
            <label for="type"><?php print _('Caractéristique testée.'); ?></label>
            <select name="type" id="type">
              <option value="carac1"><?php print ucfirst($settings['carac1_name']) ?></option>
              <option value="carac2"><?php print ucfirst($settings['carac2_name']) ?></option>
              <?php if ($settings['carac3_name'] != "") {
                print "<option value=\"carac3\">" . ucfirst($settings['carac3_name']) . "</option>";
              } ?>
            </select>
          </span>
          <span class="wrapper-penalite">
            <label for="difficulte"><?php print _('Difficulté'); ?></label>
            <select name="difficulte" id="difficulte">
              <option value="-8"><?php print _('Trivial (-8)'); ?></option>
              <option value="-6"><?php print _('Aisé (-6)'); ?></option>
              <option value="-4"><?php print _('Facile (-4)'); ?></option>
              <option value="-2"><?php print _('Assez facile (-2)'); ?></option>
              <option value="0" selected><?php print _('Normal (0)'); ?></option>
              <option value="2"><?php print _('Assez difficile (+2)'); ?></option>
              <option value="4"><?php print _('Difficile (+4)'); ?></option>
              <option value="6"><?php print _('Ardu (+6)'); ?></option>
              <option value="8"><?php print _('Cauchemardesque (+8)'); ?></option>
            </select>
          </span>
        </fieldset>
        <fieldset>
          <legend><?php print _("Conséquences"); ?></legend>
          <span class="wrapper-penalite">
            <label for="penalite"><?php print _("En cas d'échec (-)"); ?></label>
            <select name="penalite_type" id="penalite">
              <option value="hp">💛 <?php print _("Santé"); ?></option>
              <?php if ($settings['willpower_on']) {
                print "<option value=\"wp\">🌟" . _("Volonté") . "</option>";
              } ?>
              <option value="carac1"><?php print ucfirst($settings['carac1_name']) ?></option>
              <option value="carac2"><?php print ucfirst($settings['carac2_name']) ?></option>
              <?php if ($settings['carac3_name'] != "") {
                print "<option value=\"carac3\">" . ucfirst($settings['carac3_name']) . "</option>";
              } ?>
            </select>
            <input type="number" name="penalite" value="0" min="0" max="999999999">
          </span>
          <span class="wrapper-penalite">
            <label for="reward_type"><?php print _("En cas de réussite (+)"); ?></label>
            <select name="reward_type" id="reward_type">
              <option value="hp">💛 <?php print _("Santé"); ?></option>
              <?php if ($settings['willpower_on']) {
                print "<option value=\"wp\">🌟" . _("Volonté") . "</option>";
              } ?>
              <option value="carac1"><?php print ucfirst($settings['carac1_name']) ?></option>
              <option value="carac2"><?php print ucfirst($settings['carac2_name']) ?></option>
              <?php if ($settings['carac3_name'] != "") {
                print "<option value=\"carac3\">" . ucfirst($settings['carac3_name']) . "</option>";
              } ?>
            </select>
            <input type="number" name="reward" value="0" min="0" max="999999999">
          </span>
        </fieldset>
        <fieldset>
          <legend><?php print _("Qui ?"); ?></legend>
          <div class="wrapper-penalite">
            <label for="victime"><?php print _("Par groupe de personnages"); ?></label>
            <select class='pj-name' name="victime" id="victime">
              <option value="all"><?php print _("Tout le monde"); ?></option>
              <option value="carac1"><?php print sprintf(_("Chaque personnage %s"), $settings['carac1_group']); ?></option>
              <option value="carac2"><?php print sprintf(_("Chaque personnage %s"), $settings['carac2_group']); ?></option>
              <?php if ($settings['carac3_name'] != "") {
                print "<option value=\"carac3\">" . sprintf(_("Chaque personnage %s"), $settings['carac3_group'])  . "</option>";
              } ?>
            </select>
          </div>
          <div class="wrapper-penalite">
            <label for="victime_multiple"><?php print _("<strong>Ou</strong> par personnage"); ?></label>
            <input class="player-whitelist" placeholder="<?php print _("nom du personnage"); ?>" type="text" name="victime_multiple" id="victime_multiple" maxlength="250">
          </div>
          <div class="wrapper-penalite">
            <label for="victimetag"><?php print _("<strong>Ou</strong> par Tag"); ?></label>
            <input class="tag-whitelist" type="text" name="victimetag" placeholder="<?php print _("Entrez un tag"); ?>"  id="victimetag" maxlength="250">
          </div>
          <div>
            <input type="checkbox" name="restrict_active" id="restrict_active" <?php print ($settings['restrict_active'] ? 'checked' : ''); ?>>
            <label for="restrict_active"><?php print _("Limiter aux personnages actifs"); ?></label>
          </div>
        </fieldset>
        <input type="submit" value="<?php print _("ÉPROUVER"); ?>">
      </form>
    </div>

    <div id="loot">
      <h3><?php print _("Attribuer un objet à des personnages."); ?></h3>
      <!-- FORMULAIRE DU LOOT-->
      <form method="post" action="ecran.php?action=loot">
        <fieldset>
          <legend><?php print _("Quoi"); ?></legend>
          <span class="wrapper-penalite">
            <label for="loot_input"><?php print _("Nom de l'objet"); ?></label>
            <input required type="text" name="loot" id="loot_input" maxlength="250">
          </span>
          <span class="wrapper-penalite">
            <label for="propriete"><?php print _("Effet"); ?></label>
            <select name="propriete" id="propriete">
              <option value="" selected><?php print _("Choisir"); ?></option>
              <option value="hp">💛 <?php print _("Vie"); ?></option>
              <?php if ($settings['willpower_on']) {
                print "<option value=\"wp\">🌟" . _("Volonté") . "</option>\r\n";
              } ?>
              <option value="carac1"><?php print ucfirst($settings['carac1_name']); ?></option>
              <option value="carac2"><?php print ucfirst($settings['carac2_name']); ?></option>
              <?php if ($settings['carac3_name'] != "") {
                print "<option value=\"carac3\">" . ucfirst($settings['carac3_name']) . "</option>";
              } ?>
            </select>
            <input type="number" value="0" name="bonus" placeholder="bonus" min="-999999999" max="999999999">
          </span>
        </fieldset>
        <fieldset>
          <legend><?php print _("À qui (groupe)"); ?></legend>
          <div class="wrapper-penalite">
            <label for="qui"><?php print _("Un groupe de personnage"); ?></label>
            <select name="qui" id="qui">
              <option value="all"><?php print _("Tout le monde"); ?></option>
              <option value="carac1"><?php print sprintf(_("Chaque personnage %s"), $settings['carac1_group']); ?></option>
              <option value="carac2"><?php print sprintf(_("Chaque personnage %s"), $settings['carac2_group']); ?></option>
              <?php if ($settings['carac3_name'] != "") {
                print "<option value=\"carac3\">" . sprintf(_("Chaque personnage %s"), $settings['carac3_group']) . "</option>";
              } ?>
              <?php
              if (isset($list_players)) {
                // TODO : vérifier pertinence de cette section
                foreach ($list_players as $key_player => $player) {
                  print "<option value='$key_player'>$player</option>";
                }
              }
              ?>
            </select>
            <label for="qui_tags"><strong><?php print _("ayant</strong> au moins un des tags"); ?></label>
            <input class="tag-whitelist" type="text" name="qui_tags" placeholder="<?php print _("Entrez un tag"); ?>"  id="qui_tags" maxlength="250">
          </div>
          <div>
            <input type="checkbox" name="restrict_active" id="restrict_active" <?php print ($settings['restrict_active'] ? 'checked' : ''); ?>>
            <label for="restrict_active"><?php print _("Limiter aux personnages actifs"); ?></label>
          </div>
        </fieldset>
        <fieldset>
          <legend><?php print _("À qui (individus)"); ?></legend>
          <span class="wrapper-penalite">
            <label for="qui_multiple"><?php print _("<strong>Ou</strong> des personnages spécifiques"); ?></label>
            <input type="text" class="player-whitelist" name="qui_multiple" id="qui_multiple" placeholder="<?php print _("Entrez un nom"); ?>">
          </span>
        </fieldset>
        <input type="submit" value="<?php print _("Donner"); ?>">
      </form>
    </div>

    <div id="tags">
      <!-- FORMULAIRE DES TAGS-->
      <h3><?php print _("Attribuer aléatoirement des étiquettes à la population."); ?></h3>
      <form method="post" action="ecran.php?action=tags">
        <span><label for="tag1"><?php print _("Catégorie 1"); ?></label><a href="ecran.php?action=delete_tags&category=1"><?php print _("Supprimer"); ?></a></span>
        <input type="text" id="tag1" name="tag1" maxlength="250" placeholder="Entrez des mots-clefs">
        <span><label for="tag2"><?php print _("Catégorie 2"); ?></label><a href="ecran.php?action=delete_tags&category=2"><?php print _("Supprimer"); ?></a></span>
        <input type="text" id="tag2" name="tag2" maxlength="250" placeholder="Entrez des mots-clefs">
        <span><label for="tag3"><?php print _("Catégorie 3"); ?></label><a href="ecran.php?action=delete_tags&category=3"><?php print _("Supprimer"); ?></a></span>
        <input type="text" id="tag3" name="tag3" maxlength="250" placeholder="Entrez des mots-clefs">
        <input type="submit" value="<?php print _("Attribuer"); ?>">
      </form>
    </div>
    <div id="settings">
      <!-- FORMULAIRE DES PARAMETRES DE LA PARTIE-->
      <h3><?php print _("Changer les réglages de votre aventure."); ?></h3>
      <form class="grid" method="post" action="ecran.php?action=settings">
        <div class="settings-wrapper">
          <fieldset>
            <legend><?php print _("Intro"); ?></legend>
            <label for="adventure_name"><?php print _("Nom de l'aventure"); ?></label>
            <input type="text" name="adventure_name" id="adventure_name" maxlength="250" value="<?php print $settings['adventure_name']; ?>">
            <label for="adventure_guide"><?php print _("Adresse ip ou url pour rejoindre"); ?></label>
            <textarea type="textarea" name="adventure_guide" size=5 id="adventure_guide" maxlength="250"><?php print $settings['adventure_guide']; ?></textarea>
          </fieldset>
          <fieldset>
            <legend><?php print _("Rôles"); ?></legend>
            <label for="role_leader"><?php print _("Nom de rôle de leader"); ?></label>
            <input type="text" name="role_leader" id="role_leader" maxlength="250" value="<?php print $settings['role_leader']; ?>">
            <label for="role_traitre"><?php print _("Nom de rôle de traître"); ?></label>
            <input type="text" name="role_traitre" id="role_traitre" maxlength="250" value="<?php print $settings['role_traitre']; ?>">
          </fieldset>
          <fieldset class="form-list">
            <legend><?php print _("Autres paramètres"); ?></legend>
            <div>
              <input type="checkbox" name="same_stats_all" id="same_stats_all" <?php print ($settings['same_stats_all'] ? 'checked' : ''); ?>>
              <label for="same_stats_all"><?php print _("Mêmes stats pour tout le monde"); ?></label>
            </div>
            <div>
              <input type="checkbox" name="random_tags" id="random_tags" <?php print ($settings['random_tags'] ? 'checked' : ''); ?>>
              <label for="random_tags"><?php print _("Tags distribués aléatoirement"); ?></label>
            </div>
            <div>
              <input type="checkbox" name="willpower_on" id="willpower_on" <?php print ($settings['willpower_on'] ? 'checked' : ''); ?>>
              <label for="willpower_on"><?php print _("Jauge de volonté"); ?></label>
            </div>
            <div>
              <input type="checkbox" name="restrict_active" id="restrict_active" <?php print ($settings['restrict_active'] ? 'checked' : ''); ?>>
              <label for="restrict_active"><?php print _("Restreindre aux actifs par défaut"); ?></label>
            </div>
            <div>
              <input type="checkbox" name="lock_new" id="lock_new" <?php print ($settings['lock_new'] ? 'checked' : ''); ?>>
              <label for="lock_new"><?php print _("Verrouiller les créations de personnage"); ?></label>
            </div>
          </fieldset>
          <fieldset>
            <legend><?php print _("1ère caractéristique"); ?></legend>
            <label for="carac1_name"><?php print _("Nom"); ?></label>
            <input type="text" placeholder="esprit" name="carac1_name" id="carac1_name" value="<?php print $settings['carac1_name']; ?>">
            <label for="carac1_group"><?php print _("Un personnage fort dans cette carac est :"); ?></label>
            <input type="text" placeholder="malin" name="carac1_group" id="carac1_group" value="<?php print $settings['carac1_group']; ?>">
          </fieldset>
          <fieldset>
            <legend><?php print _("2ème caractéristique"); ?></legend>
            <label for="carac2_name"><?php print _("Nom"); ?></label>
            <input type="text" placeholder="corps" name="carac2_name" id="carac2_name" value="<?php print $settings['carac2_name']; ?>">
            <label for="carac2_group"><?php print _("Un personnage fort dans cette carac est :"); ?></label>
            <input type="text" placeholder="fort" name="carac2_group" id="carac2_group" value="<?php print $settings['carac2_group']; ?>">
          </fieldset>
          <fieldset>
            <legend><?php print _("3ème caractéristique"); ?></legend>
            <label for="carac3_name"><?php print _("Nom"); ?></label>
            <input type="text" placeholder="" name="carac3_name" id="carac3_name" value="<?php print $settings['carac3_name']; ?>">
            <label for="carac3_group"><?php print _("Un personnage fort dans cette carac est :"); ?></label>
            <input type="text" placeholder="" name="carac3_group" id="carac3_group" value="<?php print $settings['carac3_group']; ?>">
          </fieldset>
        </div>
        <div class="buttons-wrapper">
          <input type="submit" value="<?php print _("Enregistrer"); ?>">
          <div class="delete-game">
            <span id="delete-game" class="submit-button">🗑️ <?php print _("Détruire l'aventure"); ?></span>
            <a id="delete-game-confirm" class="submit-button" href="ecran.php?action=delete"><?php print _("Confirmez"); ?></a>
          </div>
        </div>
      </form>
    </div>
    <div id="debug">
      <span>GAME TIMESTAMP : <?php print date('H:i:s', $game_timestamp); ?></span>
    </div>
  </div>
  <?php if (isset($_SESSION['sanction'])) print $_SESSION['sanction']; ?>
  <div id="group">
    <?php
    $available_colors = ['#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231', '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe', '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000', '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'];
    $taken_colors = [];

    $list_players = [];
    $list_players_for_js = [];
    foreach ($players as $key => $row) {
      $id_joueur = $row['id'];
      $nom = $row['nom'];
      $carac3 = $row['carac3'];
      $carac2 = $row['carac2'];
      $carac1 = $row['carac1'];
      $hp = $row['hp'];
      $wp = $row['wp'];
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
          $tag = ucfirst($tag);
          $str_tags .= "<span><span class='tag-bullet' style=background-color:$color></span>$tag</span>";
        }
      }

      if ($hp <= 0) {
        $alive = 'not-alive';
      }
      elseif ($settings['willpower_on'] && $wp <= 0) {
        $alive = 'not-alive';
      }
      else {
        $alive = 'alive';
      }
      $alive .= ($row['active'] == 0) ? ' inactive' : '';

      if ($hp <= 0) {
        $list_players[$id_joueur] = $nom . ' (☠️)';
        $list_players_for_js[] = ['value' => $nom . ' (☠️)', 'code' => $id_joueur];
      }
      elseif ($settings['willpower_on'] && $wp <= 0) {
        $list_players[$id_joueur] = $nom . ' (🌑️)';
        $list_players_for_js[] = ['value' => $nom . ' (🌑️)', 'code' => $id_joueur];
      }
      else {
        $list_players[$id_joueur] = $nom;
        $list_players_for_js[] = ['value' => $nom, 'code' => $id_joueur];
      }

      $aptitude = '';

      if ($carac1 >= 15) $aptitude .= ucfirst($settings['carac1_group']) . "<br />";
      if ($carac2 >= 15) $aptitude .= ucfirst($settings['carac2_group']) . "<br />";
      if ($settings['carac3_group'] != "") {
        if ($carac3 >= 15) $aptitude .= ucfirst($settings['carac3_group']) . "<br />";
      }

      print "<div id=pj-$id_joueur class=\"$alive\">
        <b class='pj-name'>$nom</b>";
      if (!empty($str_tags)) {
        print "<div class='tags'>
          $str_tags
        </div>";
      }

      if ( ($settings['willpower_on'] && ($hp > 0 && $wp > 0)) || (!$settings['willpower_on'] && ($hp > 0)) ) {
        print "<div class='stats'>";
        if (!empty($aptitude)) {
          print "<span class='aptitude'>$aptitude</span>";
        }
        print '<span>' . ucfirst($settings['carac1_name']) . " : " . $carac1 . '</span>';
        print '<span>' . ucfirst($settings['carac2_name']) . " : " . $carac2 . '</span>';
        if ($settings['carac3_name'] != "") {
          print '<span>' . ucfirst($settings['carac3_name']) . " : " . $carac3 . '</span>';
        }
        print "</div>";
        print "<div class='stats'>";
        print "<span class='life'>". sprintf(_("Vie : %s"), $hp) . "</span>";
        if ($settings['willpower_on']) {
          print "<span>" . sprintf(_("Volonté : %s"), $wp) . "</span>";
        }
        print "</div>";
      }
      print "</div>";
    }
    print '<script>tags_players = ' . json_encode($list_players_for_js) . '</script>';
    ?>
  </div>
</div>
