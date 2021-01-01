<?php

try {
    $db = new PDO('mysql:host=localhost;dbname=atrpg;', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die('Erreur sql');
}
?>
