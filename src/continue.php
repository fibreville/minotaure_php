<?php include "header.php"; ?>
<h2>Repartons à l'aventure !</h2>
<form action="continuevalid.php" method="post">
  <label for="nom">Nom :</label><input type="text" name="nom" size=40">
  <label for="nom">Mot de passe :</label><input type="password" name="pass" size=24">
  <input class="submit-button" type="submit" value="Continuer l'Aventure">
</form>
<?php include "footer.php"; ?>