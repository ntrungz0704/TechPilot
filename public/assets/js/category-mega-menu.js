document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('categoryMenuToggle');
    const dropdown = document.getElementById('categoryMegaDropdown') || document.getElementById('categoryStaticMenu');
    const overlay = document.getElementById('categoryMenuOverlay');
    const mobileToggleBtn = document.getElementById('mobileMenuToggle');
    const mobileCloseBtn = document.getElementById('mobileDrawerClose');

    if (!dropdown) return;

    let isOpen = false;
    let hoverTimeout = null;
    let activeRow = null;
    let activePanelId = null;

    const sidebarRows = dropdown.querySelectorAll('.category-sidebar__row');
    const sidebarItems = dropdown.querySelectorAll('.category-sidebar__item');
    const megaPanels = dropdown.querySelectorAll('.category-mega__panel');

    // 1. OPEN / CLOSE DROPDOWN (DESKTOP & MOBILE)
    function openMenu() {
        isOpen = true;

        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'true');
            toggleBtn.classList.add('is-active');
        }

        if (overlay) {
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
        }

        if (!dropdown.classList.contains('is-static')) {
            dropdown.hidden = false;
            dropdown.setAttribute('aria-hidden', 'false');
            if (window.innerWidth <= 767) {
                dropdown.classList.add('is-mobile-open');
            }
            document.body.classList.add('category-scroll-locked');
        } else {
            dropdown.classList.add('is-highlighted');
        }

        // Activate first panel by default if none active
        if (sidebarItems.length > 0 && !activeRow) {
            activatePanel(sidebarItems[0]);
        }
    }

    function closeMenu() {
        isOpen = false;

        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.classList.remove('is-active');
        }

        if (overlay) {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
        }

        if (!dropdown.classList.contains('is-static')) {
            dropdown.hidden = true;
            dropdown.setAttribute('aria-hidden', 'true');
            dropdown.classList.remove('is-mobile-open');
            document.body.classList.remove('category-scroll-locked');
        } else {
            dropdown.classList.remove('is-highlighted');
        }

        deactivateAll();
    }

    function toggleMenu() {
        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        });
    }

    if (mobileToggleBtn) {
        mobileToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openMenu();
        });
    }

    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', closeMenu);
    }

    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    // Đóng khi click ngoài dropdown
    document.addEventListener('click', (e) => {
        if (isOpen && !dropdown.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target)) && (!mobileToggleBtn || !mobileToggleBtn.contains(e.target))) {
            closeMenu();
        }
    });

    // Keyboard support: Escape key closes menu and restores focus
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) {
            closeMenu();
            if (toggleBtn) toggleBtn.focus();
        }
    });

    // 2. STATE MACHINE HOVER & FOCUS CHO SIDEBAR & MEGA PANEL
    function activatePanel(itemEl) {
        if (!itemEl) return;
        const targetId = itemEl.getAttribute('data-panel-id');
        if (targetId === activePanelId) return;

        const rowEl = itemEl.closest('.category-sidebar__row') || itemEl;

        // Remove active from previous
        if (activeRow) {
            activeRow.classList.remove('is-active');
        }
        sidebarRows.forEach(r => r.classList.remove('is-active'));
        megaPanels.forEach(p => {
            p.classList.remove('is-active');
            p.setAttribute('aria-hidden', 'true');
        });

        // Set active for new
        activeRow = rowEl;
        activePanelId = targetId;
        activeRow.classList.add('is-active');

        const targetPanel = document.getElementById(targetId);
        if (targetPanel) {
            targetPanel.classList.add('is-active');
            targetPanel.setAttribute('aria-hidden', 'false');
        }

        dropdown.classList.add('has-active-panel');
    }

    function deactivateAll() {
        if (activeRow) {
            activeRow.classList.remove('is-active');
        }
        sidebarRows.forEach(r => r.classList.remove('is-active'));
        megaPanels.forEach(p => {
            p.classList.remove('is-active');
            p.setAttribute('aria-hidden', 'true');
        });
        activeRow = null;
        activePanelId = null;
        dropdown.classList.remove('has-active-panel');
    }

    if (dropdown.classList.contains('is-static')) {
        dropdown.addEventListener('mouseleave', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            deactivateAll();
        });
    }

    sidebarRows.forEach(row => {
        const item = row.querySelector('.category-sidebar__item');
        const accordionBtn = row.querySelector('.category-mobile-accordion-toggle');

        if (item) {
            item.addEventListener('mouseenter', () => {
                if (window.innerWidth > 767) {
                    if (hoverTimeout) clearTimeout(hoverTimeout);
                    hoverTimeout = setTimeout(() => {
                        activatePanel(item);
                    }, 80);
                }
            });

            item.addEventListener('focus', () => {
                activatePanel(item);
            });
        }

        if (accordionBtn) {
            accordionBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isExpanded = accordionBtn.getAttribute('aria-expanded') === 'true';
                accordionBtn.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                row.classList.toggle('is-accordion-open', !isExpanded);
                if (item) activatePanel(item);
            });
        }
    });

    // Prevent closing when mouse enters mega panel
    const megaContainer = dropdown.querySelector('.category-dropdown__mega');
    if (megaContainer) {
        megaContainer.addEventListener('mouseenter', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
        });
    }
});