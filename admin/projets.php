<!-- projets admin -->
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

if (time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();

$db = Database::getInstance()->getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// ================================
// ✅ SUPPRESSION (TRAITÉE EN PREMIER)
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    
    // ✅ Vérification CSRF
    if (function_exists('verifyCSRFToken') && !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Erreur de sécurité', 'error');
        header('Location: projets.php');
        exit;
    }
    
    $del_id = (int)$_POST['id'];
    $stmt = $db->prepare("SELECT image FROM projets WHERE id = ?");
    $stmt->execute([$del_id]);
    $projet = $stmt->fetch();
    
    if ($projet && $projet['image'] && function_exists('deleteImage')) {
        deleteImage($projet['image']);
    }
    
    $stmt = $db->prepare("DELETE FROM projets WHERE id = ?");
    $stmt->execute([$del_id]);
    
    setFlashMessage('Projet supprimé avec succès');
    header('Location: projets.php');
    exit;
}

// ================================
// ✅ AJOUT / MODIFICATION
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titre']) && isset($_POST['description'])) {
    
    // ✅ Vérification CSRF
    if (function_exists('verifyCSRFToken') && !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Erreur de sécurité', 'error');
        header('Location: projets.php');
        exit;
    }
    
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $technologies = trim($_POST['technologies'] ?? '');
    $lien_demo = trim($_POST['lien_demo'] ?? '');
    $lien_github = trim($_POST['lien_github'] ?? '');
    $ordre = (int)($_POST['ordre'] ?? 0);
    $visible = isset($_POST['visible']) ? 1 : 0;
    
    // ✅ Gestion de l'image
    $image = null;
    if (!empty($_FILES['image']['name']) && function_exists('uploadImage')) {
        $upload = uploadImage($_FILES['image'], 'projet_');
        if ($upload['success']) {
            $image = $upload['filename'];
            // Supprimer l'ancienne image si modification
            if ($id && !empty($_POST['old_image']) && function_exists('deleteImage')) {
                deleteImage($_POST['old_image']);
            }
        }
    } else {
        $image = $_POST['old_image'] ?? null;
    }
    
    try {
        if ($id) {
            // ✅ MODIFICATION
            $stmt = $db->prepare("UPDATE projets SET titre=?, description=?, image=?, technologies=?, lien_demo=?, lien_github=?, ordre=?, visible=? WHERE id=?");
            $stmt->execute([$titre, $description, $image, $technologies, $lien_demo, $lien_github, $ordre, $visible, (int)$id]);
            setFlashMessage('Projet modifié avec succès');
        } else {
            // ✅ AJOUT
            $stmt = $db->prepare("INSERT INTO projets (titre, description, image, technologies, lien_demo, lien_github, ordre, visible) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $image, $technologies, $lien_demo, $lien_github, $ordre, $visible]);
            setFlashMessage('Projet ajouté avec succès');
        }
        
        header('Location: projets.php');
        exit;
        
    } catch (PDOException $e) {
        setFlashMessage('Erreur : ' . $e->getMessage(), 'error');
    }
}

// ================================
// RÉCUPÉRATION POUR MODIFICATION
// ================================
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM projets WHERE id = ?");
    $stmt->execute([(int)$id]);
    $projet = $stmt->fetch();
    
    if (!$projet) {
        setFlashMessage('Projet introuvable', 'error');
        header('Location: projets.php');
        exit;
    }
}

// ================================
// LISTE DES PROJETS
// ================================
$projets = $db->query("SELECT * FROM projets ORDER BY ordre ASC, id DESC")->fetchAll();

// Flash message
$flash = getFlashMessage();

$pageTitle = 'Gestion des projets';
$pageHeading = 'Projets';
$activePage = 'projets';
require_once 'includes/header.php';
?>

<div class="container">
    
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['text']) ?></div>
    <?php endif; ?>
    
    <?php if ($action === 'list'): ?>
    <div class="section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0; border: none;">Liste des projets (<?= count($projets) ?>)</h2>
            <a href="?action=add" class="btn">➕ Nouveau projet</a>
        </div>
        
        <?php if (empty($projets)): ?>
            <p style="text-align: center; color: #666; padding: 2rem;">Aucun projet pour le moment</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ordre</th>
                    <th>Titre</th>
                    <th>Technologies</th>
                    <th>Visible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projets as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= $p['ordre'] ?></td>
                    <td><strong><?= escape($p['titre']) ?></strong></td>
                    <td><?= escape($p['technologies']) ?></td>
                    <td>
                        <span class="badge badge-<?= $p['visible'] ? 'success' : 'danger' ?>">
                            <?= $p['visible'] ? '✓ Visible' : '✗ Masqué' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary">✏️ Modifier</a>
                        
                        <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Supprimer ce projet ?')">
                            <?php if (function_exists('generateCSRFToken')): ?>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <?php endif; ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-danger">🗑️ Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <?php else: ?>
    <div class="section">
        <h2><?= $action === 'add' ? '➕ Nouveau projet' : '✏️ Modifier le projet' ?></h2>
        
        <form method="POST" enctype="multipart/form-data">
            <?php if (function_exists('generateCSRFToken')): ?>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <?php endif; ?>
            <input type="hidden" name="old_image" value="<?= $projet['image'] ?? '' ?>">
            
            <div class="form-group">
                <label for="titre">Titre du projet *</label>
                <input type="text" id="titre" name="titre" value="<?= escape($projet['titre'] ?? '') ?>" required maxlength="200">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required maxlength="2000"><?= escape($projet['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="technologies">Technologies *</label>
                <input type="text" id="technologies" name="technologies" value="<?= escape($projet['technologies'] ?? '') ?>" placeholder="HTML, CSS, JavaScript, PHP" required maxlength="300">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="lien_demo">Lien démo</label>
                    <input type="url" id="lien_demo" name="lien_demo" value="<?= escape($projet['lien_demo'] ?? '') ?>" placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="lien_github">Lien GitHub</label>
                    <input type="url" id="lien_github" name="lien_github" value="<?= escape($projet['lien_github'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="image">Image du projet (optionnel)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if (!empty($projet['image'])): ?>
                        <small>Image actuelle : <?= escape($projet['image']) ?></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="ordre">Ordre d'affichage</label>
                    <input type="number" id="ordre" name="ordre" value="<?= $projet['ordre'] ?? 0 ?>" min="0" max="999">
                </div>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="visible" name="visible" <?= (!isset($projet) || $projet['visible']) ? 'checked' : '' ?>>
                    <label for="visible" style="margin: 0;">✓ Visible sur le portfolio</label>
                </div>
            </div>
            
            <div class="actions">
                <button type="submit" class="btn">💾 Enregistrer</button>
                <a href="projets.php" class="btn btn-secondary">✖ Annuler</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>