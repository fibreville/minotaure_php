<?php
session_start();
$page_id = 'page-character';
$_SESSION['current_timestamp'] = 0;
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
}

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
<div id="loader" class="active"></div>
<div id="character-wrapper">Nous récupérons votre personnage.</div>
<script src="js/jquery-3.5.1.min.js"></script>
<script src="js/ajax_pj.js"></script>
<?php
include "footer.php";
?>