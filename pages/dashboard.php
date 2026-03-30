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
        $description_besoin = clean($_POST['description_besoin'] ?? '');
        $date_fin_prevue = $_POST['date_fin_prevue'];
        
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("INSERT INTO interventions (id_client, nom, description_besoin, lieu, statut) VALUES (?, ?, ?, ?, 'attente')");
            $stmt->execute([$id_client, $nom_inter, $description_besoin, $lieu]);
            $id_inter = $conn->lastInsertId();
            
            $stmt = $conn->prepare("INSERT INTO affectations (id_technicien, id_intervention, date_fin_prevue) VALUES (?, ?, ?)");
            $stmt->execute([$id_tech, $id_inter, $date_fin_prevue]);
            
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

    // Validation Admin supprimee car remplacee par Client + Tech
}

// -- LOGIQUE TECHNICIEN --
if ($role == 'technicien') {
    // Missions affectées et en cours
    $stmt = $conn->prepare("SELECT i.*, u.nom as client_nom, a.date_fin_prevue, a.heure_arrivee, a.heure_depart 
                           FROM interventions i 
                           JOIN utilisateurs u ON i.id_client = u.id_utilisateur 
                           JOIN affectations a ON i.id_intervention = a.id_intervention 
                           WHERE a.id_technicien = ? AND i.validation_technicien = 0
                           ORDER BY i.date_creation DESC");
    $stmt->execute([$id_user]);
    $missions = $stmt->fetchAll();

    // Historique des missions (Terminées par le Tech)
    $stmt = $conn->prepare("SELECT i.*, u.nom as client_nom, a.heure_arrivee, a.heure_depart 
                           FROM interventions i 
                           JOIN utilisateurs u ON i.id_client = u.id_utilisateur 
                           JOIN affectations a ON i.id_intervention = a.id_intervention 
                           WHERE a.id_technicien = ? AND i.validation_technicien = 1
                           ORDER BY i.date_creation DESC");
    $stmt->execute([$id_user]);
    $historique_missions = $stmt->fetchAll();

    // Mise à jour statut par le tech
    if (isset($_POST['update_status'])) {
        $id_i = $_POST['id_intervention'];
        $statut = $_POST['statut'];
        
        if ($statut == 'en_cours') {
            $heure_arrivee = $_POST['heure_arrivee'] ?? date('H:i');
            $date_debut = date('Y-m-d H:i:s'); // On garde aussi la date de début technique
            $stmt = $conn->prepare("UPDATE interventions SET statut = 'en_cours' WHERE id_intervention = ?");
            $stmt->execute([$id_i]);
            $stmt2 = $conn->prepare("UPDATE affectations SET date_debut = ?, heure_arrivee = ? WHERE id_intervention = ? AND id_technicien = ?");
            $stmt2->execute([$date_debut, $heure_arrivee, $id_i, $id_user]);
        } elseif ($statut == 'termine') {
            $heure_depart = $_POST['heure_depart'] ?? date('H:i');
            $date_fin = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE interventions SET validation_technicien = 1 WHERE id_intervention = ?");
            $stmt->execute([$id_i]);
            $stmt2 = $conn->prepare("UPDATE affectations SET date_fin = ?, heure_depart = ? WHERE id_intervention = ? AND id_technicien = ?");
            $stmt2->execute([$date_fin, $heure_depart, $id_i, $id_user]);
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

    // Validation Client
    if (isset($_POST['valider_client_fin'])) {
        $id_i = $_POST['id_intervention'];
        $stmt = $conn->prepare("UPDATE interventions SET validation_client = 1, statut = 'termine' WHERE id_intervention = ? AND validation_technicien = 1");
        $stmt->execute([$id_i]);
        header("Location: index.php?page=dashboard&msg=validated");
        exit();
    }
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
                                <input type="hidden" name="description_besoin" value="<?php echo htmlspecialchars($b['description'] ?? ''); ?>">
                                <input type="text" name="nom_inter" placeholder="Titre de l'intervention" class="form-input" required style="margin-bottom: 8px; font-size: 0.85rem;">
                                <div style="margin-bottom: 8px;">
                                    <label class="text-small">Date fin prévue :</label>
                                    <input type="datetime-local" name="date_fin_prevue" required class="form-input" style="font-size: 0.85rem;">
                                </div>
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
                                        if($i['statut'] == 'termine' || $i['validation_client'] == 1) $badge_class = 'badge-success'; 
                                        if($i['statut'] == 'annule') $badge_class = 'badge-danger';
                                        ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $i['statut'])); ?></span>
                                    <?php if($i['validation_technicien'] == 1 && $i['validation_client'] == 0): ?>
                                       <br><span class="text-xs text-warning">Attente accord client</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Plus de validation admin, automatique avec tech + client -->
                                    <span class="text-small text-muted py-1" style="display:block; margin-bottom: 5px;">Double validation</span>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="index.php?page=print_rapport&id=<?php echo $i['id_intervention']; ?>" target="_blank" class="btn bg-gray text-xs action-btn" style="padding: 4px; display:flex; justify-content:center; align-items:center;" title="Rapport"><i class="fas fa-file-alt"></i> Rap.</a>
                                        <a href="index.php?page=print_facture&id=<?php echo $i['id_intervention']; ?>" target="_blank" class="btn bg-gray text-xs action-btn" style="padding: 4px; display:flex; justify-content:center; align-items:center;" title="Facture"><i class="fas fa-file-invoice-dollar"></i> Fac.</a>
                                    </div>
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
                                    <div class="text-xs mt-1"><strong>Lieu :</strong> <?php echo $m['lieu']; ?></div>
                                    <div class="text-xs"><strong>Besoin :</strong> <?php echo htmlspecialchars($m['description_besoin']); ?></div>
                                    <hr style="margin: 8px 0; border: none; border-top: 1px solid var(--border-color);">
                                    <div class="text-xs"><strong>Date fin prévue :</strong> <?php echo $m['date_fin_prevue'] ? date('d/m/Y H:i', strtotime($m['date_fin_prevue'])) : 'N/C'; ?></div>
                                </div>
                                <div class="gap-2">
                                    <form method="POST" class="flex-1" style="display:flex; flex-direction:column; gap:5px;">
                                        <input type="hidden" name="id_intervention" value="<?php echo $m['id_intervention']; ?>">
                                        
                                        <?php if ($m['statut'] == 'attente'): ?>
                                            <div class="mb-1">
                                                <label class="text-small">Relevé Heure Arrivée (HH:MM) :</label>
                                                <input type="time" name="heure_arrivee" class="form-input date-input" required>
                                            </div>
                                            <input type="hidden" name="statut" value="en_cours">
                                            <button type="submit" name="update_status" class="btn btn-primary w-full text-xs">Je suis sur place (Démarrer)</button>
                                        <?php elseif ($m['statut'] == 'en_cours'): ?>
                                            <div class="mb-1">
                                                <label class="text-small">Relevé Heure Départ (HH:MM) :</label>
                                                <input type="time" name="heure_depart" class="form-input date-input" required>
                                            </div>
                                            <input type="hidden" name="statut" value="termine">
                                            <button type="submit" name="update_status" class="btn action-btn-success w-full text-xs border-0">Signaler Départ (Terminer)</button>
                                        <?php endif; ?>
                                        
                                    </form>
                                    <div style="display:flex; gap: 5px; margin-top:5px;">
                                        <a href="index.php?page=tech_rapport&id=<?php echo $m['id_intervention']; ?>" class="btn bg-gray flex-1 text-xs text-center"><i class="fas fa-edit"></i> Rédiger</a>
                                        <a href="index.php?page=print_rapport&id=<?php echo $m['id_intervention']; ?>" target="_blank" class="btn bg-gray flex-1 text-xs text-center"><i class="fas fa-print"></i> Rapport</a>
                                        <a href="index.php?page=print_facture&id=<?php echo $m['id_intervention']; ?>" target="_blank" class="btn bg-gray flex-1 text-xs text-center"><i class="fas fa-print"></i> Facture</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- NOUVELLE SECTION HISTORIQUE TECH -->
        <section class="mt-5">
            <h3>Historique de mes interventions (Terminées)</h3>
            <div class="mt-3">
                <?php if (empty($historique_missions)): ?>
                    <div class="card empty-state">
                        Vous n'avez pas encore terminé d'interventions.
                    </div>
                <?php else: ?>
                    <div class="tech-cards-grid">
                        <?php foreach ($historique_missions as $m): ?>
                            <div class="card bg-light">
                                <div class="flex-between mb-3">
                                    <div class="fw-bold text-lg"><?php echo $m['nom']; ?></div>
                                    <span class="badge badge-success">Terminé</span>
                                </div>
                                <div class="mb-3">
                                    <div class="text-xs text-muted">CLIENT</div>
                                    <div class="fw-bold"><?php echo $m['client_nom']; ?></div>
                                    <hr style="margin: 8px 0; border: none; border-top: 1px solid var(--border-color);">
                                    <div class="text-xs"><strong>Heures Relevées :</strong> <?php echo $m['heure_arrivee'] ? substr($m['heure_arrivee'], 0, 5) : '--:--'; ?> à <?php echo $m['heure_depart'] ? substr($m['heure_depart'], 0, 5) : '--:--'; ?></div>
                                </div>
                                <div style="display:flex; gap: 5px;">
                                    <a href="index.php?page=print_rapport&id=<?php echo $m['id_intervention']; ?>" target="_blank" class="btn bg-gray flex-1 text-xs text-center"><i class="fas fa-print"></i> Rapport</a>
                                    <a href="index.php?page=print_facture&id=<?php echo $m['id_intervention']; ?>" target="_blank" class="btn bg-gray flex-1 text-xs text-center"><i class="fas fa-print"></i> Facture</a>
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
                        <!-- Interventions en cours et à valider -->
                        <?php foreach ($mes_interventions as $mi): ?>
                            <div class="card flex-between-center stat-primary <?php echo $mi['validation_technicien'] == 1 ? 'border-warning highlight-card' : ''; ?>" style="<?php echo $mi['validation_technicien'] == 1 ? 'border-left: 4px solid var(--warning);' : ''; ?>">
                                <div>
                                    <div class="fw-bold"><?php echo $mi['nom']; ?></div>
                                    <div class="text-xs text-muted">Enregistrée le <?php echo formatDate($mi['date_creation']); ?></div>
                                    <?php if ($mi['validation_technicien'] == 1): ?>
                                        <p class="text-small" style="color: var(--warning); font-weight: bold; margin-top: 4px;">Le technicien a signalé la fin de l'intervention. Veuillez confirmer.</p>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                                    <?php if ($mi['validation_technicien'] == 1): ?>
                                        <form method="POST">
                                            <input type="hidden" name="id_intervention" value="<?php echo $mi['id_intervention']; ?>">
                                            <button type="submit" name="valider_client_fin" class="btn action-btn-success text-xs">Valider (Clôturer)</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $mi['statut'])); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if ($mi['statut'] == 'termine' || $mi['validation_technicien'] == 1): ?>
                                        <a href="index.php?page=print_facture&id=<?php echo $mi['id_intervention']; ?>" target="_blank" class="btn bg-gray text-xs" style="padding: 4px 8px;"><i class="fas fa-print"></i> Imprimer Facture</a>
                                    <?php endif; ?>
                                </div>
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
