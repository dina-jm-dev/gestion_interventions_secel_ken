<div class="home-page">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-text">
            <h1>Gérez vos interventions techniques en toute <span>sérénité</span>.</h1>
            <p>SECEL est la plateforme n°1 pour le suivi, la planification et la facturation de vos interventions de maintenance et de sécurité.</p>
            <div class="hero-buttons">
                <?php if(!isset($_SESSION['id_utilisateur'])): ?>
                    <a href="index.php?page=login" class="btn btn-primary btn-hero">Se connecter</a>
                    <a href="index.php?page=register" class="btn btn-hero btn-hero-outline">Créer un compte</a>
                <?php else: ?>
                    <a href="index.php?page=dashboard" class="btn btn-primary btn-hero">Aller au Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/img/2103.i511.012.P.m009.c25.repair service set flat.jpg" alt="Service Maintenance">
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2>Pourquoi choisir SECEL ?</h2>
        <div class="features-grid">
            <div class="feature-card card-hover">
                <div class="feature-icon-wrapper">
                    <img src="assets/img/construction_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Intervention">
                </div>
                <h3>Suivi en temps réel</h3>
                <p>Suivez l'état de vos interventions instantanément depuis votre tableau de bord.</p>
            </div>
            <div class="feature-card card-hover">
                <div class="feature-icon-wrapper">
                    <img src="assets/img/engineering_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Technicien">
                </div>
                <h3>Équipe d'experts</h3>
                <p>Nos techniciens qualifiés sont affectés à vos missions selon leurs spécialités.</p>
            </div>
            <div class="feature-card card-hover">
                <div class="feature-icon-wrapper">
                    <img src="assets/img/receipt_long_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Facturation">
                </div>
                <h3>Facturation simplifiée</h3>
                <p>Recevez et payez vos factures directement en ligne de manière sécurisée.</p>
            </div>
        </div>
    </section>

    <!-- App Showcase -->
    <section class="showcase">
        <div class="showcase-image">
            <img src="assets/img/31986.jpg" alt="App interface">
        </div>
        <div class="showcase-text">
            <h2>Une interface pensée pour vous.</h2>
            <p>Que vous soyez administrateur, technicien ou client, SECEL vous offre une expérience fluide et intuitive pour gérer vos opérations quotidiennes.</p>
            <ul class="showcase-list">
                <li>
                    <img src="assets/img/home_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Icon">
                    Dashboard centralisé
                </li>
                <li>
                    <img src="assets/img/menu_book_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Icon">
                    Historique des rapports
                </li>
                <li>
                    <img src="assets/img/groups_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Icon">
                    Gestion collaborative
                </li>
            </ul>
        </div>
    </section>
</div>
