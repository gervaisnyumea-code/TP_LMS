/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// public/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    // Theme toggle
    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        const root = document.documentElement;
        const sunIcon = themeBtn.querySelector('.sun-icon');
        const moonIcon = themeBtn.querySelector('.moon-icon');

        function updateIcons(theme) {
            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }

        // Initialize icons based on current theme
        const currentTheme = root.getAttribute('data-theme') || 'light';
        updateIcons(currentTheme);

        themeBtn.addEventListener('click', () => {
            const newTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', newTheme);
            localStorage.setItem('lms_theme', newTheme);
            updateIcons(newTheme);
        });
    }

    // Sidebar toggle
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    const overlay = document.querySelector('.sidebar-overlay');

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
            overlay.classList.add('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 250);
        }, 5000);
    });

    // Dropdowns
    document.addEventListener('click', (e) => {

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

        const isDropdownButton = e.target.closest('.dropdown-toggle');
        if (!isDropdownButton && e.target.closest('.dropdown') != null) return;

        let currentDropdown;
        if (isDropdownButton) {
            currentDropdown = e.target.closest('.dropdown');
            currentDropdown.classList.toggle('active');
        }

        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            if (dropdown === currentDropdown) return;
            dropdown.classList.remove('active');
        });
    });

    // Tabs
    const tabItems = document.querySelectorAll('.tab-item');
    tabItems.forEach(item => {
        item.addEventListener('click', () => {
            const tabTarget = document.querySelector(item.dataset.target);
            const tabContainer = item.closest('.tabs').parentElement;

            // Remove active from all items and contents in this container
            item.closest('.tabs').querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            tabContainer.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active
            item.classList.add('active');
            if(tabTarget) tabTarget.classList.add('active');
        });
    });

    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(el => {
        if(el.tagName === 'FORM') {
            el.addEventListener('submit', (e) => {
                if (!confirm(el.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        } else {
            el.addEventListener('click', (e) => {
                if (!confirm(el.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Helper for AJAX requests
async function fetchAjax(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            }
        });
        if (!response.ok) throw new Error('Erreur reseau');
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, message: 'Erreur de communication avec le serveur.' };
    }
}
