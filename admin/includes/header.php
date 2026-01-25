<!-- header admin -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit;
}

$pageTitle = $pageTitle ?? 'Administration';
$pageHeading = $pageHeading ?? 'Dashboard';
$activePage = $activePage ?? '';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT COUNT(*) FROM contacts WHERE lu = 0");
$nb_messages_non_lus = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-brand">
                <div class="logo">AO</div>
                <h1><?= htmlspecialchars($pageHeading) ?></h1>
                
            </div>
            
            <nav class="admin-nav" id="mobileNav">
                <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Accueil
                </a>
                
                <a href="projets.php" class="<?= $activePage === 'projets' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                    Projets
                </a>
                
                <a href="messages.php" class="<?= $activePage === 'messages' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    Messages
                    <?php if ($nb_messages_non_lus > 0): ?>
                        <span class="nav-badge"><?= $nb_messages_non_lus ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="parametres.php" class="<?= $activePage === 'parametres' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    Paramètres
                </a>
                
                <a href="?logout=1" class="logout" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"/>
                    </svg>
                    Déconnexion
                </a>
            </nav>
        </div>
    </header>
    
    <div class="mobile-overlay" onclick="closeMobileMenu()"></div>