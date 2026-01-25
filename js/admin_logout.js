/* ================================
   ADMIN JAVASCRIPT - Sans timeout (géré par PHP)
   Version optimisée 2025
   ================================ */

(function () {
    'use strict';

    console.log('✅ Admin JS chargé');

    // ================================
    // SYSTÈME DE NOTIFICATIONS
    // ================================
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `admin-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${getNotificationIcon(type)}</span>
                <span class="notification-message">${message}</span>
            </div>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.classList.add('show'), 10);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    function getNotificationIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    // Exposer la fonction globalement
    window.showNotification = showNotification;

    // Ajouter les styles pour les notifications
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .admin-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 15px;
                backdrop-filter: blur(20px);
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                opacity: 0;
                transform: translateX(400px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                min-width: 300px;
                border: 2px solid;
            }
            
            .admin-notification.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .admin-notification.success {
                background: rgba(16, 185, 129, 0.2);
                border-color: rgba(16, 185, 129, 0.5);
                color: #10b981;
            }
            
            .admin-notification.error {
                background: rgba(239, 68, 68, 0.2);
                border-color: rgba(239, 68, 68, 0.5);
                color: #ef4444;
            }
            
            .admin-notification.warning {
                background: rgba(245, 158, 11, 0.2);
                border-color: rgba(245, 158, 11, 0.5);
                color: #f59e0b;
            }
            
            .admin-notification.info {
                background: rgba(99, 102, 241, 0.2);
                border-color: rgba(99, 102, 241, 0.5);
                color: #6366f1;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 0.8rem;
            }
            
            .notification-icon {
                font-size: 1.5rem;
                font-weight: bold;
            }
            
            .notification-message {
                font-weight: 600;
            }

            @media (max-width: 768px) {
                .admin-notification {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                    min-width: auto;
                }
            }
        `;
        document.head.appendChild(style);
    }

    // ================================
    // GESTION DES FORMULAIRES
    // ================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>⏳</span> Traitement en cours...';
                
                // Rétablir le bouton après 10 secondes au cas où
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            }
        });
    });

    // ================================
    // CONFIRMATION AVANT SUPPRESSION
    // ================================
    const deleteButtons = document.querySelectorAll('a[href*="supprimer"], a[href*="delete"], button[name*="delete"], .btn-danger');
    deleteButtons.forEach(btn => {
        if (!btn.classList.contains('no-confirm')) {
            btn.addEventListener('click', function (e) {
                if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });

    // ================================
    // PREVIEW D'IMAGES
    // ================================
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    let preview = input.parentElement.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview';
                        input.parentElement.appendChild(preview);
                    }
                    preview.innerHTML = `
                        <img src="${event.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 15px; margin-top: 1rem; box-shadow: 0 5px 20px rgba(0,0,0,0.3);">
                        <p style="margin-top: 0.5rem; color: #94a3b8; font-size: 0.9rem;">${file.name} (${(file.size / 1024).toFixed(2)} KB)</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // ================================
    // MENU MOBILE
    // ================================
    const hamburger = document.querySelector('.mobile-menu-toggle');
    const overlay = document.querySelector('.mobile-overlay');
    const nav = document.querySelector('.admin-nav');
    const navLinks = document.querySelectorAll('.admin-nav a');

    if (hamburger && overlay && nav) {
        
        function toggleMenu() {
            const isOpen = nav.classList.contains('active');
            if (isOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        function openMenu() {
            nav.classList.add('active');
            overlay.classList.add('active');
            hamburger.classList.add('active');
            hamburger.innerHTML = '✕';
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            nav.classList.remove('active');
            overlay.classList.remove('active');
            hamburger.classList.remove('active');
            hamburger.innerHTML = '☰';
            document.body.style.overflow = '';
        }

        hamburger.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', closeMenu);

        // Fermer le menu quand on clique sur un lien
        navLinks.forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        // Fermer avec Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('active')) {
                closeMenu();
            }
        });

        console.log('📱 Menu mobile: actif');
    }

    // ================================
    // AUTO-DISMISS DES ALERTES
    // ================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'all 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 8000); // Disparaît après 8 secondes
    });

    // ================================
    // DÉTECTION DU MESSAGE TIMEOUT
    // ================================
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('timeout') === '1') {
        showNotification('⏰ Votre session a expiré après 30 minutes d\'inactivité', 'warning');
    }

    console.log('✅ Admin initialisé - Timeout géré par PHP');
})();