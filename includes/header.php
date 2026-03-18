<header class="main-header">
    <div class="page-logo">
        <a href="index.php?page=home">
            <img src="assets/img/logo_secel.png" alt="SECEL Logo">
        </a>
    </div>
    
    <div class="user-profile">
        <?php if(isset($_SESSION['id_utilisateur'])): ?>
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['nom']; ?></div>
                <div class="user-role"><?php echo $_SESSION['role']; ?></div>
            </div>
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['nom'], 0, 1)); ?>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="index.php?login" class="btn btn-login-link">Connexion</a>
                <a href="index.php?page=register" class="btn btn-primary btn-register-link">S'inscrire</a>
            </div>
        <?php endif; ?>
    </div>
</header>
