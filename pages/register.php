<?php
// On ne session_start pas ici, c'est fait dans index.php
require_once 'config/db.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $nom = clean($_POST['nom']);
    $email = clean($_POST['email']);
    $telephone = clean($_POST['telephone']);
    $login = clean($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($nom) || empty($email) || empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'email est invalide.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si le login ou l'email existe déjà
        $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateurs WHERE login = ? OR email = ?");
        $stmt->execute([$login, $email]);
        if ($stmt->fetch()) {
            $error = "L'identifiant ou l'email est déjà utilisé.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, telephone, login, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, 'client')");
            if ($stmt->execute([$nom, $email, $telephone, $login, $hashed_password])) {
                $success = "Compte créé avec succès ! Connectez-vous maintenant.";
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }
}
?>

<div class="auth-split-layout" id="auth-container">
    <div class="auth-image-side">
        <img src="assets/img/2103.i511.012.P.m009.c25.repair service set flat.jpg" alt="SECEL Inscription">
        <div class="auth-image-overlay">
            <h2>Rejoignez notre réseau technique.</h2>
            <p>Créez votre compte en quelques clics et soumettez vos besoins d'interventions instantanément.</p>
        </div>
    </div>
    <div class="auth-form-side">
        <div class="auth-logo-center">
            <img src="assets/img/logo_secel.png" alt="SECEL Logo">
        </div>
        <h3>Créer un compte Client</h3>
        <p class="auth-subtitle">Remplissez ce formulaire pour vous inscrire</p>
        
        <?php if($error): ?>
            <div class="alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert-success">
                <?php echo $success; ?>
                <div class="mt-2"><a href="index.php?login" class="btn btn-primary btn-block auth-btn">Se connecter</a></div>
            </div>
        <?php else: ?>
        <form method="POST">
            <div class="form-group mb-2">
                <label class="form-label">Nom complet</label>
                <input type="text" name="nom" class="form-input auth-input" required placeholder="Ex: Jean Dupont">
            </div>
            <div class="form-grid mb-2">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input auth-input" required placeholder="email@exemple.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-input auth-input" required placeholder="Ex: 0123456789">
                </div>
            </div>
            <div class="form-group mb-2">
                <label class="form-label">Identifiant de connexion</label>
                <input type="text" name="login" class="form-input auth-input" required placeholder="Ex: jean.dupont">
            </div>
            <div class="form-grid mb-3">
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input auth-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer</label>
                    <input type="password" name="confirm_password" class="form-input auth-input" required>
                </div>
            </div>
            <button type="submit" name="register" class="btn btn-primary btn-block auth-btn">Confirmer l'inscription</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            Déjà un compte ? <br><a href="index.php?login" class="auth-link">Connectez-vous ici</a>
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
