<?php
// ========================================
// FICHIER : includes/lang.php
// Système multilingue ultra-simple
// ========================================

session_start();

// ✅ 1. DÉFINIR LES LANGUES DISPONIBLES
$languages = [
    'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
    'en' => ['name' => 'English', 'flag' => '🇬🇧']
];

// ✅ 2. DÉTECTER LA LANGUE ACTUELLE
if (isset($_GET['lang']) && isset($languages[$_GET['lang']])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + (30 * 24 * 60 * 60), '/');
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} elseif (isset($_COOKIE['lang'])) {
    $lang = $_COOKIE['lang'];
} else {
    $lang = 'fr'; // Langue par défaut
}

// ✅ 3. TOUTES LES TRADUCTIONS DANS UN SEUL TABLEAU
$translations = [
    'fr' => [
        // Navigation
        'nav_home' => 'Accueil',
        'nav_about' => 'À propos',
        'nav_skills' => 'Compétences',
        'nav_projects' => 'Projets',
        'nav_education' => 'Formation',
        'nav_contact' => 'Contact',
        
        // Hero
        'hero_title' => 'Anouar Omari',
        'hero_subtitle' => 'Étudiant en 1ère année Bachelor Développement Web<br>Digital Campus Paris',
        'hero_cta' => 'Me contacter',
        
        // À propos
        'about_title' => 'À propos',
        'about_p1' => 'Passionné par le développement web et les nouvelles technologies, je suis actuellement en première année de Bachelor Développement Web à Digital Campus Paris. Mon parcours m\'a permis d\'acquérir des bases solides en programmation et de développer un réel intérêt pour la création d\'interfaces utilisateur modernes et intuitives.',
        'about_p2' => 'Titulaire d\'un Baccalauréat International scientifique option français, j\'ai choisi de me spécialiser dans le développement web pour allier créativité et technique. Mon objectif est de devenir un développeur full-stack capable de concevoir des applications web performantes et innovantes.',
        'about_p3' => 'Toujours curieux d\'apprendre de nouvelles technologies et méthodologies, je suis à la recherche d\'opportunités pour mettre en pratique mes compétences et continuer à progresser dans ce domaine en constante évolution.',
        
        // Compétences
        'skills_title' => 'Compétences',
        'skills_tech' => 'Langages & Technologies',
        'skills_soft' => 'Compétences Transversales',
        'skills_languages' => 'Langues',
        'skill_html' => 'HTML5',
        'skill_css' => 'CSS3',
        'skill_js' => 'JavaScript',
        'skill_php' => 'PHP',
        'skill_sql' => 'SQL/MySQL',
        'skill_teamwork' => 'Travail en équipe',
        'skill_project' => 'Gestion de projet',
        'skill_solving' => 'Résolution de problèmes',
        'skill_adapt' => 'Adaptabilité',
        'skill_comm' => 'Communication',
        'skill_french' => 'Français - Courant',
        'skill_english' => 'Anglais - Intermédiaire',
        'skill_arabic' => 'Arabe - Langue maternelle',
        
        // Projets
        'projects_title' => 'Projets',
        'projects_demo' => 'Démo',
        'projects_github' => 'GitHub',
        
        // Formation
        'education_title' => 'Formation',
        'edu_bachelor_title' => 'Bachelor Développement Web',
        'edu_bachelor_year' => '2024 - Présent',
        'edu_bachelor_school' => 'Digital Campus Paris - 1ère année en cours',
        'edu_bachelor_desc' => 'Formation aux technologies web modernes : HTML, CSS, JavaScript, PHP, SQL et méthodologies de développement.',
        'edu_bac_title' => 'Baccalauréat International Scientifique',
        'edu_bac_year' => '2024',
        'edu_bac_option' => 'Option Français',
        'edu_bac_desc' => 'Formation scientifique internationale avec une spécialisation en français.',
        
        // Contact
        'contact_title' => 'Contact',
        'contact_intro' => 'N\'hésitez pas à me contacter pour toute opportunité ou collaboration !',
        'contact_name' => 'Nom complet',
        'contact_email' => 'Email',
        'contact_subject' => 'Sujet',
        'contact_message' => 'Message',
        'contact_send' => 'Envoyer le message',
        'contact_placeholder_name' => 'Sheedan Hyman',
        'contact_placeholder_email' => 'Sheedan-hyman@exemple.com',
        'contact_placeholder_subject' => 'Demande de collaboration',
        'contact_placeholder_message' => 'Votre message...',
        'contact_email_label' => '📧 Email',
        
        // Messages
        'msg_required' => 'Tous les champs sont obligatoires.',
        'msg_invalid_email' => 'Adresse email invalide.',
        'msg_success' => '✅ Votre message a été envoyé avec succès ! Je vous répondrai dans les plus brefs délais.',
        'msg_error' => 'Une erreur est survenue lors de l\'envoi du message.',
        'msg_tech_error' => 'Une erreur technique est survenue.',
        
        // Footer
        'footer_rights' => 'Tous droits réservés'
    ],
    
    'en' => [
        // Navigation
        'nav_home' => 'Home',
        'nav_about' => 'About',
        'nav_skills' => 'Skills',
        'nav_projects' => 'Projects',
        'nav_education' => 'Education',
        'nav_contact' => 'Contact',
        
        // Hero
        'hero_title' => 'Anouar Omari',
        'hero_subtitle' => '1st Year Web Development Bachelor Student<br>Digital Campus Paris',
        'hero_cta' => 'Contact me',
        
        // About
        'about_title' => 'About',
        'about_p1' => 'Passionate about web development and new technologies, I am currently in my first year of a Web Development Bachelor at Digital Campus Paris. My journey has allowed me to acquire solid programming foundations and develop a real interest in creating modern and intuitive user interfaces.',
        'about_p2' => 'Holder of an International Scientific Baccalaureate with French option, I chose to specialize in web development to combine creativity and technical skills. My goal is to become a full-stack developer capable of designing high-performance and innovative web applications.',
        'about_p3' => 'Always curious to learn new technologies and methodologies, I am looking for opportunities to put my skills into practice and continue to progress in this constantly evolving field.',
        
        // Skills
        'skills_title' => 'Skills',
        'skills_tech' => 'Languages & Technologies',
        'skills_soft' => 'Soft Skills',
        'skills_languages' => 'Languages',
        'skill_html' => 'HTML5',
        'skill_css' => 'CSS3',
        'skill_js' => 'JavaScript',
        'skill_php' => 'PHP',
        'skill_sql' => 'SQL/MySQL',
        'skill_teamwork' => 'Teamwork',
        'skill_project' => 'Project Management',
        'skill_solving' => 'Problem Solving',
        'skill_adapt' => 'Adaptability',
        'skill_comm' => 'Communication',
        'skill_french' => 'French - Fluent',
        'skill_english' => 'English - Intermediate',
        'skill_arabic' => 'Arabic - Native',
        
        // Projects
        'projects_title' => 'Projects',
        'projects_demo' => 'Demo',
        'projects_github' => 'GitHub',
        
        // Education
        'education_title' => 'Education',
        'edu_bachelor_title' => 'Web Development Bachelor',
        'edu_bachelor_year' => '2024 - Present',
        'edu_bachelor_school' => 'Digital Campus Paris - 1st year in progress',
        'edu_bachelor_desc' => 'Training in modern web technologies: HTML, CSS, JavaScript, PHP, SQL and development methodologies.',
        'edu_bac_title' => 'International Scientific Baccalaureate',
        'edu_bac_year' => '2024',
        'edu_bac_option' => 'French Option',
        'edu_bac_desc' => 'International scientific education with a specialization in French.',
        
        // Contact
        'contact_title' => 'Contact',
        'contact_intro' => 'Feel free to contact me for any opportunity or collaboration!',
        'contact_name' => 'Full name',
        'contact_email' => 'Email',
        'contact_subject' => 'Subject',
        'contact_message' => 'Message',
        'contact_send' => 'Send message',
        'contact_placeholder_name' => 'Sheedan hyman',
        'contact_placeholder_email' => 'Sheedan-hyman@example.com',
        'contact_placeholder_subject' => 'Collaboration request',
        'contact_placeholder_message' => 'Your message...',
        'contact_email_label' => '📧 Email',
        
        // Messages
        'msg_required' => 'All fields are required.',
        'msg_invalid_email' => 'Invalid email address.',
        'msg_success' => '✅ Your message has been sent successfully! I will reply to you as soon as possible.',
        'msg_error' => 'An error occurred while sending the message.',
        'msg_tech_error' => 'A technical error occurred.',
        
        // Footer
        'footer_rights' => 'All rights reserved'
    ]
];

// ✅ 4. FONCTION SIMPLE DE TRADUCTION
function __($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}

// ✅ 5. FONCTION POUR URL AVEC LANGUE
function lang_url($new_lang) {
    $url = $_SERVER['REQUEST_URI'];
    $url = preg_replace('/(\?|&)lang=[^&]*/', '', $url);
    $separator = strpos($url, '?') !== false ? '&' : '?';
    return $url . $separator . 'lang=' . $new_lang;
}