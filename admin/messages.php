<!-- message admin -->
<?php
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

$db = Database::getInstance()->getConnection();

// Actions
if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? null;
    
    if ($_GET['action'] === 'marquer_lu' && $id) {
        $stmt = $db->prepare("UPDATE contacts SET lu = 1 WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('Message marqué comme lu');
    } elseif ($_GET['action'] === 'marquer_non_lu' && $id) {
        $stmt = $db->prepare("UPDATE contacts SET lu = 0 WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('Message marqué comme non lu');
    } elseif ($_GET['action'] === 'supprimer' && $id) {
        $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('Message supprimé');
    } elseif ($_GET['action'] === 'marquer_tous_lu') {
        $db->query("UPDATE contacts SET lu = 1");
        setFlashMessage('Tous les messages ont été marqués comme lus');
    }
    
    // ✅ CORRECTION ICI
    header('Location: messages.php');
    exit;
}

// Récupération des messages
$filtre = $_GET['filtre'] ?? 'tous';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if ($filtre === 'non_lus') {
    $query .= " AND lu = 0";
} elseif ($filtre === 'lus') {
    $query .= " AND lu = 1";
}

if ($search) {
    $query .= " AND (nom LIKE ? OR email LIKE ? OR sujet LIKE ? OR message LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Statistiques
$stmt = $db->query("SELECT COUNT(*) FROM contacts WHERE lu = 0");
$nb_non_lus = $stmt->fetchColumn();

$flash = getFlashMessage();
$pageTitle   = 'Messages - Administration';
$pageHeading = 'Messages';
$activePage  = 'messages';

require_once 'includes/header.php';
?>

    
    <div class="container">
        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['text']) ?></div>
        <?php endif; ?>
        
        <div class="section">
            <div class="stats">
                <div class="stat-badge">
                    <strong><?= $nb_non_lus ?></strong>
                    <span>Non lus</span>
                </div>
                <div class="stat-badge">
                    <strong><?= count($messages) ?></strong>
                    <span>Total</span>
                </div>
            </div>
            
            <div class="filters">
                <a href="messages.php?filtre=tous" class="filter-btn <?= $filtre === 'tous' ? 'active' : '' ?>">
                    📬 Tous
                </a>
                <a href="messages.php?filtre=non_lus" class="filter-btn <?= $filtre === 'non_lus' ? 'active' : '' ?>">
                    🔔 Non lus (<?= $nb_non_lus ?>)
                </a>
                <a href="messages.php?filtre=lus" class="filter-btn <?= $filtre === 'lus' ? 'active' : '' ?>">
                    ✅ Lus
                </a>
                
                <div class="search-box">
                    <form method="GET">
                        <input type="hidden" name="filtre" value="<?= escape($filtre) ?>">
                        <input type="text" name="search" placeholder="🔍 Rechercher..." value="<?= escape($search) ?>">
                    </form>
                </div>
                
                <?php if ($nb_non_lus > 0): ?>
                <a href="?action=marquer_tous_lu" class="btn btn-success" onclick="return confirm('Marquer tous les messages comme lus ?')">
                    ✓ Tout marquer comme lu
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section">
            <?php if (empty($messages)): ?>
                <div class="no-messages">
                    <h2>📭 Aucun message</h2>
                    <p>Les messages de contact apparaîtront ici</p>
                </div>
            <?php else: ?>
                <div class="message-list">
                    <?php foreach ($messages as $msg): ?>
                    <div class="message-card <?= $msg['lu'] ? '' : 'unread' ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <h3><?= escape($msg['nom']) ?></h3>
                                <small>
                                    📧 <a href="mailto:<?= escape($msg['email']) ?>"><?= escape($msg['email']) ?></a> • 
                                    📅 <?= date('d/m/Y à H:i', strtotime($msg['created_at'])) ?>
                                </small>
                            </div>
                            <span class="message-status <?= $msg['lu'] ? 'status-read' : 'status-unread' ?>">
                                <?= $msg['lu'] ? '✓ Lu' : '🔔 Nouveau' ?>
                            </span>
                        </div>
                        
                        <div class="message-body">
                            <strong>Sujet :</strong> <?= escape($msg['sujet']) ?><br><br>
                            <?= nl2br(escape($msg['message'])) ?>
                        </div>
                        
                        <div class="message-actions">
                            <a href="mailto:<?= escape($msg['email']) ?>?subject=Re: <?= urlencode($msg['sujet']) ?>" class="btn">
                                📧 Répondre par email
                            </a>
                            
                            <?php if ($msg['lu']): ?>
                            <a href="?action=marquer_non_lu&id=<?= $msg['id'] ?>" class="btn btn-secondary">
                                ✖ Marquer non lu
                            </a>
                            <?php else: ?>
                            <a href="?action=marquer_lu&id=<?= $msg['id'] ?>" class="btn btn-success">
                                ✓ Marquer lu
                            </a>
                            <?php endif; ?>
                            
                            <a href="?action=supprimer&id=<?= $msg['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce message ?')">
                                🗑️ Supprimer
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>