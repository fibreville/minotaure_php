<?php
error_reporting(E_ALL);
try {
  $url = parse_url(getenv("CLEARDB_DATABASE_URL"));

  $server = $url["host"];
  $username = $url["user"];
  $password = $url["pass"];
  $db = substr($url["path"], 1);
  $db = new PDO('mysql:host=' . $server . ';dbname=' . $db, $username, $password);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die('Erreur sql : ' . $e->getMessage());
}
$tmp_path = '/tmp';
if (file_exists($tmp_path)) {
  if (!is_writable($tmp_path)) {
    die("Dossier des fichiers temporaires non-accessible en écriture.");
  }
}
else {
  die("Dossier des fichiers temporaires introuvable.");
}