<?php
include "connexion.php";
$handle = fopen("./generate.txt", "r");
if ($handle) {
  while (($line = fgets($handle)) !== false) {
    $name = str_replace("\n", '', $line);;
    $stmt = $db->prepare("INSERT INTO hrpg (nom,mdp,carac2,carac1,hp,active) VALUES(:nom,:pass,:carac2,:carac1,:hp,:active)");
    $pass = $name . substr(strtolower($name), 0, 3) . substr(strtolower($name), -1);
    $pass = md5($pass);
    try {
      $stmt->execute([
        ':nom' => $name,
        ':pass' => $pass,
        ':carac2' => rand(1,5),
        ':carac1' => rand(1,5),
        ':hp' => rand(5,10),
        ':active' => 1
      ]);
    } catch (Exception $e) {
      die($e);
    }

  }

  fclose($handle);
} else {
  // error opening the file.
}