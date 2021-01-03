<!doctype html>
<html id="page-character">
<?php
include 'header.php';

$cleanPost = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$choix = $cleanPost['choix'];
$lead = $cleanPost['lead'];
$traitre = $cleanPost['traitre'];

$id = $_SESSION['id'];
if ($choix != "") {
  $stmt = $db->prepare("UPDATE hrpg SET vote=:choix WHERE id=:id");
  $stmt->execute([
      ':choix' => $choix,
      ':id' => $id,
  ]);

  if ($lead == 1) {
      $stmt = $db->prepare("UPDATE hrpg SET leader='2' WHERE id=:id");
      $stmt->execute([
      ':id' => $id,
  ]);
  }

  if ($traitre == 1) {
      $stmt = $db->prepare("UPDATE hrpg SET traitre='2' WHERE id=:id");
      $stmt->execute([
      ':id' => $id,
  ]);
}
?>
<div id="character-wrapper"></div>
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous">
</script>
<script src="js/ajax_pj.js"></script>
<?php
  include "footer.php";
?>
</html>
