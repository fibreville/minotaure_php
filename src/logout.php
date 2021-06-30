<?php
require_once "connexion.php";
session_start();
if (isset($_SESSION['id'])) {
  $stmt = $db->prepare("UPDATE hrpg SET active=0 WHERE id = :id");
  $stmt->execute([':id' => $_SESSION['id']]);
}
session_destroy();
header('Location: index.php');