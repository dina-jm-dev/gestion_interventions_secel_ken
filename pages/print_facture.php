<?php
// pages/print_facture.php
$id_intervention = $_GET['id'] ?? 0;

// Fetch Invoice details, mapping to the interventions and clients
$stmt = $conn->prepare("SELECT f.*, i.nom as intervention_nom, i.lieu, i.date_creation, 
                               u.nom as client_nom, u.telephone as client_tel, u.email as client_email
                        FROM interventions i
                        LEFT JOIN factures f ON f.id_intervention = i.id_intervention
                        LEFT JOIN utilisateurs u ON i.id_client = u.id_utilisateur
                        WHERE i.id_intervention = ?");
$stmt->execute([$id_intervention]);
$data = $stmt->fetch();

if (!$data) {
    echo "<h3>Intervention/Facture introuvable.</h3>";
    exit();
}

// Ensure there is a fake invoice generated if non exists in DB for demo purposes
$montant = $data['montant'] ?? 0.00;
$facture_libelle = $data['libelle'] ?? 'Facture standard d\'intervention';
$date_facture = $data['date_facture'] ?? date('Y-m-d H:i:s');
$statut = $data['statut'] ?? 'non_paye';
?>

<style>
/* Styles spécifiques pour l'impression locale via le navigateur */
@media print {
    body * {
        visibility: hidden;
    }
    #print-zone, #print-zone * {
        visibility: visible;
    }
    #print-zone {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
        box-sizing: border-box;
    }
    .no-print {
        display: none !important;
    }
    @page { margin: 1cm; size: A4 portrait; }
}

.print-container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    padding: 40px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    color: #333;
}
.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 2px solid #2563eb;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.invoice-title h1 {
    color: #2563eb;
    margin: 0;
    font-size: 32px;
}
.invoice-info p, .client-info p {
    margin: 5px 0;
}
.client-info {
    text-align: right;
}
.invoice-table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0;
}
.invoice-table th, .invoice-table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
.invoice-table th {
    background-color: #f8fafc;
    color: #2563eb;
}
.invoice-table td.text-right, .invoice-table th.text-right {
    text-align: right;
}
.invoice-total {
    width: 50%;
    margin-left: auto;
    background: #f8fafc;
    padding: 20px;
    border-radius: 8px;
}
.invoice-total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1.1rem;
}
.invoice-total-row.grand-total {
    font-weight: bold;
    font-size: 1.3rem;
    color: #2563eb;
    border-top: 2px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
}
</style>

<div class="card" style="text-align: right; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn btn-primary no-print"><i class="fas fa-print"></i> Imprimer la Facture</button>
    <a href="index.php?page=dashboard" class="btn bg-gray no-print">Retour</a>
</div>

<div id="print-zone" class="print-container">
    <div class="invoice-header">
        <div class="invoice-title">
            <h1>FACTURE</h1>
            <div class="invoice-info mt-3">
                <p><strong>Date de facturation :</strong> <?php echo date('d/m/Y', strtotime($date_facture)); ?></p>
                <p><strong>Référence :</strong> FAC-INT-<?php echo str_pad($id_intervention, 5, "0", STR_PAD_LEFT); ?></p>
                <p><strong>Statut :</strong> <?php echo $statut === 'paye' ? 'Acquittée' : 'À payer'; ?></p>
            </div>
        </div>
        <div class="client-info">
            <h3 style="color: #64748b; margin-top: 0; margin-bottom: 15px;">FACTURÉ À :</h3>
            <p><strong>Client :</strong> <?php echo htmlspecialchars($data['client_nom']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($data['client_email']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($data['client_tel'] ?? 'N/C'); ?></p>
            <p><strong>Lieu d'intervention :</strong> <?php echo htmlspecialchars($data['lieu']); ?></p>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description de la prestation</th>
                <th class="text-right">Montant HT</th>
                <th class="text-right">TVA (20%)</th>
                <th class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($facture_libelle); ?></strong><br>
                    <span style="font-size: 0.85rem; color: #666;">
                        Intervention : <?php echo htmlspecialchars($data['intervention_nom']); ?>
                    </span>
                </td>
                <?php
                if ($montant == 0) {
                    // Si pas de montant en DB, on simule un prix forfaitaire ou on laisse 0
                    $montant_ht = 150.00;
                } else {
                    $montant_ht = $montant / 1.20; // Reverse calculate HT if recorded as TTC
                }
                $tva = $montant_ht * 0.20;
                $tot_ttc = $montant_ht + $tva;
                ?>
                <td class="text-right"><?php echo number_format($montant_ht, 2, ',', ' '); ?> €</td>
                <td class="text-right"><?php echo number_format($tva, 2, ',', ' '); ?> €</td>
                <td class="text-right"><?php echo number_format($tot_ttc, 2, ',', ' '); ?> €</td>
            </tr>
        </tbody>
    </table>

    <div class="invoice-total">
        <div class="invoice-total-row">
            <span>Total HT :</span>
            <span><?php echo number_format($montant_ht, 2, ',', ' '); ?> €</span>
        </div>
        <div class="invoice-total-row">
            <span>TVA (20%) :</span>
            <span><?php echo number_format($tva, 2, ',', ' '); ?> €</span>
        </div>
        <div class="invoice-total-row grand-total">
            <span>TOTAL TTC :</span>
            <span><?php echo number_format($tot_ttc, 2, ',', ' '); ?> €</span>
        </div>
    </div>

    <div style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; color: #64748b; font-size: 0.9rem; text-align: center;">
        <p>Merci de votre confiance. En cas de questions concernant cette facture, merci de nous contacter.</p>
        <p>SECEL - 123 Rue de la Réparation, 75000 Paris - TVA: FR123456789</p>
    </div>
</div>
