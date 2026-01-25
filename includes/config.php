<?php
/**
 * ================================
 * CONFIGURATION PRINCIPALE
 * Portfolio Anouar Omari
 * ================================
 */

// ================================
// PARAMÈTRES DE CONNEXION BDD
// ================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'u190435350_Portfolio');
define('DB_USER', 'u190435350_AnouarOmari');
define('DB_PASS', '1947Samcroo@');
define('DB_CHARSET', 'utf8mb4');

// ================================
// PARAMÈTRES DU SITE
// ================================
define('SITE_URL', 'https://anouarom.fr');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ================================
// SÉCURITÉ & SESSION
// ================================
define('SESSION_NAME', 'PORTFOLIO_SESSION');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 1800); // 30 minutes en secondes

// ================================
// DÉMARRAGE DE LA SESSION SÉCURISÉE
// ================================
if (session_status() === PHP_SESSION_NONE) {
    // Configuration de sécurité
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    ini_set('session.cookie_lifetime', 0); // Expire à la fermeture du navigateur
    ini_set('session.cookie_httponly', 1); // Protection XSS
    ini_set('session.use_only_cookies', 1); // Sécurité renforcée
    
    session_name(SESSION_NAME);
    session_start();
    
    // Initialisation de la session
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
        $_SESSION['created'] = time();
        $_SESSION['last_activity'] = time();
    }
    
    // Régénération périodique de l'ID de session (toutes les 10 minutes)
    if (isset($_SESSION['created']) && (time() - $_SESSION['created'] > 600)) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// ================================
// GESTION DES REQUÊTES AJAX
// ================================

// 💓 HEARTBEAT - Maintenir la session active
if (isset($_GET['heartbeat']) && $_GET['heartbeat'] === '1') {
    header('Content-Type: application/json');
    
    if (isset($_SESSION['admin_id'])) {
        $_SESSION['last_activity'] = time();
        $_SESSION['last_heartbeat'] = time();
        echo json_encode([
            'status' => 'ok',
            'logged_in' => true,
            'timestamp' => time()
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'logged_in' => false,
            'message' => 'Non authentifié'
        ]);
    }
    exit;
}

// 🚪 AUTO LOGOUT - Déconnexion automatique à la fermeture
if (isset($_GET['auto_logout']) && $_GET['auto_logout'] === '1') {
    // Vérifier le dernier heartbeat (si > 10 secondes, on déconnecte)
    $lastHeartbeat = $_SESSION['last_heartbeat'] ?? 0;
    $timeSinceHeartbeat = time() - $lastHeartbeat;
    
    if ($timeSinceHeartbeat > 10 || !isset($_SESSION['admin_id'])) {
        // Destruction complète de la session
        session_unset();
        session_destroy();
        
        // Suppression du cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }
    
    http_response_code(204); // No Content
    exit;
}

// 🔍 CHECK SESSION - Vérifier l'état de la session
if (isset($_GET['check_session']) && $_GET['check_session'] === '1') {
    header('Content-Type: application/json');
    
    $loggedIn = isset($_SESSION['admin_id']);
    
    // Vérifier le timeout d'inactivité
    if ($loggedIn && isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_TIMEOUT) {
            $loggedIn = false;
            session_unset();
            session_destroy();
        }
    }
    
    // Vérifier le dernier heartbeat
    if ($loggedIn && isset($_SESSION['last_heartbeat'])) {
        $timeSinceHeartbeat = time() - $_SESSION['last_heartbeat'];
        
        // Si pas de heartbeat depuis 15 secondes, considérer comme déconnecté
        if ($timeSinceHeartbeat > 15) {
            $loggedIn = false;
            session_unset();
            session_destroy();
        }
    }
    
    echo json_encode([
        'logged_in' => $loggedIn,
        'timestamp' => time()
    ]);
    exit;
}

// ================================
// CLASSE DATABASE (SINGLETON)
// ================================
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion BDD : " . $e->getMessage());
            die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Empêcher le clonage
    private function __clone() {}
    
    // Empêcher la désérialisation
    public function __wakeup() {
        throw new Exception("Impossible de désérialiser un singleton");
    }
}

// ================================
// FONCTIONS UTILITAIRES
// ================================

/**
 * Échappe les caractères spéciaux HTML
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL et termine le script
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Requiert une authentification valide
 */
function requireLogin() {
    // Vérification du timeout d'inactivité
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            redirect('login.php?timeout=1');
        }
    }
    
    // Mise à jour de l'activité
    $_SESSION['last_activity'] = time();
    
    // Vérification de la connexion
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// ================================
// PROTECTION CSRF
// ================================

/**
 * Génère un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Vérifie un token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && 
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// ================================
// MESSAGES FLASH
// ================================

/**
 * Définit un message flash
 */
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Récupère et supprime le message flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type']
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

// ================================
// GESTION DES UPLOADS
// ================================

/**
 * Upload une image avec validation
 */
function uploadImage($file, $prefix = 'img_') {
    // Extensions autorisées
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Vérification de l'existence du fichier
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Aucun fichier sélectionné'];
    }
    
    // Vérification des erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }
    
    // Extraction et validation de l'extension
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return [
            'success' => false,
            'error' => 'Format non autorisé. Formats acceptés : ' . implode(', ', $allowed)
        ];
    }
    
    // Vérification de la taille (max 5 MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 5 MB)'];
    }
    
    // Vérification que c'est bien une image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'error' => 'Le fichier n\'est pas une image valide'];
    }
    
    // Création du répertoire si nécessaire
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    // Génération d'un nom unique
    $newFilename = $prefix . uniqid() . '.' . $ext;
    $destination = UPLOAD_DIR . $newFilename;
    
    // Déplacement du fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Optimisation de l'image (optionnel)
        // optimizeImage($destination, $ext);
        
        return ['success' => true, 'filename' => $newFilename];
    }
    
    return ['success' => false, 'error' => 'Erreur lors du déplacement du fichier'];
}

/**
 * Supprime une image
 */
function deleteImage($filename) {
    if (empty($filename)) {
        return false;
    }
    
    $path = UPLOAD_DIR . $filename;
    
    if (file_exists($path) && is_file($path)) {
        return unlink($path);
    }
    
    return false;
}

// ================================
// FONCTIONS UTILITAIRES DIVERSES
// ================================

/**
 * Génère un slug à partir d'un texte
 */
function slugify($text) {
    // Remplace les caractères non alphanumériques par des tirets
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Translittération
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Supprime les caractères non désirés
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Nettoie les tirets
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    
    // Conversion en minuscules
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

/**
 * Formate une date en français
 */
function formatDate($date, $format = 'd/m/Y à H:i') {
    return date($format, strtotime($date));
}

/**
 * Tronque un texte avec ellipse
 */
function truncate($text, $length = 100, $ellipsis = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $ellipsis;
}

/**
 * Génère un mot de passe aléatoire
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
}

// ================================
// CONFIGURATION PHP
// ================================

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Affichage des erreurs (désactiver en production)
if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
}

// ================================
// LOGGING (optionnel)
// ================================

/**
 * Log une action admin
 */
function logAdminAction($action, $details = '') {
    if (!isset($_SESSION['admin_id'])) {
        return;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    try {
        $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        // La table n'existe peut-être pas encore, on ignore l'erreur
        error_log("Erreur log admin : " . $e->getMessage());
    }
}