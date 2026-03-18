<?php
/**
 * Gestion des Techniciens - SECEL
 * Interface Administrateur uniquement
 */
checkAuth('admin');

$error = "";
$success = "";

// -- ACTIONS --

// 1. AJOUT
if (isset($_POST['add_tech'])) {
    $nom = clean($_POST['nom']);
    $email = clean($_POST['email']);
    $id_spec = $_POST['id_specialite'];
    $login = clean($_POST['login']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, login, mot_de_passe, role, id_specialite) VALUES (?, ?, ?, ?, 'technicien', ?)");
        $stmt->execute([$nom, $email, $login, $pass, $id_spec]);
        $success = "Technicien ajouté avec succès.";
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// 2. MODIFICATION
if (isset($_POST['edit_tech'])) {
    $id = $_POST['id_utilisateur'];
    $nom = clean($_POST['nom']);
    $email = clean($_POST['email']);
    $id_spec = $_POST['id_specialite'];
    $dispo = isset($_POST['disponible']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, email = ?, id_specialite = ?, disponible = ? WHERE id_utilisateur = ? AND role = 'technicien'");
        $stmt->execute([$nom, $email, $id_spec, $dispo, $id]);
        $success = "Technicien mis à jour.";
    } catch (PDOException $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// 3. SUPPRESSION
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ? AND role = 'technicien'");
        $stmt->execute([$id]);
        $success = "Technicien supprimé.";
    } catch (PDOException $e) {
        $error = "Erreur : Impossible de supprimer ce technicien (il peut avoir des interventions liées).";
    }
}

// -- RECUPERATION DONNEES --

// Techniciens
$stmt = $conn->query("SELECT u.*, s.libelle as specialite_nom 
                     FROM utilisateurs u 
                     LEFT JOIN specialites s ON u.id_specialite = s.id_specialite 
                     WHERE u.role = 'technicien'
                     ORDER BY u.nom ASC");
$techniciens = $stmt->fetchAll();

// Spécialités
$specialites = $conn->query("SELECT * FROM specialites ORDER BY libelle ASC")->fetchAll();
?>

<div class="techniciens-page">
    <div class="flex-between-center mb-4">
        <h3>Gestion des Techniciens</h3>
        <button class="btn btn-primary" onclick="toggleModal('modal-add')">
            <img src="assets/img/add_24dp_FFF_FILL0_wght400_GRAD0_opsz24.svg" class="icon-sm">
            Ajouter un Technicien
        </button>
    </div>

    <?php if($error): ?>
        <div class="card alert-danger border-0 p-2 text-center"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="card alert-success border-0 p-2 text-center"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card table-wrapper">
        <table class="w-full">
            <thead>
                <tr>
                    <th>Nom & Prénom</th>
                    <th>Email / Contact</th>
                    <th>Spécialité</th>
                    <th>Disponibilité</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($techniciens as $t): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo $t['nom']; ?></div>
                        <div class="text-xs text-muted"><?php echo $t['login']; ?></div>
                    </td>
                    <td><?php echo $t['email']; ?></td>
                    <td><span class="badge badge-info"><?php echo $t['specialite_nom'] ?? 'Général'; ?></span></td>
                    <td>
                        <?php if ($t['disponible']): ?>
                            <span class="text-success fw-bold">● Disponible</span>
                        <?php else: ?>
                            <span class="text-danger fw-bold">○ Occupé</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <button class="btn p-1" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($t)); ?>)">
                            <img src="assets/img/edit_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" class="icon-sm">
                        </button>
                        <a href="index.php?page=techniciens&delete=<?php echo $t['id_utilisateur']; ?>" class="btn p-1" onclick="return confirm('Supprimer ce technicien ?')">
                            <img src="assets/img/delete_24dp_EA3323_FILL0_wght400_GRAD0_opsz24.svg" class="icon-sm">
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajout -->
<div id="modal-add" class="modal-overlay">
    <div class="card modal-card">
        <h4>Nouveau Technicien</h4>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="nom" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Spécialité</label>
                <select name="id_specialite" class="form-input" required>
                    <?php foreach ($specialites as $s): ?>
                        <option value="<?php echo $s['id_specialite']; ?>"><?php echo $s['libelle']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid-2-cols gap-2">
                <div class="form-group">
                    <label class="form-label">Identifiant</label>
                    <input type="text" name="login" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
            </div>
            <div class="flex-end gap-2 mt-3">
                <button type="button" class="btn" onclick="toggleModal('modal-add')">Annuler</button>
                <button type="submit" name="add_tech" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="modal-overlay">
    <div class="card modal-card">
        <h4>Modifier Technicien</h4>
        <form method="POST" id="edit-form">
            <input type="hidden" name="id_utilisateur" id="edit-id">
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" name="nom" id="edit-nom" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit-email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Spécialité</label>
                <select name="id_specialite" id="edit-spec" class="form-input" required>
                    <?php foreach ($specialites as $s): ?>
                        <option value="<?php echo $s['id_specialite']; ?>"><?php echo $s['libelle']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group flex-between-center gap-2" style="justify-content: flex-start;">
                <input type="checkbox" name="disponible" id="edit-dispo" value="1">
                <label class="form-label m-0">Disponible pour interventions</label>
            </div>
            <div class="flex-end gap-2 mt-3">
                <button type="button" class="btn" onclick="toggleModal('modal-edit')">Annuler</button>
                <button type="submit" name="edit_tech" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        // On .modal-overlay, display can be toggled by JS. It works as inline style override.
        modal.style.display = (modal.style.display === 'block') ? 'none' : 'block';
        if (modal.style.display === 'block') {
            gsap.from(modal.querySelector('.card'), { y: -20, opacity: 0, duration: 0.3 });
        }
    }

    function openEditModal(tech) {
        document.getElementById('edit-id').value = tech.id_utilisateur;
        document.getElementById('edit-nom').value = tech.nom;
        document.getElementById('edit-email').value = tech.email;
        document.getElementById('edit-spec').value = tech.id_specialite;
        document.getElementById('edit-dispo').checked = tech.disponible == 1;
        toggleModal('modal-edit');
    }
</script>
