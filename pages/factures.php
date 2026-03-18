<?php
/**
 * Gestion de la Facturation - SECEL
 * Interface Administrateur
 */
checkAuth('admin');

// Marquer comme payé (PDO)
if (isset($_GET['paye'])) {
    $id_fac = $_GET['paye'];
    $stmt = $conn->prepare("UPDATE factures SET statut = 'paye' WHERE id_facture = ?");
    $stmt->execute([$id_fac]);
    header("Location: index.php?page=factures&msg=paid");
    exit();
}

// Récupération des factures
$stmt = $conn->query("SELECT f.*, i.nom as intervention_nom, u.nom as client_nom 
                     FROM factures f 
                     JOIN interventions i ON f.id_intervention = i.id_intervention 
                     JOIN utilisateurs u ON i.id_client = u.id_utilisateur 
                     ORDER BY date_facture DESC");
$factures = $stmt->fetchAll();
?>

<div class="billing-page">
    <div class="flex-between-center mb-4">
        <h3>Gestion de la Facturation</h3>
        <div class="badge badge-info">Registre Financier</div>
    </div>

    <div class="card table-wrapper">
        <table class="w-full">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Client</th>
                    <th>Intervention</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factures as $f): ?>
                <tr>
                    <td><span class="fw-bold">#FAC-<?php echo str_pad($f['id_facture'], 5, '0', STR_PAD_LEFT); ?></span></td>
                    <td><strong><?php echo $f['client_nom']; ?></strong></td>
                    <td class="text-md"><?php echo $f['intervention_nom']; ?></td>
                    <td class="fw-extrabold text-primary"><?php echo formatMoney($f['montant']); ?></td>
                    <td>
                        <?php if ($f['statut'] == 'paye'): ?>
                            <span class="badge badge-success">Payée</span>
                        <?php else: ?>
                            <span class="badge badge-error">Impayée</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?php if ($f['statut'] == 'non_paye'): ?>
                            <a href="index.php?page=factures&paye=<?php echo $f['id_facture']; ?>" class="btn btn-primary btn-sm-px">Encaisser</a>
                        <?php else: ?>
                            <img src="assets/img/check_circle_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" class="icon-md opacity-50">
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($factures)): ?>
                <tr>
                    <td colspan="6" class="empty-state-text">Aucune facture générée.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
