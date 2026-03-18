<?php
checkAuth('client');
$id_client = $_SESSION['id_utilisateur'];

// Récupération des besoins du client
$sql = "SELECT * FROM besoins WHERE id_client = '$id_client' ORDER BY date_besoin DESC";
$besoins = mysqli_query($conn, $sql);

// Récupération des interventions en cours pour le client
$sql_int = "SELECT * FROM interventions WHERE id_client = '$id_client' ORDER BY date_creation DESC";
$interventions = mysqli_query($conn, $sql_int);
?>

<div class="client-tracking">
    <section class="mb-5">
        <h3>Mes Demandes en Attente</h3>
        <div class="demandes-grid">
            <?php while($b = mysqli_fetch_assoc($besoins)): ?>
                <div class="card">
                    <div class="text-xs text-muted"><?php echo formatDate($b['date_besoin']); ?></div>
                    <p class="mt-1"><?php echo $b['description']; ?></p>
                    <div class="text-sm text-primary mt-3">⌛ En attente d'un technicien</div>
                </div>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($besoins) == 0): ?>
                <p class="text-muted">Aucune nouvelle demande en cours.</p>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h3>Historique des Interventions</h3>
        <div class="card mt-3">
            <table class="table-basic">
                <tr class="table-header">
                    <th class="p-sm">Sujet</th>
                    <th class="p-sm">Statut</th>
                    <th class="p-sm">Date</th>
                </tr>
                <?php while($i = mysqli_fetch_assoc($interventions)): ?>
                <tr class="table-row">
                    <td class="p-sm fw-medium"><?php echo $i['nom']; ?></td>
                    <td class="p-sm">
                        <span class="badge badge-light-primary">
                            <?php echo $i['statut']; ?>
                        </span>
                    </td>
                    <td class="p-sm"><?php echo formatDate($i['date_creation']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </section>
</div>
