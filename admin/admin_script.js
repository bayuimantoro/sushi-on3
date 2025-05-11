document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.getElementById('mainContent');
    const appFooter = document.getElementById('appFooter'); // ID untuk footer
    const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
    const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');

    function applySidebarState() {
        if (!sidebar) return; // Jika sidebar tidak ada di halaman, jangan lakukan apa-apa

        const isCollapsed = sidebar.classList.contains('collapsed');
        if (mainContent) {
            if (isCollapsed) {
                mainContent.classList.add('sidebar-collapsed');
            } else {
                mainContent.classList.remove('sidebar-collapsed');
            }
        }
        if (appFooter) { // Juga toggle class untuk footer
             if (isCollapsed) {
                appFooter.classList.add('sidebar-collapsed');
            } else {
                appFooter.classList.remove('sidebar-collapsed');
            }
        }
    }

    function toggleSidebar() {
        if (!sidebar) return;
        sidebar.classList.toggle('collapsed');
        applySidebarState();
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    if (sidebarToggleDesktop) {
        sidebarToggleDesktop.addEventListener('click', toggleSidebar);
    }
    if (sidebarToggleMobile) {
        sidebarToggleMobile.addEventListener('click', toggleSidebar);
    }

    // Cek status sidebar dari localStorage saat halaman dimuat
    if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
         sidebar.classList.add('collapsed');
    }
    // Selalu panggil applySidebarState di awal untuk menyesuaikan konten dan footer
    // bahkan jika sidebar tidak ada (misalnya di halaman login yang mungkin tidak sengaja me-load script ini)
    applySidebarState();


    // Submenu Toggle
    const submenuToggles = document.querySelectorAll('.admin-sidebar .nav-link.has-submenu');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            // Allow navigation if the link is not just '#' or if it has a valid href for actual navigation
            if (this.getAttribute('href') === '#' || !this.getAttribute('href')) {
                event.preventDefault();
            }

            const submenu = this.nextElementSibling;

            if (submenu && submenu.classList.contains('submenu')) {
                // Close other open submenus if you want an accordion-style behavior
                if (!submenu.classList.contains('open')) { // Only close others if opening a new one
                    document.querySelectorAll('.admin-sidebar .submenu.open').forEach(otherSubmenu => {
                        if (otherSubmenu !== submenu) {
                            otherSubmenu.classList.remove('open');
                            if (otherSubmenu.previousElementSibling && otherSubmenu.previousElementSibling.classList.contains('has-submenu')) {
                              otherSubmenu.previousElementSibling.classList.remove('open');
                            }
                        }
                    });
                }
                // Toggle current submenu
                submenu.classList.toggle('open');
                this.classList.toggle('open'); // For arrow rotation
            }
        });
    });

    // (PHP Anda sudah menangani penambahan kelas 'active' dan 'open' berdasarkan $current_page,
    // jadi bagian JS untuk highlight dan open submenu berdasarkan URL mungkin tidak terlalu diperlukan
    // atau bisa menjadi fallback jika logika PHP tidak mencakup semua kasus)
    // Jika Anda ingin JS juga yang membuka submenu berdasarkan halaman aktif:
    const activeLinkInSubmenu = document.querySelector('.admin-sidebar .submenu .nav-link.active');
    if (activeLinkInSubmenu) {
        const parentSubmenu = activeLinkInSubmenu.closest('.submenu');
        if (parentSubmenu && !parentSubmenu.classList.contains('open')) {
            parentSubmenu.classList.add('open');
            const parentToggle = parentSubmenu.previousElementSibling;
            if (parentToggle && parentToggle.classList.contains('has-submenu') && !parentToggle.classList.contains('open')) {
                parentToggle.classList.add('open');
            }
        }
    }

});