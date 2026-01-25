<!-- parametres admin -->
<?php
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// ✅ TIMEOUT 30 MINUTES
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

$inactive_time = time() - $_SESSION['last_activity'];

if ($inactive_time > 1800) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();

// ✅ DÉCONNEXION (déplacée AVANT les requêtes SQL)
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// ================================
// TRAITEMENT DU FORMULAIRE
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Traitement des paramètres généraux
    if (isset($_POST['save_settings'])) {
        $settings = [
            'site_nom' => trim($_POST['site_nom']),
            'email' => trim($_POST['email']),
            'linkedin_url' => trim($_POST['linkedin_url']),
            'github_url' => trim($_POST['github_url']),
            'telephone' => trim($_POST['telephone'])
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("UPDATE parametres SET valeur = ? WHERE cle = ?");
            $stmt->execute([$value, $key]);
        }
        
        setFlashMessage('✅ Paramètres mis à jour avec succès');
        header('Location: parametres.php');
        exit;
    }
    
    // Traitement changement de mot de passe
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $stmt = $db->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        if (!password_verify($old_password, $admin['password'])) {
            setFlashMessage('❌ Ancien mot de passe incorrect', 'error');
        } elseif ($new_password !== $confirm_password) {
            setFlashMessage('❌ Les nouveaux mots de passe ne correspondent pas', 'error');
        } elseif (strlen($new_password) < 6) {
            setFlashMessage('❌ Le mot de passe doit contenir au moins 6 caractères', 'error');
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['admin_id']]);
            setFlashMessage('✅ Mot de passe modifié avec succès');
        }
        
        header('Location: parametres.php');
        exit;
    }
}

// Récupération des paramètres
$stmt = $db->query("SELECT cle, valeur FROM parametres");
$params = [];
while ($row = $stmt->fetch()) {
    $params[$row['cle']] = $row['valeur'];
}

$flash = getFlashMessage();
$pageTitle   = 'Paramètres - Administration';
$pageHeading = 'Paramètres';
$activePage  = 'parametres';

require_once 'includes/header.php';
?>

    
    <!-- Main Content -->
    <div class="admin-container">
        <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?php if ($flash['type'] === 'success'): ?>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            <?php else: ?>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
            </svg>
            <?php endif; ?>
            <?= escape($flash['text']) ?>
        </div>
        <?php endif; ?>
        
        <div class="settings-grid">
            <!-- Paramètres généraux -->
            <div class="settings-section">
                <div class="section-header">
                    <h3>
                        <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                        Paramètres généraux
                    </h3>
                </div>
                
                <form method="POST" class="settings-form">
                    <input type="hidden" name="save_settings" value="1">
                    
                    <div class="form-group">
                        <label for="site_nom">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"/>
                            </svg>
                            Nom du site
                        </label>
                        <input type="text" id="site_nom" name="site_nom" value="<?= escape($params['site_nom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Email de contact
                        </label>
                        <input type="email" id="email" name="email" value="<?= escape($params['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            Téléphone
                        </label>
                        <input type="tel" id="telephone" name="telephone" value="<?= escape($params['telephone']) ?>" placeholder="+33 6 12 34 56 78">
                    </div>
                    
                    <div class="form-group">
                        <label for="linkedin_url">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                            </svg>
                            LinkedIn URL
                        </label>
                        <input type="url" id="linkedin_url" name="linkedin_url" value="<?= escape($params['linkedin_url']) ?>" placeholder="https://linkedin.com/in/...">
                    </div>
                    
                    <div class="form-group">
                        <label for="github_url">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                            </svg>
                            GitHub URL
                        </label>
                        <input type="url" id="github_url" name="github_url" value="<?= escape($params['github_url']) ?>" placeholder="https://github.com/...">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                        Enregistrer les modifications
                    </button>
                </form>
            </div>
            
            <!-- Sécurité -->
            <div class="settings-section">
                <div class="section-header">
                    <h3>
                        <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                        </svg>
                        Sécurité
                    </h3>
                </div>
                
                <form method="POST" class="settings-form">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="old_password">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z"/>
                            </svg>
                            Ancien mot de passe
                        </label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                            </svg>
                            Nouveau mot de passe
                        </label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small>Minimum 6 caractères</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                            </svg>
                            Confirmer le mot de passe
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                        </svg>
                        Changer le mot de passe
                    </button>
                </form>
                
                <div class="info-box">
                    <svg width="24" height="24" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                    </svg>
                    <div>
                        <strong>Conseils de sécurité</strong>
                        <ul>
                            <li>Utilisez un mot de passe fort</li>
                            <li>Ne partagez jamais vos identifiants</li>
                            <li>Déconnectez-vous après utilisation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>