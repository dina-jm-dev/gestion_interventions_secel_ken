<?php
checkAuth();
$role = $_SESSION['role'];
$id_user = $_SESSION['id_utilisateur'];

// -- ACTIONS ADMIN --
if ($role == 'admin') {
    // Transformer un besoin en intervention et affecter
    if (isset($_POST['create_intervention'])) {
        $id_client = clean($_POST['id_client']);
        $nom_inter = clean($_POST['nom_inter']);
        $id_tech = clean($_POST['id_tech']);
        $id_besoin = clean($_POST['id_besoin']);
        
        mysqli_begin_transaction($conn);
        try {
            // 1. Créer l'intervention
            $sql1 = "INSERT INTO interventions (id_client, nom, statut) VALUES ('$id_client', '$nom_inter', 'en_cours')";
            mysqli_query($conn, $sql1);
            $id_inter = mysqli_insert_id($conn);
            
            // 2. Affecter le technicien
            $sql2 = "INSERT INTO affectations (id_technicien, id_intervention, date_debut) VALUES ('$id_tech', '$id_inter', NOW())";
            mysqli_query($conn, $sql2);
            
            // 3. Supprimer le besoin (puisque traité)
            $sql3 = "DELETE FROM besoins WHERE id_besoin = '$id_besoin'";
            mysqli_query($conn, $sql3);
            
            mysqli_commit($conn);
            echo "<script>alert('Intervention créée et technicien affecté !');</script>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<script>alert('Erreur lors de la création');</script>";
        }
    }
}

// -- RECUPERATION DES DONNEES --
if ($role == 'admin') {
    $interventions = mysqli_query($conn, "SELECT i.*, u.nom as client_nom FROM interventions i JOIN utilisateurs u ON i.id_client = u.id_utilisateur");
    $besoins = mysqli_query($conn, "SELECT b.*, u.nom as client_nom FROM besoins b JOIN utilisateurs u ON b.id_client = u.id_utilisateur");
    $techs = mysqli_query($conn, "SELECT * FROM utilisateurs WHERE role = 'technicien' AND disponible = 1");
} elseif ($role == 'technicien') {
    $interventions = mysqli_query($conn, "SELECT i.*, u.nom as client_nom FROM interventions i JOIN utilisateurs u ON i.id_client = u.id_utilisateur JOIN affectations a ON i.id_intervention = a.id_intervention WHERE a.id_technicien = '$id_user'");
} else {
    $interventions = mysqli_query($conn, "SELECT * FROM interventions WHERE id_client = '$id_user'");
}
?>

<div class="interventions-page">
    <?php if ($role == 'admin' && mysqli_num_rows($besoins) > 0): ?>
        <section class="mb-5">
            <h4>Besoins Clients (Non traités)</h4>
            <div class="interventions-grid mt-3">
                <?php while($b = mysqli_fetch_assoc($besoins)): ?>
                    <div class="card">
                        <div class="fw-semibold text-primary"><?php echo $b['client_nom']; ?></div>
                        <p class="text-sm my-2"><?php echo $b['description']; ?></p>
                        <hr class="hr-basic">
                        <form method="POST">
                            <input type="hidden" name="id_client" value="<?php echo $b['id_client']; ?>">
                            <input type="hidden" name="id_besoin" value="<?php echo $b['id_besoin']; ?>">
                            <input type="text" name="nom_inter" placeholder="Nom de l'intervention" class="form-input mb-1" required>
                            <select name="id_tech" class="form-input mb-1">
                                <option value="">Choisir un technicien</option>
                                <?php mysqli_data_seek($techs, 0); while($t = mysqli_fetch_assoc($techs)): ?>
                                    <option value="<?php echo $t['id_utilisateur']; ?>"><?php echo $t['nom']; ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" name="create_intervention" class="btn btn-primary w-full">Affecter</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    <?php endif; ?>

    <section>
        <h4>Liste des Interventions</h4>
        <div class="card mt-3">
            <table class="table-basic">
                <tr class="table-header">
                    <th class="p-sm">ID</th>
                    <th class="p-sm">Client</th>
                    <th class="p-sm">Intervention</th>
                    <th class="p-sm">Statut</th>
                    <th class="p-sm">Action</th>
                </tr>
                <?php while($i = mysqli_fetch_assoc($interventions)): ?>
                <tr class="table-row">
                    <td class="p-sm">#<?php echo $i['id_intervention']; ?></td>
                    <td class="p-sm"><?php echo $i['client_nom'] ?? 'Inconnu'; ?></td>
                    <td class="p-sm fw-medium"><?php echo $i['nom']; ?></td>
                    <td class="p-sm">
                        <span class="badge badge-light-primary">
                            <?php echo $i['statut']; ?>
                        </span>
                    </td>
                    <td class="p-sm">
                        <?php if ($role == 'technicien' && $i['statut'] != 'termine'): ?>
                            <a href="index.php?page=tech_rapport&id=<?php echo $i['id_intervention']; ?>" class="btn btn-sm-gray">Faire Rapport</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php if (mysqli_num_rows($interventions) == 0): ?>
                <p class="empty-state-text">Aucune intervention enregistrée.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
