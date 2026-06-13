// public/js/app.js

document.addEventListener('DOMContentLoaded', () => {
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
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
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
