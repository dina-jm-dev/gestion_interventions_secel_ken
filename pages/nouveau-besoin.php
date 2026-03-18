<?php
/**
 * Nouveau Besoin - SECEL
 * Interface Client
 */
checkAuth('client');

if (isset($_POST['add_besoin'])) {
    $desc = clean($_POST['description']);
    $lieu = clean($_POST['lieu']);
    $id_client = $_SESSION['id_utilisateur'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO besoins (id_client, lieu, description, date_besoin) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$id_client, $lieu, $desc]);
        header("Location: index.php?page=dashboard&msg=requested");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de l'envoi : " . $e->getMessage();
    }
}
?>

<div class="card form-page-card">
    <h3 class="mb-1">Exprimer un nouveau besoin</h3>
    <p class="form-page-desc">Détaillez votre problème technique ici. Nos experts prendront contact avec vous sous 24h.</p>

    <?php if(isset($error)): ?>
        <div class="alert-danger border-0 mb-3"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Lieu de l'intervention</label>
            <input type="text" name="lieu" class="form-input" placeholder="Ex: 12 Rue de Paris, Bâtiment A" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Que se passe-t-il ?</label>
            <textarea name="description" class="form-input" rows="6" placeholder="Décrivez précisément votre panne ou votre besoin (ex: Mon ordinateur ne s'allume plus depuis ce matin...)" required></textarea>
        </div>
        
        <div class="gap-2 mt-4">
            <a href="index.php?page=dashboard" class="btn bg-gray flex-1 text-center">Plus tard</a>
            <button type="submit" name="add_besoin" class="btn btn-primary flex-2">Envoyer ma demande</button>
        </div>
    </form>
</div>

<script>
    gsap.from(".card", { opacity: 0, y: 30, duration: 0.6, ease: "power3.out" });
</script>
