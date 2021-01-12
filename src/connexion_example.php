<?php
try {
  // Changez les valeurs dans la ligne ci-dessous.
  $db = new PDO('mysql:host=serveur-mysql;dbname=nombase;', 'login', 'mdp');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die('Erreur sql : ' . $e->getMessage());
}
// Pour la majorité des hébergements, laissez "/tmp" .
// Pour 000webhost, mettre "./tmp" .
$tmp_path = '/tmp';

if (file_exists($tmp_path)) {
  if (!is_writable($tmp_path)) {
    die("Dossier des fichiers temporaires non-accessible en écriture.");
  }
}
else {
  die("Dossier des fichiers temporaires introuvable.");
}

