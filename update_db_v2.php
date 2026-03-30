<?php
$host = 'localhost';
$dbname = 'secel_db';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion réussie...\n";

    // Table `interventions`
    try {
        $conn->exec("ALTER TABLE interventions ADD COLUMN lieu VARCHAR(150)");
        echo "Colonne 'lieu' ajoutée à 'interventions'.\n";
    } catch (PDOException $e) { echo "Info : 'lieu' existe peut-être déjà ou erreur. " . $e->getMessage() . "\n"; }

    try {
        $conn->exec("ALTER TABLE interventions ADD COLUMN description_besoin TEXT");
        echo "Colonne 'description_besoin' ajoutée à 'interventions'.\n";
    } catch (PDOException $e) { echo "Info : 'description_besoin' existe peut-être déjà. " . $e->getMessage() . "\n"; }

    try {
        $conn->exec("ALTER TABLE interventions ADD COLUMN validation_technicien BOOLEAN DEFAULT FALSE");
        echo "Colonne 'validation_technicien' ajoutée à 'interventions'.\n";
    } catch (PDOException $e) { echo "Info : 'validation_technicien' existe peut-être déjà.\n"; }

    try {
        $conn->exec("ALTER TABLE interventions ADD COLUMN validation_client BOOLEAN DEFAULT FALSE");
        echo "Colonne 'validation_client' ajoutée à 'interventions'.\n";
    } catch (PDOException $e) { echo "Info : 'validation_client' existe peut-être déjà.\n"; }

    // Table `affectations`
    try {
        $conn->exec("ALTER TABLE affectations ADD COLUMN date_fin_prevue DATETIME");
        echo "Colonne 'date_fin_prevue' ajoutée à 'affectations'.\n";
    } catch (PDOException $e) { echo "Info : 'date_fin_prevue' existe peut-être déjà.\n"; }

    try {
        $conn->exec("ALTER TABLE affectations ADD COLUMN date_arrivee DATETIME");
        echo "Colonne 'date_arrivee' ajoutée à 'affectations'.\n";
    } catch (PDOException $e) { echo "Info : 'date_arrivee' existe peut-être déjà.\n"; }

    try {
        $conn->exec("ALTER TABLE affectations ADD COLUMN date_depart DATETIME");
        echo "Colonne 'date_depart' ajoutée à 'affectations'.\n";
    } catch (PDOException $e) { echo "Info : 'date_depart' existe peut-être déjà.\n"; }

    echo "\nMise à jour de la base de données terminée avec succès !\n";

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
