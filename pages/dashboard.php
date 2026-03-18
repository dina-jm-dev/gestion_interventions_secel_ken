<?php
/**
 * Dashboard Centralisé SECEL
 * Fusionne les vues principales par rôle (Admin, Tech, Client)
 */
checkAuth();
$role = $_SESSION['role'];
$id_user = $_SESSION['id_utilisateur'];

// -- LOGIQUE ADMIN --
if ($role == 'admin') {
    // Statistiques
    $stats = [
        'attente' => $conn->query("SELECT COUNT(*) FROM interventions WHERE statut='attente'")->fetchColumn(),
        'en_cours' => $conn->query("SELECT COUNT(*) FROM interventions WHERE statut='en_cours'")->fetchColumn(),
        'techs' => $conn->query("SELECT COUNT(*) FROM utilisateurs WHERE role='technicien' AND disponible=1")->fetchColumn()
    ];

    // Besoins non traités
    $stmt = $conn->prepare("SELECT b.*, u.nom as client_nom FROM besoins b JOIN utilisateurs u ON b.id_client = u.id_utilisateur ORDER BY date_besoin DESC");
    $stmt->execute();
    $besoins = $stmt->fetchAll();

    // Interventions récentes
    $stmt = $conn->prepare("SELECT i.*, uc.nom as client_nom, ut.nom as tech_nom 
                           FROM interventions i 
                           JOIN utilisateurs uc ON i.id_client = uc.id_utilisateur 
                           LEFT JOIN affectations a ON i.id_intervention = a.id_intervention
                           LEFT JOIN utilisateurs ut ON a.id_technicien = ut.id_utilisateur
                           ORDER BY i.date_creation DESC LIMIT 10");
    $stmt->execute();
    $interventions = $stmt->fetchAll();

    // Techniciens pour l'affectation
    $stmt = $conn->prepare("SELECT id_utilisateur, nom FROM utilisateurs WHERE role='technicien' AND disponible=1");
    $stmt->execute();
    $techs = $stmt->fetchAll();

    // Traitement affectation (Ancienne logique Interventions)
    if (isset($_POST['create_intervention'])) {
        $id_client = $_POST['id_client'];
        $nom_inter = clean($_POST['nom_inter']);
        $id_tech = $_POST['id_tech'];
        $id_besoin = $_POST['id_besoin'];
        $lieu = clean($_POST['lieu']);
        
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("INSERT INTO interventions (id_client, nom, lieu, statut) VALUES (?, ?, ?, 'attente')");
            $stmt->execute([$id_client, $nom_inter, $lieu]);
            $id_inter = $conn->lastInsertId();
            
            $stmt = $conn->prepare("INSERT INTO affectations (id_technicien, id_intervention) VALUES (?, ?)");
            $stmt->execute([$id_tech, $id_inter]);
            
            $stmt = $conn->prepare("DELETE FROM besoins WHERE id_besoin = ?");
            $stmt->execute([$id_besoin]);
            
            $conn->commit();
            header("Location: index.php?page=dashboard&msg=success");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Erreur lors de l'affectation: " . $e->getMessage();
        }
    }

    // Validation Statut
    if (isset($_POST['valider_statut'])) {
        $id_i = $_POST['id_intervention'];
        // L'admin valide = statut final 'valide'
        $stmt = $conn->prepare("UPDATE interventions SET statut = 'valide' WHERE id_intervention = ?");
        $stmt->execute([$id_i]);
        header("Location: index.php?page=dashboard&msg=updated");
        exit();
    }
}

