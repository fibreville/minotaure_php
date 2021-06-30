<?php
// Change the values below to connect to your database.
$host = ''; // Host name.
$dbname = ''; // Name.
$port = 3306; // Port.
$login = ''; // Login.
$mdp = ''; // Password.

/*
Fill the location of your temporary files folder.
For most server you can keep /tmp.
*/
$tmp_path = '/tmp';

/*
Refer to README.md if a language doesn't work on your server.
Below is an example of manually set the locale for a language.
*/
/*
$languages = [
  'fr' => 'fr_FR.UTF8',
];
*/

try {
  $db = new PDO("mysql:host=$host:$port;dbname=$dbname", $login, $mdp);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die(_('Erreur sql : ') . $e->getMessage());
}

if (file_exists($tmp_path)) {
  if (!is_writable($tmp_path)) {
    die(_("Dossier des fichiers temporaires non-accessible en écriture."));
  }
}
else {
  die(_("Dossier des fichiers temporaires non-accessible en écriture."));
}

