<?php
// On ne session_start pas ici, c'est fait dans index.php
require_once 'config/db.php'; // Correction du chemin critique

$error = "";

if (isset($_POST['login'])) {
    $user_login = clean($_POST['user_login']);
    $user_pass = $_POST['user_pass'];

    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE login = ?");
    $stmt->execute([$user_login]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($user_pass, $user['mot_de_passe'])) {
            session_regenerate_id(true); // Sécurité de la session
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php?page=dashboard");
            exit();
        } elseif ($user_pass === $user['mot_de_passe']) {
            // Migration transparente pour les anciens mots de passe en clair
            $hashed = password_hash($user_pass, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?");
            $update->execute([$hashed, $user['id_utilisateur']]);
            
            session_regenerate_id(true);
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php?page=dashboard");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Utilisateur non trouvé.";
    }
}
?>

<div class="auth-split-layout" id="auth-container">
    <div class="auth-image-side">
        <img src="assets/img/31986.jpg" alt="SECEL Interventions">
        <div class="auth-image-overlay">
            <h2>Gérez vos interventions techniques efficacement.</h2>
            <p>La plateforme SECEL simplifie le suivi de vos missions et optimise la relation client-technicien.</p>
        </div>
    </div>
    <div class="auth-form-side">
        <div class="auth-logo-center">
            <img src="assets/img/logo_secel.png" alt="SECEL Logo">
        </div>
        <h3>Bon retour parmi nous</h3>
        <p class="auth-subtitle">Connectez-vous pour accéder à votre espace</p>
        
        <?php if($error): ?>
            <div class="alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group mb-3">
                <label class="form-label">Identifiant</label>
                <input type="text" name="user_login" class="form-input auth-input" required autofocus placeholder="Ex: admin">
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="user_pass" class="form-input auth-input" required placeholder="••••••••">
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-block auth-btn">Se connecter</button>
        </form>

        <div class="auth-footer">
            Pas encore de compte ? <br><a href="index.php?page=register" class="auth-link">Créer un profil Client</a>
        </div>
    </div>
</div>

<script>
    gsap.from("#auth-container", {
        y: 40,
        opacity: 0,
        duration: 0.8,
        ease: "power3.out"
    });
    gsap.from(".auth-image-overlay", {
        x: -30,
        opacity: 0,
        duration: 0.8,
        delay: 0.3,
        ease: "power2.out"
    });
    gsap.from(".auth-form-side", {
        x: 30,
        opacity: 0,
        duration: 0.8,
        delay: 0.2,
        ease: "power2.out"
    });
</script>
