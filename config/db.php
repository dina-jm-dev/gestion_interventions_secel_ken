<?php
/**
 * Configuration de la base de données
 * Utilisation de PDO pour la sécurité et les requêtes préparées
 */

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "secel_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Configurer le mode d'erreur sur Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configurer le mode de récupération par défaut sur assoc
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}
?>
