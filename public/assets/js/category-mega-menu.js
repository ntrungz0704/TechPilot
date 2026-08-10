/**
 * TechPilot Category Mega Menu & Navigation Controller (V4 Final)
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
    let staticMenuController = null;
    let dropdownMenuController = null;

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

    // --- RESPONSIVE STATE SYNCHRONIZATION ---
    function syncMainNavResponsiveState() {
        const isDrawerMode = window.matchMedia('(max-width: 1024px)').matches;

        if (!isDrawerMode) {
            // Desktop Mode (> 1024px): Display as header nav
            if (mainNavMenu) {
                mainNavMenu.classList.remove('is-mobile-open');
                mainNavMenu.removeAttribute('aria-hidden');
                mainNavMenu.removeAttribute('inert');
            }
            if (mobileMenuBtn) {
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            }
            if (activeDrawer === 'mainNav') {
                activeDrawer = null;
                setOverlaysVisible(false);
                updateScrollLock();
            }
        } else {
            // Drawer Mode (<= 1024px)
            if (activeDrawer !== 'mainNav') {
                if (mainNavMenu) {
                    mainNavMenu.classList.remove('is-mobile-open');
                    mainNavMenu.setAttribute('aria-hidden', 'true');
                    mainNavMenu.setAttribute('inert', '');
                }
                if (mobileMenuBtn) {
                    mobileMenuBtn.setAttribute('aria-expanded', 'false');
                }
            } else {
                if (mainNavMenu) {
                    mainNavMenu.classList.add('is-mobile-open');
                    mainNavMenu.setAttribute('aria-hidden', 'false');
                    mainNavMenu.removeAttribute('inert');
                }
                if (mobileMenuBtn) {
                    mobileMenuBtn.setAttribute('aria-expanded', 'true');
                }
            }
        }
    }

    function syncCategoryDrawerResponsiveState() {
        const isMobileCategoryMode = window.matchMedia('(max-width: 767px)').matches;
        if (!isMobileCategoryMode) {
            if (activeDrawer === 'categoryDrawer' || (categoryDropdown && categoryDropdown.classList.contains('is-mobile-open'))) {
                closeCategoryDrawer(false);
            }
        }
    }

    function syncResponsiveNavigationState() {
        syncMainNavResponsiveState();
        syncCategoryDrawerResponsiveState();
    }

    // --- MAIN NAVIGATION DRAWER CONTROLLER ---
    function openMainNav(triggerEl) {
        const isDrawerMode = window.matchMedia('(max-width: 1024px)').matches;
        if (!isDrawerMode) return;

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
            mainNavMenu.classList.add('is-mobile-open');
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

        mainNavMenu.classList.remove('is-mobile-open');

        const isDrawerMode = window.matchMedia('(max-width: 1024px)').matches;
        if (isDrawerMode) {
            mainNavMenu.setAttribute('aria-hidden', 'true');
            mainNavMenu.setAttribute('inert', '');
        } else {
            mainNavMenu.removeAttribute('aria-hidden');
            mainNavMenu.removeAttribute('inert');
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
                if (dropdownMenuController) {
                    const firstRow = categoryDropdown.querySelector('.category-sidebar__row');
                    if (firstRow) {
                        const panelId = firstRow.getAttribute('data-panel-id');
                        dropdownMenuController.activatePanel(panelId, firstRow);
                    }
                }
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

            // Nếu trên Trang chủ (đang có Bảng Danh mục bên trái ở ảnh 2)
            if (categoryStaticMenu && window.innerWidth > 767) {
                // Đóng dropdown popup cũ nếu đang mở
                if (activeDrawer === 'categoryDrawer') {
                    closeCategoryDrawer(false);
                }

                // Cuộn mượt và làm sáng bảng danh mục bên trái (ảnh 2)
                categoryStaticMenu.scrollIntoView({ behavior: 'smooth', block: 'center' });
                categoryStaticMenu.classList.add('is-highlighted');
                setTimeout(() => {
                    categoryStaticMenu.classList.remove('is-highlighted');
                }, 1600);

                // Mở menu con của mục đầu tiên nếu chưa mở mục nào
                if (staticMenuController) {
                    const firstRow = categoryStaticMenu.querySelector('.category-sidebar__row');
                    if (firstRow) {
                        const panelId = firstRow.getAttribute('data-panel-id');
                        staticMenuController.activatePanel(panelId, firstRow);
                    }
                }
                return;
            }

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

    // Window Resize / Media Query Listener for Responsive State Sync
    const mediaQuery1024 = window.matchMedia('(max-width: 1024px)');
    const mediaQuery767 = window.matchMedia('(max-width: 767px)');

    [mediaQuery1024, mediaQuery767].forEach(mq => {
        if (mq.addEventListener) {
            mq.addEventListener('change', syncResponsiveNavigationState);
        } else if (mq.addListener) {
            mq.addListener(syncResponsiveNavigationState);
        }
    });
    window.addEventListener('resize', syncResponsiveNavigationState);

    // Initial state setup
    syncResponsiveNavigationState();
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
                targetPanel.removeAttribute('hidden');
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

        // Row Mouseenter, Click & Focus Listeners (Hỗ trợ cả Hover & Click mở menu con)
        let staticLeaveTimer = null;

        rows.forEach(row => {
            const panelId = row.getAttribute('data-panel-id');
            const itemLink = row.querySelector('.category-sidebar__item');
            const accBtn = row.querySelector('.category-mobile-accordion-toggle');

            function handleRowActivate() {
                if (window.innerWidth > 767) {
                    if (staticLeaveTimer) {
                        clearTimeout(staticLeaveTimer);
                        staticLeaveTimer = null;
                    }
                    if (hoverTimeout) clearTimeout(hoverTimeout);
                    activatePanel(panelId, row);
                }
            }

            row.addEventListener('mouseenter', handleRowActivate);

            if (itemLink) {
                itemLink.addEventListener('focus', handleRowActivate);
                itemLink.addEventListener('click', (e) => {
                    if (window.innerWidth > 767) {
                        const targetPanel = rootElement.querySelector(`#${panelId}`);
                        const isCurrentlyActive = row.classList.contains('is-active') && targetPanel && targetPanel.classList.contains('is-active');

                        if (!isCurrentlyActive) {
                            e.preventDefault();
                            handleRowActivate();
                        }
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

        // Mouseleave container handler for static menu (Grace period 200ms)
        if (isStatic) {
            const megaArea = rootElement.querySelector('.category-dropdown__mega');

            function handleStaticMouseLeave(e) {
                if (window.innerWidth > 767) {
                    const related = e.relatedTarget;
                    if (related && (rootElement.contains(related) || (megaArea && megaArea.contains(related)))) {
                        return;
                    }
                    if (staticLeaveTimer) clearTimeout(staticLeaveTimer);
                    staticLeaveTimer = setTimeout(() => {
                        resetMenu();
                    }, 200);
                }
            }

            function handleStaticMouseEnter() {
                if (staticLeaveTimer) {
                    clearTimeout(staticLeaveTimer);
                    staticLeaveTimer = null;
                }
            }

            rootElement.addEventListener('mouseleave', handleStaticMouseLeave);
            rootElement.addEventListener('mouseenter', handleStaticMouseEnter);

            if (megaArea) {
                megaArea.addEventListener('mouseleave', handleStaticMouseLeave);
                megaArea.addEventListener('mouseenter', handleStaticMouseEnter);
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
        dropdownMenuController = initCategoryMenu(categoryDropdown, { isStatic: false });
    }

    // Initialize Homepage Static Menu (#categoryStaticMenu)
    if (categoryStaticMenu) {
        staticMenuController = initCategoryMenu(categoryStaticMenu, { isStatic: true });
    }
});