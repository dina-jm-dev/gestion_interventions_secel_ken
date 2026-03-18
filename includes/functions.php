<?php
/**
 * Fonctions globales SECEL
 */

// Sécurisation des entrées
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Vérification de la session
function checkAuth($role = null) {
    if (!isset($_SESSION['id_utilisateur'])) {
        // Redirection vers login si non connecté
        $path = (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'index.php?login' : '../index.php?login';
        header("Location: $path");
        exit();
    }
    
    if ($role && $_SESSION['role'] !== $role) {
        header("Location: index.php?error=unauthorized");
        exit();
    }
}

// Formatage de date
function formatDate($date) {
    return date('d/m/Y à H:i', strtotime($date));
}

// Formatage de montant en FCFA
function formatMoney($amount) {
    return number_format($amount, 0, ',', ' ') . ' FCFA';
}
?>
