<?php
$host = 'localhost';
$dbname = 'secel_db';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion réussie...\n";

    try {
        $conn->exec("ALTER TABLE affectations ADD COLUMN heure_arrivee TIME");
        echo "Colonne 'heure_arrivee' ajoutée.\n";
    } catch (PDOException $e) { echo "Info : 'heure_arrivee' existe peut-être déjà.\n"; }

    try {
        $conn->exec("ALTER TABLE affectations ADD COLUMN heure_depart TIME");
        echo "Colonne 'heure_depart' ajoutée.\n";
    } catch (PDOException $e) { echo "Info : 'heure_depart' existe peut-être déjà.\n"; }

    echo "\nMise à jour V3 terminée !\n";

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
