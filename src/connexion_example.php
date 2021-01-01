<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=nombase;', 'login', 'mdp');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die('Erreur sql : ' . $e->getMessage());
}

