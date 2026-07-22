/**
 * TechPilot Category Mega Menu & Navigation Controller (V3 Final)
 */
document.addEventListener('DOMContentLoaded', () => {

    // Global Active Drawer State Tracker: null | 'mainNav' | 'categoryDrawer'
    let activeDrawer = null;
    let lastActiveTrigger = null;

    // DOM Elements
    const mainNavMenu = document.getElementById('mainNavMenu');
    const mobileMenuBtn = document.getElementById('mobileMenuToggle');
    const mainNavCloseBtn = document.getElementById('mobileDrawerClose');

    const categoryDropdown = document.getElementById('categoryMegaDropdown');
    const categoryStaticMenu = document.getElementById('categoryStaticMenu');

    const categoryTriggers = [
        document.getElementById('categoryMenuToggle'),
        document.getElementById('mobileCategoryToggle'),
        document.getElementById('mobileQuickCatAll'),
        document.getElementById('mobileBottomNavCats')
    ].filter(Boolean);

    const categoryOverlays = Array.from(document.querySelectorAll('.category-overlay'));
    const categoryCloseBtns = Array.from(document.querySelectorAll('.category-drawer-close'));

    // 1. ARIA Targets & Expanded State Synchronization
    categoryTriggers.forEach(trig => {
        trig.setAttribute('aria-controls', 'categoryMegaDropdown');
        trig.setAttribute('aria-expanded', 'false');
    });

    if (mobileMenuBtn) {
        mobileMenuBtn.setAttribute('aria-controls', 'mainNavMenu');
        mobileMenuBtn.setAttribute('aria-expanded', 'false');
    }

    // Helper: Body Scroll Lock
    function updateScrollLock() {
        if (activeDrawer) {
            document.body.classList.add('category-scroll-locked');
        } else {
            document.body.classList.remove('category-scroll-locked');
        }
    }

    // Helper: Overlays
    function setOverlaysVisible(visible) {
        categoryOverlays.forEach(ov => {
            if (visible) {
                ov.hidden = false;
                ov.setAttribute('aria-hidden', 'false');
            } else {
                ov.hidden = true;
                ov.setAttribute('aria-hidden', 'true');
            }
        });
    }

    // --- MAIN NAVIGATION DRAWER CONTROLLER ---
    function openMainNav(triggerEl) {
        if (activeDrawer === 'categoryDrawer') {
            closeCategoryDrawer(false);
        }

        activeDrawer = 'mainNav';
        lastActiveTrigger = triggerEl || mobileMenuBtn;

        if (mobileMenuBtn) {
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
        }

        if (mainNavMenu) {
            mainNavMenu.removeAttribute('inert');
            mainNavMenu.classList.add('is-mobile-open', 'is-active');
            mainNavMenu.setAttribute('aria-hidden', 'false');
        }

        setOverlaysVisible(true);
        updateScrollLock();

        if (mainNavCloseBtn) {
            mainNavCloseBtn.focus();
        }
    }

    function closeMainNav(restoreFocus = true) {
        if (!mainNavMenu) return;

        if (activeDrawer === 'mainNav') {
            activeDrawer = null;
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
        }

        mainNavMenu.classList.remove('is-mobile-open', 'is-active');
        mainNavMenu.setAttribute('aria-hidden', 'true');

        if (window.innerWidth <= 767) {
            mainNavMenu.setAttribute('inert', '');
        }

        setOverlaysVisible(false);
        updateScrollLock();

        if (restoreFocus && lastActiveTrigger && typeof lastActiveTrigger.focus === 'function') {
            lastActiveTrigger.focus();
        }
    }

    // --- CATEGORY DRAWER CONTROLLER ---
    function openCategoryDrawer(triggerEl) {
        if (activeDrawer === 'mainNav') {
            closeMainNav(false);
        }

        activeDrawer = 'categoryDrawer';
        lastActiveTrigger = triggerEl || categoryTriggers[0];

        categoryTriggers.forEach(trig => {
            trig.setAttribute('aria-expanded', 'true');
            trig.classList.add('is-active');
        });

        if (categoryDropdown) {
            categoryDropdown.removeAttribute('inert');
            categoryDropdown.hidden = false;
            categoryDropdown.setAttribute('aria-hidden', 'false');

            if (window.innerWidth <= 767) {
                categoryDropdown.classList.add('is-mobile-open');
            } else {
                categoryDropdown.classList.add('is-active');
            }
        }

        setOverlaysVisible(true);
        updateScrollLock();

        const closeBtn = categoryDropdown ? categoryDropdown.querySelector('.category-drawer-close') : null;
        if (closeBtn) {
            closeBtn.focus();
        }
    }

    function closeCategoryDrawer(restoreFocus = true) {
        if (!categoryDropdown) return;

        if (activeDrawer === 'categoryDrawer') {
            activeDrawer = null;
        }

        categoryTriggers.forEach(trig => {
            trig.setAttribute('aria-expanded', 'false');
            trig.classList.remove('is-active');
        });

        categoryDropdown.classList.remove('is-mobile-open', 'is-active');
        categoryDropdown.hidden = true;
        categoryDropdown.setAttribute('aria-hidden', 'true');
        categoryDropdown.setAttribute('inert', '');

        resetPanelsAndAccordions(categoryDropdown);

        setOverlaysVisible(false);
        updateScrollLock();

        if (restoreFocus && lastActiveTrigger && typeof lastActiveTrigger.focus === 'function') {
            lastActiveTrigger.focus();
        }
    }

    // Event Listeners for Main Nav Triggers
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (activeDrawer === 'mainNav') {
                closeMainNav(true);
            } else {
                openMainNav(mobileMenuBtn);
            }
        });
    }

    if (mainNavCloseBtn) {
        mainNavCloseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeMainNav(true);
        });
    }

    // Event Listeners for Category Triggers
    categoryTriggers.forEach(trig => {
        trig.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (activeDrawer === 'categoryDrawer') {
                closeCategoryDrawer(true);
            } else {
                openCategoryDrawer(trig);
            }
        });
    });

    categoryCloseBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (activeDrawer === 'categoryDrawer') {
                closeCategoryDrawer(true);
            } else if (activeDrawer === 'mainNav') {
                closeMainNav(true);
            }
        });
    });

    categoryOverlays.forEach(ov => {
        ov.addEventListener('click', () => {
            if (activeDrawer === 'categoryDrawer') closeCategoryDrawer(true);
            if (activeDrawer === 'mainNav') closeMainNav(true);
        });
    });

    // Global Click Outside Handler
    document.addEventListener('click', (e) => {
        const isCatClick = categoryDropdown && categoryDropdown.contains(e.target);
        const isCatTriggerClick = categoryTriggers.some(t => t.contains(e.target));
        const isNavClick = mainNavMenu && mainNavMenu.contains(e.target);
        const isNavTriggerClick = mobileMenuBtn && mobileMenuBtn.contains(e.target);

        if (activeDrawer === 'categoryDrawer' && !isCatClick && !isCatTriggerClick) {
            closeCategoryDrawer(false);
        }
        if (activeDrawer === 'mainNav' && !isNavClick && !isNavTriggerClick) {
            closeMainNav(false);
        }
    });

    // Escape Key Listener
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (activeDrawer === 'categoryDrawer') {
                closeCategoryDrawer(true);
            } else if (activeDrawer === 'mainNav') {
                closeMainNav(true);
            }
        }
    });

    // Initial state setup
    closeMainNav(false);
    closeCategoryDrawer(false);

    // --- REUSABLE CATEGORY MENU INITIALIZER ---
    function initCategoryMenu(rootElement, options = {}) {
        if (!rootElement) return;

        const isStatic = options.isStatic || false;
        const rows = rootElement.querySelectorAll('.category-sidebar__row');
        const megaPanels = rootElement.querySelectorAll('.category-mega__panel');
        const mobilePanels = rootElement.querySelectorAll('.category-mobile__panel');
        const accBtns = rootElement.querySelectorAll('.category-mobile-accordion-toggle');

        let activePanelId = null;
        let hoverTimeout = null;

        function activatePanel(panelId, rowEl) {
            if (!panelId || !rowEl) return;
            if (panelId === activePanelId && rowEl.classList.contains('is-active')) return;

            rows.forEach(r => r.classList.remove('is-active'));
            megaPanels.forEach(p => {
                p.classList.remove('is-active');
                p.hidden = true;
                p.setAttribute('aria-hidden', 'true');
            });

            rowEl.classList.add('is-active');
            activePanelId = panelId;

            const targetPanel = rootElement.querySelector(`#${panelId}`);
            if (targetPanel) {
                targetPanel.classList.add('is-active');
                targetPanel.hidden = false;
                targetPanel.setAttribute('aria-hidden', 'false');
            }

            rootElement.classList.add('has-active-panel');
        }

        function resetMenu() {
            rows.forEach(r => r.classList.remove('is-active', 'is-accordion-open'));
            megaPanels.forEach(p => {
                p.classList.remove('is-active');
                p.hidden = true;
                p.setAttribute('aria-hidden', 'true');
            });
            mobilePanels.forEach(mp => {
                mp.hidden = true;
                mp.setAttribute('aria-hidden', 'true');
            });
            accBtns.forEach(b => b.setAttribute('aria-expanded', 'false'));
            activePanelId = null;
            rootElement.classList.remove('has-active-panel');
        }

        // Row Mouseenter & Focus Listener
        rows.forEach(row => {
            const panelId = row.getAttribute('data-panel-id');
            const itemLink = row.querySelector('.category-sidebar__item');
            const accBtn = row.querySelector('.category-mobile-accordion-toggle');

            if (itemLink) {
                itemLink.addEventListener('mouseenter', () => {
                    if (window.innerWidth > 767) {
                        if (hoverTimeout) clearTimeout(hoverTimeout);
                        hoverTimeout = setTimeout(() => {
                            activatePanel(panelId, row);
                        }, 80);
                    }
                });

                itemLink.addEventListener('focus', () => {
                    if (window.innerWidth > 767) {
                        activatePanel(panelId, row);
                    }
                });
            }

            // Mobile Exclusive Accordion Toggle
            if (accBtn) {
                accBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetMobilePanelId = accBtn.getAttribute('aria-controls');
                    const targetMobilePanel = rootElement.querySelector(`#${targetMobilePanelId}`);
                    const isCurrentlyOpen = row.classList.contains('is-accordion-open');

                    // Exclusive accordion: Close all other open accordions in this rootElement
                    rows.forEach(otherRow => {
                        if (otherRow !== row) {
                            otherRow.classList.remove('is-accordion-open');
                            const otherBtn = otherRow.querySelector('.category-mobile-accordion-toggle');
                            if (otherBtn) {
                                otherBtn.setAttribute('aria-expanded', 'false');
                                const otherPanelId = otherBtn.getAttribute('aria-controls');
                                const otherPanel = rootElement.querySelector(`#${otherPanelId}`);
                                if (otherPanel) {
                                    otherPanel.hidden = true;
                                    otherPanel.setAttribute('aria-hidden', 'true');
                                }
                            }
                        }
                    });

                    if (isCurrentlyOpen) {
                        row.classList.remove('is-accordion-open');
                        accBtn.setAttribute('aria-expanded', 'false');
                        if (targetMobilePanel) {
                            targetMobilePanel.hidden = true;
                            targetMobilePanel.setAttribute('aria-hidden', 'true');
                        }
                    } else {
                        row.classList.add('is-accordion-open');
                        accBtn.setAttribute('aria-expanded', 'true');
                        if (targetMobilePanel) {
                            targetMobilePanel.hidden = false;
                            targetMobilePanel.setAttribute('aria-hidden', 'false');
                        }
                    }
                });
            }
        });

        // Mouseleave container handler for static menu
        if (isStatic) {
            rootElement.addEventListener('mouseleave', () => {
                if (window.innerWidth > 767) {
                    if (hoverTimeout) clearTimeout(hoverTimeout);
                    resetMenu();
                }
            });

            // Prevent panel closing when hovering over static mega panel area
            const megaArea = rootElement.querySelector('.category-dropdown__mega');
            if (megaArea) {
                megaArea.addEventListener('mouseenter', () => {
                    if (hoverTimeout) clearTimeout(hoverTimeout);
                });
            }
        }

        return { activatePanel, resetMenu };
    }

    function resetPanelsAndAccordions(rootEl) {
        if (!rootEl) return;
        const rows = rootEl.querySelectorAll('.category-sidebar__row');
        const megaPanels = rootEl.querySelectorAll('.category-mega__panel');
        const mobilePanels = rootEl.querySelectorAll('.category-mobile__panel');
        const accBtns = rootEl.querySelectorAll('.category-mobile-accordion-toggle');

        rows.forEach(r => r.classList.remove('is-active', 'is-accordion-open'));
        megaPanels.forEach(p => {
            p.classList.remove('is-active');
            p.hidden = true;
            p.setAttribute('aria-hidden', 'true');
        });
        mobilePanels.forEach(mp => {
            mp.hidden = true;
            mp.setAttribute('aria-hidden', 'true');
        });
        accBtns.forEach(b => b.setAttribute('aria-expanded', 'false'));
        rootEl.classList.remove('has-active-panel');
    }

    // Initialize Dropdown Menu (#categoryMegaDropdown)
    if (categoryDropdown) {
        initCategoryMenu(categoryDropdown, { isStatic: false });
    }

    // Initialize Homepage Static Menu (#categoryStaticMenu)
    if (categoryStaticMenu) {
        initCategoryMenu(categoryStaticMenu, { isStatic: true });
    }
});