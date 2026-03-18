<?php
require 'config/db.php';

try {
    // Add lieu to besoins
    $conn->exec("ALTER TABLE besoins ADD COLUMN lieu VARCHAR(255) AFTER id_client;");
    echo "Lieu added to besoins.<br>";
} catch (Exception $e) { echo $e->getMessage() . "<br>"; }

try {
    // Add lieu to interventions
    $conn->exec("ALTER TABLE interventions ADD COLUMN lieu VARCHAR(255) AFTER id_client;");
    echo "Lieu added to interventions.<br>";
} catch (Exception $e) { echo $e->getMessage() . "<br>"; }

try {
    // Update ENUM for interventions statut
    $conn->exec("ALTER TABLE interventions MODIFY COLUMN statut ENUM('attente', 'en_cours', 'termine', 'valide', 'annule') DEFAULT 'attente';");
    echo "Statut ENUM modified.<br>";
} catch (Exception $e) { echo $e->getMessage() . "<br>"; }

echo "Database updated successfully.";
