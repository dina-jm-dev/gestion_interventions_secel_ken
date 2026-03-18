<?php
ob_start();
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Routage simple
$page = $_GET['page'] ?? 'home';

// Gestion de la connexion via ?login
if (isset($_GET['login']) || $page == 'login') {
    $page = 'login';
}

// Liste des pages accessibles sans connexion
$public_pages = ['home', 'login', 'register'];

// Protection de l'accès
if (!isset($_SESSION['id_utilisateur']) && !in_array($page, $public_pages)) {
    header("Location: index.php?page=login");
    exit();
}

// Si l'utilisateur est déjà connecté et essaie d'aller sur login/register
if (isset($_SESSION['id_utilisateur']) && in_array($page, ['login', 'register'])) {
    header("Location: index.php?page=dashboard");
    exit();
}

$page_path = "pages/" . $page . ".php";

if (!file_exists($page_path)) {
    $page_path = "pages/home.php";
}
?>
<!DOCTYPE html> 
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECEL - Gestion des Interventions</title>
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Preloader -->
    <div id="loader-wrapper">
        <div class="loader-content">
            <h1 style="color: #2563eb; margin-bottom: 10px;">SECEL</h1>
            <div class="loader-bar">
                <div class="loader-progress" id="loader-progress"></div>
            </div>
        </div>
    </div>

    <!-- App Wrapper -->
    <div class="app-container" style="display: none;" id="app-main">
        <?php 
        // N'afficher la sidebar que si l'utilisateur est connecté et qu'on n'est pas sur la page home
        if (isset($_SESSION['id_utilisateur']) && $page !== 'home') {
            include 'includes/sidebar.php'; 
        }
        ?>
        
        <main class="main-content <?php echo (isset($_SESSION['id_utilisateur']) && $page !== 'home') ? 'with-sidebar' : ''; ?>">
            <?php 
            // Header inclus sur toutes les pages
            include 'includes/header.php'; 
            ?>
            
            <div id="page-content">
                <?php include $page_path; ?>
            </div>

            <?php include 'includes/footer.php'; ?>
        </main>
    </div>

    <!-- Scripts -->
    <script src="assets/js/vendor/gsap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
