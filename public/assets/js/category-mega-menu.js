document.addEventListener('DOMContentLoaded', () => {
    // Triggers & Containers
    const desktopToggleBtn = document.getElementById('categoryMenuToggle');
    const mobileCategoryBtn = document.getElementById('mobileCategoryToggle');
    const mobileBottomCatsBtn = document.getElementById('mobileBottomNavCats');
    const mobileQuickCatAllBtn = document.getElementById('mobileQuickCatAll');
    
    const categoryDropdown = document.getElementById('categoryMegaDropdown') || document.getElementById('categoryStaticMenu');
    const categoryOverlays = document.querySelectorAll('.category-overlay');
    const categoryCloseBtns = document.querySelectorAll('.category-drawer-close');

    const mobileMenuBtn = document.getElementById('mobileMenuToggle');
    const mainNavMenu = document.getElementById('mainNavMenu');
    const mainNavCloseBtn = document.getElementById('mobileDrawerClose');

    let isCategoryOpen = false;
    let isMainNavOpen = false;
    let lastActiveTrigger = null;
    let hoverTimeout = null;
    let activeDesktopRow = null;
    let activeDesktopPanelId = null;

    // Helper: Lock / Unlock Body Scroll
    function updateBodyScrollLock() {
        if (isCategoryOpen || isMainNavOpen) {
            document.body.classList.add('category-scroll-locked');
        } else {
            document.body.classList.remove('category-scroll-locked');
        }
    }

    // 1. MAIN NAVIGATION DRAWER HANDLERS (mobileMenuToggle ONLY)
    function openMainNav(triggerEl) {
        if (isCategoryOpen) closeCategoryMenu(false);

        isMainNavOpen = true;
        lastActiveTrigger = triggerEl || mobileMenuBtn;

        if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'true');
        if (mainNavMenu) mainNavMenu.classList.add('is-mobile-open');
        
        categoryOverlays.forEach(ov => {
            ov.hidden = false;
            ov.setAttribute('aria-hidden', 'false');
        });
        updateBodyScrollLock();

        if (mainNavCloseBtn) mainNavCloseBtn.focus();
    }

    function closeMainNav(restoreFocus = true) {
        isMainNavOpen = false;
        if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
        if (mainNavMenu) mainNavMenu.classList.remove('is-mobile-open');
        
        if (!isCategoryOpen) {
            categoryOverlays.forEach(ov => {
                ov.hidden = true;
                ov.setAttribute('aria-hidden', 'true');
            });
        }
        updateBodyScrollLock();

        if (restoreFocus && lastActiveTrigger && typeof lastActiveTrigger.focus === 'function') {
            lastActiveTrigger.focus();
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (isMainNavOpen) {
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

    // 2. CATEGORY DRAWER HANDLERS
    function openCategoryMenu(triggerEl) {
        if (isMainNavOpen) closeMainNav(false);
        if (!categoryDropdown) return;

        isCategoryOpen = true;
        lastActiveTrigger = triggerEl || desktopToggleBtn || mobileCategoryBtn;

        if (desktopToggleBtn) {
            desktopToggleBtn.setAttribute('aria-expanded', 'true');
            desktopToggleBtn.classList.add('is-active');
        }
        if (mobileCategoryBtn) mobileCategoryBtn.setAttribute('aria-expanded', 'true');

        categoryOverlays.forEach(ov => {
            ov.hidden = false;
            ov.setAttribute('aria-hidden', 'false');
        });

        categoryDropdown.hidden = false;
        categoryDropdown.setAttribute('aria-hidden', 'false');

        if (window.innerWidth <= 767) {
            categoryDropdown.classList.add('is-mobile-open');
        } else if (categoryDropdown.classList.contains('is-static')) {
            categoryDropdown.classList.add('is-highlighted');
        }

        updateBodyScrollLock();

        if (window.innerWidth > 767) {
            const firstRow = categoryDropdown.querySelector('.category-sidebar__item');
            if (firstRow) activateDesktopPanel(firstRow);
        } else {
            const activeCloseBtn = categoryDropdown.querySelector('.category-drawer-close');
            if (activeCloseBtn) activeCloseBtn.focus();
        }
    }

    function closeCategoryMenu(restoreFocus = true) {
        if (!categoryDropdown) return;

        isCategoryOpen = false;

        if (desktopToggleBtn) {
            desktopToggleBtn.setAttribute('aria-expanded', 'false');
            desktopToggleBtn.classList.remove('is-active');
        }
        if (mobileCategoryBtn) mobileCategoryBtn.setAttribute('aria-expanded', 'false');

        categoryDropdown.classList.remove('is-mobile-open');
        if (!categoryDropdown.classList.contains('is-static')) {
            categoryDropdown.hidden = true;
            categoryDropdown.setAttribute('aria-hidden', 'true');
        } else {
            categoryDropdown.classList.remove('is-highlighted');
        }

        if (!isMainNavOpen) {
            categoryOverlays.forEach(ov => {
                ov.hidden = true;
                ov.setAttribute('aria-hidden', 'true');
            });
        }

        updateBodyScrollLock();
        resetAllPanelsAndAccordions();

        if (restoreFocus && lastActiveTrigger && typeof lastActiveTrigger.focus === 'function') {
            lastActiveTrigger.focus();
        }
    }

    function toggleCategoryMenu(triggerEl) {
        if (isCategoryOpen) {
            closeCategoryMenu(true);
        } else {
            openCategoryMenu(triggerEl);
        }
    }

    if (desktopToggleBtn) {
        desktopToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleCategoryMenu(desktopToggleBtn);
        });
    }

    if (mobileCategoryBtn) {
        mobileCategoryBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleCategoryMenu(mobileCategoryBtn);
        });
    }

    if (mobileBottomCatsBtn) {
        mobileBottomCatsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleCategoryMenu(mobileBottomCatsBtn);
        });
    }

    if (mobileQuickCatAllBtn) {
        mobileQuickCatAllBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleCategoryMenu(mobileQuickCatAllBtn);
        });
    }

    categoryCloseBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeCategoryMenu(true);
        });
    });

    categoryOverlays.forEach(ov => {
        ov.addEventListener('click', () => {
            if (isCategoryOpen) closeCategoryMenu(true);
            if (isMainNavOpen) closeMainNav(true);
        });
    });

    // Global Click Outside
    document.addEventListener('click', (e) => {
        const isClickInsideCat = categoryDropdown && categoryDropdown.contains(e.target);
        const isClickCatTrigger = (desktopToggleBtn && desktopToggleBtn.contains(e.target)) ||
                                  (mobileCategoryBtn && mobileCategoryBtn.contains(e.target)) ||
                                  (mobileBottomCatsBtn && mobileBottomCatsBtn.contains(e.target)) ||
                                  (mobileQuickCatAllBtn && mobileQuickCatAllBtn.contains(e.target));
        
        const isClickInsideNav = mainNavMenu && mainNavMenu.contains(e.target);
        const isClickNavTrigger = mobileMenuBtn && mobileMenuBtn.contains(e.target);

        if (isCategoryOpen && !isClickInsideCat && !isClickCatTrigger) {
            closeCategoryMenu(false);
        }
        if (isMainNavOpen && !isClickInsideNav && !isClickNavTrigger) {
            closeMainNav(false);
        }
    });

    // Escape Key Handler
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (isCategoryOpen) {
                closeCategoryMenu(true);
            } else if (isMainNavOpen) {
                closeMainNav(true);
            }
        }
    });

    // 3. DESKTOP HOVER & FOCUS STATE MACHINE
    function activateDesktopPanel(itemEl) {
        if (!itemEl || !categoryDropdown) return;
        const targetId = itemEl.getAttribute('data-panel-id');
        if (targetId === activeDesktopPanelId) return;

        const rowEl = itemEl.closest('.category-sidebar__row') || itemEl;
        const allRows = categoryDropdown.querySelectorAll('.category-sidebar__row');
        const allMegaPanels = categoryDropdown.querySelectorAll('.category-mega__panel');

        allRows.forEach(r => r.classList.remove('is-active'));
        allMegaPanels.forEach(p => {
            p.classList.remove('is-active');
            p.hidden = true;
            p.setAttribute('aria-hidden', 'true');
        });

        activeDesktopRow = rowEl;
        activeDesktopPanelId = targetId;
        activeDesktopRow.classList.add('is-active');

        const targetPanel = document.getElementById(targetId);
        if (targetPanel) {
            targetPanel.classList.add('is-active');
            targetPanel.hidden = false;
            targetPanel.setAttribute('aria-hidden', 'false');
        }

        categoryDropdown.classList.add('has-active-panel');
    }

    function resetAllPanelsAndAccordions() {
        if (!categoryDropdown) return;

        const allRows = categoryDropdown.querySelectorAll('.category-sidebar__row');
        const allMegaPanels = categoryDropdown.querySelectorAll('.category-mega__panel');
        const allMobilePanels = categoryDropdown.querySelectorAll('.category-mobile__panel');
        const allAccBtns = categoryDropdown.querySelectorAll('.category-mobile-accordion-toggle');

        allRows.forEach(r => {
            r.classList.remove('is-active');
            r.classList.remove('is-accordion-open');
        });

        allMegaPanels.forEach(p => {
            p.classList.remove('is-active');
            p.hidden = true;
            p.setAttribute('aria-hidden', 'true');
        });

        allMobilePanels.forEach(mp => {
            mp.hidden = true;
            mp.setAttribute('aria-hidden', 'true');
        });

        allAccBtns.forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
        });

        activeDesktopRow = null;
        activeDesktopPanelId = null;
        categoryDropdown.classList.remove('has-active-panel');
    }

    if (categoryDropdown) {
        if (categoryDropdown.classList.contains('is-static')) {
            categoryDropdown.addEventListener('mouseleave', () => {
                if (window.innerWidth > 767) {
                    if (hoverTimeout) clearTimeout(hoverTimeout);
                    resetAllPanelsAndAccordions();
                }
            });
        }

        const sidebarRows = categoryDropdown.querySelectorAll('.category-sidebar__row');
        sidebarRows.forEach(row => {
            const itemLink = row.querySelector('.category-sidebar__item');
            const accBtn = row.querySelector('.category-mobile-accordion-toggle');

            if (itemLink) {
                itemLink.addEventListener('mouseenter', () => {
                    if (window.innerWidth > 767) {
                        if (hoverTimeout) clearTimeout(hoverTimeout);
                        hoverTimeout = setTimeout(() => {
                            activateDesktopPanel(itemLink);
                        }, 80);
                    }
                });

                itemLink.addEventListener('focus', () => {
                    if (window.innerWidth > 767) {
                        activateDesktopPanel(itemLink);
                    }
                });
            }

            // 4. MOBILE EXCLUSIVE ACCORDION HANDLER
            if (accBtn) {
                accBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetMobilePanelId = accBtn.getAttribute('aria-controls');
                    const targetMobilePanel = document.getElementById(targetMobilePanelId);
                    const isCurrentlyOpen = row.classList.contains('is-accordion-open');

                    // Close all other open accordions
                    sidebarRows.forEach(otherRow => {
                        if (otherRow !== row) {
                            otherRow.classList.remove('is-accordion-open');
                            const otherBtn = otherRow.querySelector('.category-mobile-accordion-toggle');
                            if (otherBtn) {
                                otherBtn.setAttribute('aria-expanded', 'false');
                                const otherPanelId = otherBtn.getAttribute('aria-controls');
                                const otherPanel = document.getElementById(otherPanelId);
                                if (otherPanel) {
                                    otherPanel.hidden = true;
                                    otherPanel.setAttribute('aria-hidden', 'true');
                                }
                            }
                        }
                    });

                    // Toggle current row
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

        // Prevent panel close when hovering over mega container
        const megaContainer = categoryDropdown.querySelector('.category-dropdown__mega');
        if (megaContainer) {
            megaContainer.addEventListener('mouseenter', () => {
                if (hoverTimeout) clearTimeout(hoverTimeout);
            });
        }
    }
});