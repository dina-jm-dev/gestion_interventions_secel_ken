<?php
/**
 * Historique des Rapports - SECEL
 */
checkAuth();
$role = $_SESSION['role'];
$id_user = $_SESSION['id_utilisateur'];

try {
    if ($role == 'admin') {
        $stmt = $conn->query("SELECT r.*, i.nom as inter_nom, u.nom as tech_nom 
                             FROM rapports r 
                             JOIN interventions i ON r.id_intervention = i.id_intervention 
                             JOIN affectations a ON i.id_intervention = a.id_intervention 
                             JOIN utilisateurs u ON a.id_technicien = u.id_utilisateur
                             ORDER BY r.date_rapport DESC");
    } elseif ($role == 'technicien') {
        $stmt = $conn->prepare("SELECT r.*, i.nom as inter_nom 
                                FROM rapports r 
                                JOIN interventions i ON r.id_intervention = i.id_intervention 
                                JOIN affectations a ON i.id_intervention = a.id_intervention 
                                WHERE a.id_technicien = ?
                                ORDER BY r.date_rapport DESC");
        $stmt->execute([$id_user]);
    } else {
        $stmt = $conn->prepare("SELECT r.*, i.nom as inter_nom 
                                FROM rapports r 
                                JOIN interventions i ON r.id_intervention = i.id_intervention 
                                WHERE i.id_client = ?
                                ORDER BY r.date_rapport DESC");
        $stmt->execute([$id_user]);
    }
    $rapports = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<div class="rapports-page">
    <div class="mb-5">
        <h3>Rapports d'Intervention</h3>
        <p class="text-muted">Historique complet des opérations techniques réalisées.</p>
    </div>

    <div class="rapports-grid">
        <?php foreach ($rapports as $r): ?>
            <div class="card border-top-primary">
                <div class="flex-between mb-3">
                    <div class="fw-bold text-lg text-primary"><?php echo $r['inter_nom']; ?></div>
                    <div class="text-xs text-muted"><?php echo formatDate($r['date_rapport']); ?></div>
                </div>
                <?php if(isset($r['tech_nom'])): ?>
                    <div class="rapport-tech-badge">
                        👷 Technicien : <strong><?php echo $r['tech_nom']; ?></strong>
                    </div>
                <?php endif; ?>
                <div class="rapport-content">
                    <div class="fw-bold mb-2 text-main"><?php echo $r['libelle']; ?></div>
                    <p class="rapport-text"><?php echo $r['contenu']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($rapports)): ?>
            <div class="card empty-state col-span-full">
                Aucun rapport technique disponible.
            </div>
        <?php endif; ?>
    </div>
</div>
