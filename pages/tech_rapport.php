<?php
/**
 * Rédaction Rapport - SECEL
 * Interface Technicien
 */
checkAuth('technicien');

$id_inter = $_GET['id'] ?? null;
if (!$id_inter) {
    header("Location: index.php?page=dashboard");
    exit();
}

if (isset($_POST['add_rapport'])) {
    $libelle = clean($_POST['libelle']);
    $contenu = clean($_POST['contenu']);
    $montant = $_POST['montant'];
    
    $conn->beginTransaction();
    try {
        // 1. Créer le rapport
        $stmt = $conn->prepare("INSERT INTO rapports (id_intervention, libelle, contenu) VALUES (?, ?, ?)");
        $stmt->execute([$id_inter, $libelle, $contenu]);
        
        // 2. Mettre à jour le statut de l'intervention et la date_fin
        $date_fin = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE interventions SET statut = 'termine' WHERE id_intervention = ?");
        $stmt->execute([$id_inter]);
        
        $stmt = $conn->prepare("UPDATE affectations SET date_fin = ? WHERE id_intervention = ? AND id_technicien = ?");
        $stmt->execute([$date_fin, $id_inter, $_SESSION['id_utilisateur']]);
        
        // 3. Créer une facture préliminaire
        $stmt = $conn->prepare("INSERT INTO factures (id_intervention, libelle, montant) VALUES (?, ?, ?)");
        $stmt->execute([$id_inter, "Intervention : " . $libelle, $montant]);
        
        $conn->commit();
        header("Location: index.php?page=dashboard&msg=done");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>

<div class="card form-page-wide">
    <h3 class="mb-1">Rapport d'Intervention</h3>
    <p class="form-page-desc">Clôturer l'intervention #<?php echo $id_inter; ?> en rédigeant votre rapport.</p>
    
    <?php if(isset($error)): ?>
        <div class="alert-danger border-0 mb-3"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Libellé succinct</label>
            <input type="text" name="libelle" class="form-input" placeholder="ex: Remplacement disque dur" required>
        </div>
        <div class="form-group">
            <label class="form-label">Détails de l'intervention</label>
            <textarea name="contenu" class="form-input" rows="7" placeholder="Expliquez ce que vous avez fait..." required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Montant à facturer (FCFA)</label>
            <input type="number" name="montant" class="form-input" placeholder="ex: 25000" required>
        </div>
        
        <div class="gap-2 mt-4">
            <a href="index.php?page=dashboard" class="btn bg-gray flex-1 text-center">Annuler</a>
            <button type="submit" name="add_rapport" class="btn btn-primary flex-2">Valider et Facturer</button>
        </div>
    </form>
</div>
