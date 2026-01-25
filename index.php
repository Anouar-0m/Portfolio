<?php
require_once 'includes/config.php';
require_once 'includes/lang.php'; // ✅ FICHIER UNIQUE

$db = Database::getInstance()->getConnection();

// Traitement formulaire contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        setFlashMessage(__('msg_required'), 'error');
        header('Location: index.php?lang=' . $lang . '#contact');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage(__('msg_invalid_email'), 'error');
        header('Location: index.php?lang=' . $lang . '#contact');
        exit;
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO contacts (nom, email, sujet, message, lu, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        
        if ($stmt->execute([$nom, $email, $sujet, $message])) {
            setFlashMessage(__('msg_success'), 'success');
            header('Location: index.php?lang=' . $lang . '#contact');
            exit;
        } else {
            setFlashMessage(__('msg_error'), 'error');
            header('Location: index.php?lang=' . $lang . '#contact');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erreur contact : " . $e->getMessage());
        setFlashMessage(__('msg_tech_error'), 'error');
        header('Location: index.php?lang=' . $lang . '#contact');
        exit;
    }
}

$stmt = $db->prepare("SELECT * FROM projets WHERE visible = 1 ORDER BY ordre ASC");
$stmt->execute();
$projets = $stmt->fetchAll();

$stmt = $db->query("SELECT cle, valeur FROM parametres");
$params = [];
while ($row = $stmt->fetch()) {
    $params[$row['cle']] = $row['valeur'];
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($params['site_nom'] ?? 'Anouar Omari') ?> - Portfolio</title>
    
    <!-- SEO Multilingue -->
    <link rel="alternate" hreflang="fr" href="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?lang=fr' ?>">
    <link rel="alternate" hreflang="en" href="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?lang=en' ?>">
    <link rel="alternate" hreflang="x-default" href="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/index.php?lang=fr' ?>">
    
    <link rel="icon" type="image/png" href="favicon/favicon.webp">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- BOUTON TOGGLE THÈME -->
<button class="theme-toggle" id="themeToggle" aria-label="Changer de thème">
    <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <!-- Icône Soleil (mode clair) -->
        <path class="sun" d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.758 17.303a.75.75 0 00-1.061-1.06l-1.591 1.59a.75.75 0 001.06 1.061l1.591-1.59zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.697 7.757a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 00-1.061 1.06l1.59 1.591z"/>
        <!-- Icône Lune (mode sombre) - cachée par défaut -->
        <path class="moon" style="display:none;" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z"/>
    </svg>
</button>

    <a href="#accueil" class="scroll-top-link" aria-label="<?= __('nav_home') ?>">↑</a>
    
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="#accueil" class="brand">
               <img src="uploads/logo.png" 
                    srcset="uploads/logo@2x.png" 
                    alt="Anouar Omari" 
                    class="brand-logo">
            </a>
            
            <input type="checkbox" id="menu-toggle">
            
            <label for="menu-toggle" class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </label>
            
            <ul>
                <li><a href="#accueil"><?= __('nav_home') ?></a></li>
                <li><a href="#apropos"><?= __('nav_about') ?></a></li>
                <li><a href="#competences"><?= __('nav_skills') ?></a></li>
                <li><a href="#projets"><?= __('nav_projects') ?></a></li>
                <li><a href="#formation"><?= __('nav_education') ?></a></li>
                <li><a href="#contact"><?= __('nav_contact') ?></a></li>
                
                <!-- ✅ SÉLECTEUR LANGUE SIMPLE -->
                <li class="lang-switcher">
                    <div class="lang-dropdown">
                        <button class="lang-btn" type="button">
                            <?= $languages[$lang]['flag'] ?> <?= strtoupper($lang) ?>
                            <span class="arrow">▼</span>
                        </button>
                        <div class="lang-menu">
                            <?php foreach ($languages as $code => $info): ?>
                                <a href="<?= lang_url($code) ?>" 
                                   class="lang-option <?= $code === $lang ? 'active' : '' ?>">
                                    <?= $info['flag'] ?> <?= $info['name'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero -->
    <section id="accueil" class="hero">
        <div class="hero-content fade-in">
            <h1><?= __('hero_title') ?></h1>
            <h2><?= __('hero_subtitle') ?></h2>
            <a href="#contact" class="cta-button"><?= __('hero_cta') ?></a>
        </div>
    </section>

    <!-- About -->
    <section id="apropos" class="fade-in-section">
        <h2><?= __('about_title') ?></h2>
        <div class="about-content">
            <p><?= __('about_p1') ?></p>
            <p><?= __('about_p2') ?></p>
            <p><?= __('about_p3') ?></p>
        </div>
    </section>

    <!-- Skills -->
    <section id="competences" class="fade-in-section">
        <h2><?= __('skills_title') ?></h2>
        <div class="skills-grid">
            <div class="skill-category">
                <h3><?= __('skills_tech') ?></h3>
                <ul class="skill-list">
                    <li><?= __('skill_html') ?></li>
                    <li><?= __('skill_css') ?></li>
                    <li><?= __('skill_js') ?></li>
                    <li><?= __('skill_php') ?></li>
                    <li><?= __('skill_sql') ?></li>
                </ul>
            </div>
            <div class="skill-category">
                <h3><?= __('skills_soft') ?></h3>
                <ul class="skill-list">
                    <li><?= __('skill_teamwork') ?></li>
                    <li><?= __('skill_project') ?></li>
                    <li><?= __('skill_solving') ?></li>
                    <li><?= __('skill_adapt') ?></li>
                    <li><?= __('skill_comm') ?></li>
                </ul>
            </div>
            <div class="skill-category">
                <h3><?= __('skills_languages') ?></h3>
                <ul class="skill-list">
                    <li><?= __('skill_french') ?></li>
                    <li><?= __('skill_english') ?></li>
                    <li><?= __('skill_arabic') ?></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Projects -->
    <section id="projets" class="fade-in-section">
        <h2><?= __('projects_title') ?></h2>
        <div class="projects-grid">
            <?php foreach ($projets as $projet): ?>
            <div class="project-card">
                <div class="project-image" style="<?= $projet['image'] ? 'background-image: url(uploads/' . escape($projet['image']) . '); background-size: cover; background-position: center;' : '' ?>">
                    <?php if (!$projet['image']): ?>
                    <span style="font-size: 3rem;">📁</span>
                    <?php endif; ?>
                </div>
                <div class="project-content">
                    <h3><?= escape($projet['titre']) ?></h3>
                    <p><?= escape($projet['description']) ?></p>
                    <div class="project-tags">
                        <?php 
                        $techs = explode(',', $projet['technologies']);
                        foreach ($techs as $tech): 
                        ?>
                        <span class="tag"><?= escape(trim($tech)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-links">
                        <?php if ($projet['lien_demo']): ?>
                        <a href="<?= escape($projet['lien_demo']) ?>" target="_blank" class="project-link"><?= __('projects_demo') ?></a>
                        <?php endif; ?>
                        <?php if ($projet['lien_github']): ?>
                        <a href="<?= escape($projet['lien_github']) ?>" target="_blank" class="project-link"><?= __('projects_github') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Education -->
    <section id="formation" class="fade-in-section">
        <h2><?= __('education_title') ?></h2>
        <div class="education-timeline">
            <div class="education-item">
                <h3><?= __('edu_bachelor_title') ?></h3>
                <p class="year"><?= __('edu_bachelor_year') ?></p>
                <p><?= __('edu_bachelor_school') ?></p>
                <p><?= __('edu_bachelor_desc') ?></p>
            </div>
            <div class="education-item">
                <h3><?= __('edu_bac_title') ?></h3>
                <p class="year"><?= __('edu_bac_year') ?></p>
                <p><?= __('edu_bac_option') ?></p>
                <p><?= __('edu_bac_desc') ?></p>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="fade-in-section">
        <h2><?= __('contact_title') ?></h2>
        <div class="contact-content">
            <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?>">
                <?= escape($flashMessage['text']) ?>
            </div>
            <?php endif; ?>
            
            <p><?= __('contact_intro') ?></p>
            
            <form method="POST" action="index.php?lang=<?= $lang ?>#contact" class="contact-form">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom"><?= __('contact_name') ?> *</label>
                        <input type="text" id="nom" name="nom" required 
                               placeholder="<?= __('contact_placeholder_name') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><?= __('contact_email') ?> *</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="<?= __('contact_placeholder_email') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sujet"><?= __('contact_subject') ?> *</label>
                    <input type="text" id="sujet" name="sujet" required 
                           placeholder="<?= __('contact_placeholder_subject') ?>">
                </div>
                
                <div class="form-group">
                    <label for="message"><?= __('contact_message') ?> *</label>
                    <textarea id="message" name="message" rows="5" required 
                              placeholder="<?= __('contact_placeholder_message') ?>"></textarea>
                </div>
                
                <button type="submit" class="cta-button"><?= __('contact_send') ?></button>
            </form>
            
            <div class="contact-info">
                <div class="contact-item">
                    <strong><?= __('contact_email_label') ?></strong>
                    <a href="mailto:<?= escape($params['email'] ?? '') ?>"><?= escape($params['email'] ?? '') ?></a>
                </div>
            </div>
            
            <div class="social-links">
                <?php if (!empty($params['linkedin_url'])): ?>
                <a href="<?= escape($params['linkedin_url']) ?>" target="_blank">LinkedIn</a>
                <?php endif; ?>
                <?php if (!empty($params['github_url'])): ?>
                <a href="<?= escape($params['github_url']) ?>" target="_blank">GitHub</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> <?= escape($params['site_nom'] ?? 'Anouar Omari') ?>. <?= __('footer_rights') ?></p>
    </footer>
    <script>
// ========================================
// SYSTÈME DE THÈME CLAIR/SOMBRE
// ========================================

const themeToggle = document.getElementById('themeToggle');
const html = document.documentElement;
const sunIcon = document.querySelector('.sun');
const moonIcon = document.querySelector('.moon');

// 🔍 Obtenir le thème préféré
function getPreferredTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        return savedTheme;
    }
    // Détection de la préférence système
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

// 🎨 Appliquer le thème
function setTheme(theme) {
    html.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Changer l'icône
    if (theme === 'dark') {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
    } else {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
    }
}

// 🔄 Toggle entre les thèmes
function toggleTheme() {
    const currentTheme = html.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Animation de rotation
    themeToggle.classList.add('rotating');
    setTimeout(() => themeToggle.classList.remove('rotating'), 500);
    
    setTheme(newTheme);
}

// 🚀 Initialisation au chargement
setTheme(getPreferredTheme());

// 🖱️ Événement clic
themeToggle.addEventListener('click', toggleTheme);

// 🎧 Écouter les changements de préférence système (optionnel)
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
        setTheme(e.matches ? 'dark' : 'light');
    }
});
</script>
</body>
</html>