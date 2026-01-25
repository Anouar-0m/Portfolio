<!-- Dashboard  admin -->
<?php
// LIGNE 1 : Démarrage de session
session_start();

// Configuration
require_once '../includes/config.php';

// Vérification simple
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// ✅ TIMEOUT 30 MINUTES (côté PHP - plus fiable que JavaScript)
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

$inactive_time = time() - $_SESSION['last_activity'];

// Si inactif depuis plus de 30 minutes (1800 secondes)
if ($inactive_time > 1800) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Mise à jour de l'activité
$_SESSION['last_activity'] = time();

// Le reste de votre code...

$db = Database::getInstance()->getConnection();

// Statistiques
$stats = [
    'projets' => $db->query("SELECT COUNT(*) FROM projets")->fetchColumn(),
    'projets_visibles' => $db->query("SELECT COUNT(*) FROM projets WHERE visible = 1")->fetchColumn(),
    'messages' => $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn(),
    'messages_non_lus' => $db->query("SELECT COUNT(*) FROM contacts WHERE lu = 0")->fetchColumn()
];

// Derniers messages
$stmt = $db->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$derniers_messages = $stmt->fetchAll();

// Derniers projets
$stmt = $db->query("SELECT * FROM projets ORDER BY created_at DESC LIMIT 5");
$derniers_projets = $stmt->fetchAll();

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('login.php');
}
$pageTitle   = 'Tableau de bord - Administration';
$pageHeading = 'Dashboard';
$activePage  = 'dashboard';

require_once 'includes/header.php';
?>

    
    <!-- Main Content -->
    <div class="admin-container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h2>👋 Bienvenue, <?= escape($_SESSION['admin_username']) ?> !</h2>
                <p>Voici un aperçu de votre portfolio</p>
            </div>
            <a href="../index.php" target="_blank" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                </svg>
                Voir le site
            </a>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <svg width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['projets'] ?></h3>
                    <p>Projets totaux</p>
                    <small><?= $stats['projets_visibles'] ?> visibles</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <svg width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?= $stats['messages'] ?></h3>
                    <p>Messages reçus</p>
                    <small><?= $stats['messages_non_lus'] ?> non lus</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <svg width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3><?= date('d/m/Y') ?></h3>
                    <p>Aujourd'hui</p>
                    <small><?= date('H:i') ?></small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <svg width="32" height="32" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Portfolio</h3>
                    <p>En ligne</p>
                    <small>Actif</small>
                </div>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Messages -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>
                        <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        Derniers messages
                    </h3>
                    <a href="messages.php" class="section-link">Voir tout →</a>
                </div>
                
                <?php if (empty($derniers_messages)): ?>
                <div class="empty-state">
                    <p>📭 Aucun message pour le moment</p>
                </div>
                <?php else: ?>
                <div class="message-list">
                    <?php foreach ($derniers_messages as $msg): ?>
                    <div class="message-item <?= $msg['lu'] ? '' : 'unread' ?>">
                        <div class="message-header">
                            <strong><?= escape($msg['nom']) ?></strong>
                            <?php if (!$msg['lu']): ?>
                            <span class="badge badge-primary">Nouveau</span>
                            <?php endif; ?>
                        </div>
                        <p class="message-subject"><?= escape($msg['sujet']) ?></p>
                        <small><?= date('d/m/Y à H:i', strtotime($msg['created_at'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Projects -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>
                        <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        Derniers projets
                    </h3>
                    <a href="projets.php" class="section-link">Gérer →</a>
                </div>
                
                <?php if (empty($derniers_projets)): ?>
                <div class="empty-state">
                    <p>📁 Aucun projet pour le moment</p>
                </div>
                <?php else: ?>
                <div class="article-list">
                    <?php foreach ($derniers_projets as $projet): ?>
                    <div class="article-item">
                        <div class="article-info">
                            <strong><?= escape($projet['titre']) ?></strong>
                            <small><?= escape(substr($projet['technologies'], 0, 30)) ?>...</small>
                        </div>
                        <div class="article-stats">
                            <span class="<?= $projet['visible'] ? 'badge badge-primary' : 'badge' ?>" style="<?= !$projet['visible'] ? 'background: #999;' : '' ?>">
                                <?= $projet['visible'] ? '✓' : '✗' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>🚀 Actions rapides</h3>
            <div class="action-buttons">
                <a href="projets.php?action=add" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                    Nouveau projet
                </a>
                <a href="messages.php" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    Voir les messages
                </a>
                <a href="parametres.php" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    Paramètres
                </a>
                <a href="../index.php" target="_blank" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                    </svg>
                    Voir le site
                </a>
            </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>