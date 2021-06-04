<?php
include "connexion.php";
include "header.php";

isset($_POST['nom']) ? $nom = $_POST['nom'] : $nom = "";
isset($_POST['pass']) ? $pass = $_POST['pass'] : $pass = "";
isset($_POST['stat']) ? $stat = $_POST['stat'] : $stat = "";
$probleme = NULL;

if ($settings['lock_new']) {
  $probleme = _("L'aventure n'accueille pas de nouveaux personnages pour l'instant !");
}
elseif (empty($nom) || empty($pass)) {
  $probleme = sprintf(_('Veuillez remplir le champ : %s.'), (empty($nom) ? _('nom') : _('mot de passe')));
}
elseif (preg_match('/^[A-Za-z0-9-]+$/D', $nom) === 0) {
  $probleme = _('Veuillez utiliser uniquement des chiffres et des lettres pour votre login.');
}
else {
  $nom = strtolower($nom);
  $pass = password_hash($pass, PASSWORD_DEFAULT);
  $stmt = $db->prepare("SELECT id FROM hrpg WHERE nom=:nom");
  $stmt->execute([
          ':nom' => $nom,
  ]);

  if ($stmt->rowCount() > 0) {
    $probleme = _('Ce nom est déjà utilisé. Veuillez en choisir un autre.');
  }
}
?>
<div>
  <?php
  if (empty($probleme)) {
    if ($settings['same_stats_all']) {
      $carac1 = $carac2 = $carac3 = $hp = $wp = 10;
    }
    else {
      $caracs = explode('_', $stat);
      $carac1 = $caracs[0];
      $carac2 = $caracs[1];
      $carac3 = $caracs[2]; // définie à 10 par défaut même si 2 caracs
      
      if ($_SESSION['settings']['carac3_name'] != "") {
        if ($carac3 == 9) {
          // Personnage équilibré
          // Chaque carac peut aller de 9 à 11
          
          // Protection triche
          if (($carac1 + $carac2 + $carac3) > 27) {
            $carac1 = $carac2 = $carac3 = 9;
          }
          
          // Tirage des caracs
          $tirage_carac = array(0, 1, 1, 2);
          shuffle($tirage_carac);
          $carac1 = $carac1 + $tirage_carac[0];
          $carac2 = $carac2 + $tirage_carac[1];
          $carac3 = 30 - ( $carac1 + $carac2);
        }
        else {
          // Personnage spécialisé
          
          // Protection triche
          if (($carac1 + $carac2 + $carac3) > 24) {
            $carac1 = $carac2 = $carac3 = 8;
          }
          
          // Tirage des caracs
          $tirage_carac = array(0, 1, 1, 2, 2, 2, 3, 3, 4);
          shuffle($tirage_carac);
          $carac1 = $carac1 + $tirage_carac[0];
          $carac2 = $carac2 + $tirage_carac[1];
          $carac3 = 30 - ( $carac1 + $carac2);
        }
      }
      else {
        // 2 caracs : protection triche
        if (($carac1 + $carac2) > 20) {
          $carac1 = $carac2 = $carac3 = 10;
        }
      }
      
      $hp = 10 + rand(-2, 2);
      $wp = 20 - $hp + rand(-1, 1);
    }

    $tags = [];
    if ($settings['random_tags']) {
      $stmt = $db->prepare("SELECT id FROM tag WHERE category = 1 ORDER BY RAND()");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $tags[] = $row[0];
      }

      $stmt = $db->prepare("SELECT id FROM tag WHERE category = 2 ORDER BY RAND()");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $tags[] = $row[0];
      }

      $stmt = $db->prepare("SELECT id FROM tag WHERE category = 3 ORDER BY RAND()");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $tags[] = $row[0];
      }
    }
    else {
      $stmt = $db->prepare("
      SELECT id, count(*) c FROM tag
      RIGHT JOIN character_tag c ON c.`id_tag` = tag.id
      WHERE tag.category = 1 
      GROUP BY tag.id
      ORDER BY c ASC
      LIMIT 0,1");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $tags[] = $row[0];
      }

      $stmt = $db->prepare("
      SELECT id, count(*) c FROM tag
      LEFT JOIN character_tag c ON c.`id_tag` = tag.id
      WHERE tag.category = 2
      GROUP BY tag.id
      ORDER BY c ASC
      LIMIT 0,1");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $tags[] = $row[0];
      }

      $stmt = $db->prepare("
      SELECT id, count(*) c FROM tag
      LEFT JOIN character_tag c ON c.`id_tag` = tag.id
      WHERE tag.category = 3
      GROUP BY tag.id
      ORDER BY c ASC
      LIMIT 0,1");
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $tags[] = $row[0];
      }
    }

    try {
      $stmt = $db->prepare("INSERT INTO hrpg (nom,mdp,carac3,carac2,carac1,hp,wp,active) VALUES(:nom,:pass,:carac3,:carac2,:carac1,:hp,:wp,:active)");
      $stmt->execute([
        ':nom' => $nom,
        ':pass' => $pass,
        ':carac3' => $carac3,
        ':carac2' => $carac2,
        ':carac1' => $carac1,
        ':hp' => $hp,
        ':wp' => $wp,
        ':active' => 1
      ]);
      $id = $db->lastInsertId();

      foreach($tags as $tag) {
        if (!empty($tag)) {
          $stmt = $db->prepare("INSERT INTO character_tag (id_player,id_tag) VALUES(:id_player,:id_tag)");
          $stmt->execute([':id_player' => $id, ':id_tag' => $tag]);
        }
      }

    } catch (Exception $e) {
      die($e->getMessage());
    }

    $_SESSION['id'] = $id;
    $_SESSION['nom'] = $nom;
    ?>
    <?php if ($id != 1): ?>
      <div><span class="pj-name"><?php print sprintf(_("%s entre en scène."), $nom); ?></span></div>
      <div><?php print _("Bienvenue dans notre grande aventure."); ?></div>
      <div><a href="main.php"><?php print _("C'est parti."); ?></a></div>
    <?php else: ?>
      <div><?php print _("Le compte d'administration a été créé."); ?></div>
      <div><?php print _("Bienvenue dans votre aventure."); ?></div>
      <div><a href="ecran.php"><?php print _("Aller sur l'écran du MJ."); ?></a></div>
    <?php endif; ?>
    <?php
  }
  else {
    ?>
    <div><?php print _("Impossible de créer votre personnage 😢."); ?></div>
    <div><?php print $probleme; ?></div>
    <div><a href=new.php><?php print _("Réessayez</a> ou retournez <a href=index.php>au menu principal."); ?></a></div>
    <?php
  }
  ?>
</div>
<?php include "footer.php"; ?>
