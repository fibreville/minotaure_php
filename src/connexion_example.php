<?php
try {
    $db = new PDO('mysql:host=serveur-mysql;dbname=nombase;', 'login', 'mdp');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die('Erreur sql : ' . $e->getMessage());
}
$tmp_path = '/tmp';

