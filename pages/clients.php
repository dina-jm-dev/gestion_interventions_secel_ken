<?php
/**
 * Gestion des Clients - SECEL
 * Interface Administrateur (Lecture seule)
 */
checkAuth('admin');

// Récupération des clients via PDO
try {
    $stmt = $conn->query("SELECT * FROM utilisateurs WHERE role = 'client' ORDER BY date_creation DESC");
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<div class="clients-page">
    <div class="flex-between-center mb-4">
        <h3>Registre des Clients</h3>
        <div class="text-sm text-muted">
            Nombre total : <strong><?php echo count($clients); ?></strong>
        </div>
    </div>

    <div class="card table-wrapper">
        <table class="w-full">
            <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date d'Inscription</th>
                    <th class="text-right">Rôle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo $c['nom']; ?></div>
                        <div class="text-xs text-muted">ID: #<?php echo $c['id_utilisateur']; ?></div>
                    </td>
                    <td><?php echo $c['email']; ?></td>
                    <td><?php echo $c['telephone'] ?? '<span class="text-gray-300">N/A</span>'; ?></td>
                    <td><?php echo formatDate($c['date_creation']); ?></td>
                    <td class="text-right">
                        <span class="badge badge-info">Client</span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="5" class="empty-state-text">
                        Aucun client enregistré dans la base.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