// -- LOGIQUE TECHNICIEN --
if ($role == 'technicien') {
    // Missions affectées
    $stmt = $conn->prepare("SELECT i.*, u.nom as client_nom 
                           FROM interventions i 
                           JOIN utilisateurs u ON i.id_client = u.id_utilisateur 
                           JOIN affectations a ON i.id_intervention = a.id_intervention 
                           WHERE a.id_technicien = ? AND i.statut != 'termine'
                           ORDER BY i.date_creation DESC");
    $stmt->execute([$id_user]);
    $missions = $stmt->fetchAll();

    // Mise à jour statut par le tech
    if (isset($_POST['update_status'])) {
        $id_i = $_POST['id_intervention'];
        $statut = $_POST['statut'];
        
        if ($statut == 'en_cours') {
            $date_debut = $_POST['date_debut'] ?? date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE interventions SET statut = 'en_cours' WHERE id_intervention = ?");
            $stmt->execute([$id_i]);
            $stmt2 = $conn->prepare("UPDATE affectations SET date_debut = ? WHERE id_intervention = ? AND id_technicien = ?");
            $stmt2->execute([$date_debut, $id_i, $id_user]);
        } elseif ($statut == 'termine') {
            $date_fin = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE interventions SET statut = 'termine' WHERE id_intervention = ?");
            $stmt->execute([$id_i]);
            $stmt2 = $conn->prepare("UPDATE affectations SET date_fin = ? WHERE id_intervention = ? AND id_technicien = ?");
            $stmt2->execute([$date_fin, $id_i, $id_user]);
        }
        
        header("Location: index.php?page=dashboard&msg=status_sent");
        exit();
    }
}

// -- LOGIQUE CLIENT --
if ($role == 'client') {
    // Ses demandes (besoins)
    $stmt = $conn->prepare("SELECT * FROM besoins WHERE id_client = ? ORDER BY date_besoin DESC");
    $stmt->execute([$id_user]);
    $mes_demandes = $stmt->fetchAll();

    // Ses interventions en cours
    $stmt = $conn->prepare("SELECT * FROM interventions WHERE id_client = ? AND statut != 'termine' ORDER BY date_creation DESC");
    $stmt->execute([$id_user]);
    $mes_interventions = $stmt->fetchAll();
}
?>

<div class="dashboard-page">
    <div class="dashboard-header">
        <h2>Tableau de Bord</h2>
        <p class="text-muted">Bienvenue, <strong><?php echo $_SESSION['nom']; ?></strong> (<?php echo ucfirst($role); ?>)</p>
    </div>

    <?php if ($role == 'admin'): ?>
        <!-- STATS ADMIN -->
        <div class="stats-grid">
            <div class="card stat-primary">
                <div class="stat-label">EN ATTENTE</div>
                <div class="stat-value text-primary"><?php echo $stats['attente']; ?></div>
            </div>
            <div class="card stat-success">
                <div class="stat-label">EN COURS</div>
                <div class="stat-value text-success"><?php echo $stats['en_cours']; ?></div>
            </div>
            <div class="card stat-warning">
                <div class="stat-label">TECHS DISPONIBLES</div>
                <div class="stat-value text-warning"><?php echo $stats['techs']; ?></div>
            </div>
        </div>

        <div class="dashboard-content">
            <!-- BESOINS A TRAITER -->
            <?php if (!empty($besoins)): ?>
            <section>
                <h3>Nouveaux Besoins Clients</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--space-md); margin-top: var(--space-md);">
                    <?php foreach ($besoins as $b): ?>
                        <div class="card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-sm);">
                                <div style="font-weight: 700; color: var(--primary);"><?php echo $b['client_nom']; ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo formatDate($b['date_besoin']); ?></div>
                            </div>
                            <p style="font-size: 0.9rem; margin-bottom: var(--space-md); min-height: 50px;"><?php echo $b['description']; ?></p>
                            <form method="POST" style="background: var(--bg-main); padding: var(--space-sm); border-radius: var(--radius-sm);">
                                <input type="hidden" name="id_client" value="<?php echo $b['id_client']; ?>">
                                <input type="hidden" name="id_besoin" value="<?php echo $b['id_besoin']; ?>">
                                <input type="hidden" name="lieu" value="<?php echo htmlspecialchars($b['lieu'] ?? ''); ?>">
                                <input type="text" name="nom_inter" placeholder="Titre de l'intervention" class="form-input" required style="margin-bottom: 8px; font-size: 0.85rem;">
                                <div style="display: flex; gap: 5px;">
                                    <select name="id_tech" class="form-input" required style="font-size: 0.85rem; padding: 5px;">
                                        <option value="">Choisir Technicien</option>
                                        <?php foreach ($techs as $t): ?>
                                            <option value="<?php echo $t['id_utilisateur']; ?>"><?php echo $t['nom']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="create_intervention" class="btn btn-primary" style="padding: 5px 15px;">Affecter</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- TOUTES LES INTERVENTIONS -->
            <section>
                <h3>Suivi des Interventions</h3>
                <div class="card table-wrapper">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Intervention</th>
                                <th>Technicien</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interventions as $i): ?>
                            <tr>
                                <td>#<?php echo $i['id_intervention']; ?></td>
                                <td><strong class="fw-bold"><?php echo $i['client_nom']; ?></strong></td>
                                <td><?php echo $i['nom']; ?></td>
                                <td><?php echo $i['tech_nom'] ?? '<span class="text-danger">Non affecté</span>'; ?></td>
                                <td>
                                    <?php 
                                        $badge_class = 'badge-info';
                                        if($i['statut'] == 'en_cours') $badge_class = 'badge-warning';
                                        if($i['statut'] == 'termine') $badge_class = 'badge-primary'; // Tech finished
                                        if($i['statut'] == 'valide') $badge_class = 'badge-success'; // Admin validated
                                        if($i['statut'] == 'annule') $badge_class = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($i['statut']); ?></span>
                                </td>
                                <td>
                                    <form method="POST" class="gap-1">
                                        <input type="hidden" name="id_intervention" value="<?php echo $i['id_intervention']; ?>">
                                        <?php if($i['statut'] == 'termine'): ?>
                                            <input type="hidden" name="valider_statut" value="1">
                                            <button type="submit" class="btn btn-primary action-btn action-btn-success">Valider</button>
                                        <?php elseif($i['statut'] == 'valide'): ?>
                                            <span class="text-small text-success">Validé ✅</span>
                                        <?php else: ?>
                                            <span class="text-small text-muted py-1">En cours tech</span>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    <?php elseif ($role == 'technicien'): ?>
        <!-- VUE TECHNICIEN -->
        <section>
            <h3>Mes Missions en cours</h3>
            <div class="mt-3">
                <?php if (empty($missions)): ?>
                    <div class="card empty-state">
                        Vous n'avez aucune mission active pour le moment.
                    </div>
                <?php else: ?>
                    <div class="tech-cards-grid">
                        <?php foreach ($missions as $m): ?>
                            <div class="card">
                                <div class="flex-between mb-3">
                                    <div class="fw-bold text-lg"><?php echo $m['nom']; ?></div>
                                    <span class="badge badge-warning"><?php echo ucfirst($m['statut']); ?></span>
                                </div>
                                <div class="mb-3">
                                    <div class="text-xs text-muted">CLIENT</div>
                                    <div class="fw-bold"><?php echo $m['client_nom']; ?></div>
                                </div>
                                <div class="gap-2">
                                    <form method="POST" class="flex-1">
                                        <input type="hidden" name="id_intervention" value="<?php echo $m['id_intervention']; ?>">
                                        
                                        <?php if ($m['statut'] == 'attente'): ?>
                                            <div class="mb-1">
                                                <label class="text-small">Début :</label>
                                                <input type="datetime-local" name="date_debut" class="form-input date-input" required>
                                            </div>
                                            <input type="hidden" name="statut" value="en_cours">
                                            <button type="submit" name="update_status" class="btn btn-primary w-full text-xs">Démarrer</button>
                                        <?php elseif ($m['statut'] == 'en_cours'): ?>
                                            <input type="hidden" name="statut" value="termine">
                                            <button type="submit" name="update_status" class="btn btn-primary action-btn-success w-full text-xs border-0">Terminer</button>
                                        <?php endif; ?>
                                        
                                    </form>
                                    <a href="index.php?page=tech_rapport&id=<?php echo $m['id_intervention']; ?>" class="btn bg-gray flex-1 text-xs">Rapport</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    <?php else: ?>
        <!-- VUE CLIENT -->
        <div class="dashboard-columns">
            <section>
                <h3>Suivi de mes demandes</h3>
                <div class="mt-3">
                    <?php if (empty($mes_demandes) && empty($mes_interventions)): ?>
                        <div class="card empty-state p-xl">
                            Vous n'avez aucune demande active.
                        </div>
                    <?php else: ?>
                        <!-- Interventions en cours -->
                        <?php foreach ($mes_interventions as $mi): ?>
                            <div class="card flex-between-center stat-primary">
                                <div>
                                    <div class="fw-bold"><?php echo $mi['nom']; ?></div>
                                    <div class="text-xs text-muted">Reçue le <?php echo formatDate($mi['date_creation']); ?></div>
                                </div>
                                <span class="badge badge-info"><?php echo ucfirst($mi['statut']); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <!-- Besoins en attente -->
                        <?php foreach ($mes_demandes as $md): ?>
                            <div class="card flex-between-center bg-light">
                                <div>
                                    <p class="m-0"><?php echo $md['description']; ?></p>
                                    <div class="text-xs text-muted">Envoyée le <?php echo formatDate($md['date_besoin']); ?></div>
                                </div>
                                <span class="badge">En attente d'expert</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <aside>
                <div class="card card-sidebar">
                    <img src="assets/img/maps_ugc_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Besoin Icon">
                    <h3>Nouveau Besoin ?</h3>
                    <p>Décrivez votre problème technique et un de nos experts interviendra rapidement.</p>
                    <a href="index.php?page=nouveau-besoin" class="btn bg-white w-full text-primary fw-bold">Créer une demande</a>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>

<script>
    gsap.from(".card", {
        y: 20,
        opacity: 0,
        duration: 0.4,
        stagger: 0.05,
        ease: "power2.out"
    });
</script>
