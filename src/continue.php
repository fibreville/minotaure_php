<?php include "header.php"; ?>
<h2><?php print _("Repartons à l'aventure !"); ?></h2>
<form action="continuevalid.php" method="post">
  <label for="nom"><?php print _("Nom : "); ?></label><input type="text" name="nom" size=40">
  <label for="nom"><?php print _("Mot de passe : "); ?></label><input type="password" name="pass" size=24">
  <input class="submit-button" type="submit" value="<?php print _("Continuer l'Aventure"); ?>">
</form>
<a href="index.php"><?php print _('Retour'); ?></a>
<?php include "footer.php"; ?>
