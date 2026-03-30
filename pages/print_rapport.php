<?php
// pages/print_rapport.php
$id_intervention = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT r.*, i.nom as intervention_nom, i.lieu, i.description_besoin, 
                               u.nom as client_nom, u.telephone as client_tel, 
                               a.heure_arrivee, a.heure_depart, 
                               t.nom as tech_nom 
                        FROM interventions i
                        LEFT JOIN rapports r ON r.id_intervention = i.id_intervention
                        LEFT JOIN utilisateurs u ON i.id_client = u.id_utilisateur
                        LEFT JOIN affectations a ON i.id_intervention = a.id_intervention
                        LEFT JOIN utilisateurs t ON a.id_technicien = t.id_utilisateur
                        WHERE i.id_intervention = ?");
$stmt->execute([$id_intervention]);
$data = $stmt->fetch();

if (!$data) {
    echo "<h3>Intervention introuvable.</h3>";
    exit();
}
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
.print-header {
    text-align: center;
    border-bottom: 2px solid #2563eb;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.print-header h1 {
    color: #2563eb;
    margin: 0;
    font-size: 28px;
}
.print-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
    background: #f8fafc;
    padding: 20px;
    border-radius: 6px;
}
.print-content {
    line-height: 1.6;
}
.print-section-title {
    font-weight: bold;
    color: #2563eb;
    margin-top: 25px;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}
</style>

<div class="card" style="text-align: right; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn btn-primary no-print"><i class="fas fa-print"></i> Imprimer ce document</button>
    <a href="index.php?page=dashboard" class="btn bg-gray no-print">Retour</a>
</div>

<div id="print-zone" class="print-container">
    <div class="print-header">
        <h1>RAPPORT D'INTERVENTION</h1>
        <p>Référence : INT-<?php echo str_pad($id_intervention, 5, "0", STR_PAD_LEFT); ?></p>
    </div>

    <div class="print-meta-grid">
        <div>
            <div class="print-section-title" style="margin-top:0;">Client</div>
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($data['client_nom']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($data['client_tel'] ?? 'N/C'); ?></p>
            <p><strong>Lieu :</strong> <?php echo htmlspecialchars($data['lieu']); ?></p>
        </div>
        <div>
            <div class="print-section-title" style="margin-top:0;">Intervention</div>
            <p><strong>Technicien :</strong> <?php echo htmlspecialchars($data['tech_nom']); ?></p>
            <p><strong>Arrivée :</strong> <?php echo $data['heure_arrivee'] ? substr($data['heure_arrivee'], 0, 5) : 'Non renseigné'; ?></p>
            <p><strong>Départ :</strong> <?php echo $data['heure_depart'] ? substr($data['heure_depart'], 0, 5) : 'Non renseigné'; ?></p>
        </div>
    </div>

    <div class="print-content">
        <h3 class="print-section-title">Description du Besoin Initial</h3>
        <p><?php echo nl2br(htmlspecialchars($data['description_besoin'] ?? 'Aucune description fournie.')); ?></p>

        <h3 class="print-section-title">Titre de la mission</h3>
        <p><?php echo htmlspecialchars($data['intervention_nom']); ?></p>

        <h3 class="print-section-title">Contenu du Rapport</h3>
        <?php if (!empty($data['contenu'])): ?>
            <p><?php echo nl2br(htmlspecialchars($data['contenu'])); ?></p>
        <?php else: ?>
            <p style="color: #64748b; font-style: italic;">Le technicien n'a pas encore rédigé le rapport officiel.</p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 60px; display: flex; justify-content: space-between; text-align: center;">
        <div>
            <p><strong>Signature Client</strong></p>
            <div style="width: 200px; height: 100px; border: 1px dashed #ccc; margin-top: 10px;"></div>
        </div>
        <div>
            <p><strong>Signature Technicien</strong></p>
            <div style="width: 200px; height: 100px; border: 1px dashed #ccc; margin-top: 10px;"></div>
        </div>
    </div>
</div>
