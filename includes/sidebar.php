<div class="sidebar">
    <div class="sidebar-logo">
        <a href="index.php?page=home">
            <img src="assets/img/logo_secel.png" alt="SECEL Logo">
        </a>
    </div>
    
    <nav class="sidebar-nav" style="flex-grow: 1;">
        <?php if (isset($_SESSION['role'])): ?>
            <!-- Accueil Dashboard (Commun à tous) -->
            <a href="index.php?page=dashboard" class="nav-item <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>">
                <img src="assets/img/home_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Tableau de bord">
                <span>Tableau de bord</span>
            </a>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <!-- Liens Admin -->
                <a href="index.php?page=techniciens" class="nav-item <?php echo ($_GET['page'] ?? '') == 'techniciens' ? 'active' : ''; ?>">
                    <img src="assets/img/engineering_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Techniciens">
                    <span>Techniciens</span>
                </a>
                <a href="index.php?page=factures" class="nav-item <?php echo ($_GET['page'] ?? '') == 'factures' ? 'active' : ''; ?>">
                    <img src="assets/img/receipt_long_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Factures">
                    <span>Factures</span>
                </a>
            <?php elseif ($_SESSION['role'] == 'technicien'): ?>
                <!-- Liens Tech -->
                <a href="index.php?page=rapports" class="nav-item <?php echo ($_GET['page'] ?? '') == 'rapports' ? 'active' : ''; ?>">
                    <img src="assets/img/menu_book_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Rapports">
                    <span>Rapports</span>
                </a>
            <?php elseif ($_SESSION['role'] == 'client'): ?>
                <!-- Liens Client -->
                <a href="index.php?page=nouveau-besoin" class="nav-item <?php echo ($_GET['page'] ?? '') == 'nouveau-besoin' ? 'active' : ''; ?>">
                    <img src="assets/img/maps_ugc_24dp_2563EB_FILL0_wght400_GRAD0_opsz24.svg" alt="Nouveau Besoin">
                    <span>Nouveau Besoin</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="config/logout.php" class="nav-item" style="background: #fca5a5; color: #ffffff;">
            <img src="assets/img/door_open_24dp_FFF_FILL0_wght400_GRAD0_opsz24.svg" alt="Déconnexion" style="filter: none;">
            <span>Déconnexion</span>
        </a>
    </div>
</div>
