const DESKTOP_MIN_WIDTH = 1025;

const catalogState = {
    isOpen: false,
    mode: null // 'hero' | 'overlay' | 'mobile' | null
};

document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('headerCategoryTrigger');
    const sharedMenu = document.getElementById('sharedCategoryMenu');
    const heroSlot = document.getElementById('heroCategorySlot');
    const overlaySlot = document.getElementById('overlayCategorySlot');
    const overlay = document.getElementById('globalCategoryOverlay');
    const commerceHeaderSentinel = document.getElementById('commerceHeaderSentinel');
    const commerceHeaderStack = document.getElementById('commerceHeaderStack');
    const mainNavInner = document.querySelector('.main-nav__inner');

    const menuItems = sharedMenu
        ? Array.from(sharedMenu.querySelectorAll('.vertical-menu__item'))
        : [];

    function isDesktop() {
        return window.innerWidth >= DESKTOP_MIN_WIDTH;
    }

    // 1. Intersection Observer for Sticky Header
    if (commerceHeaderSentinel && commerceHeaderStack && 'IntersectionObserver' in window) {
        const stickyObserver = new IntersectionObserver(
            ([entry]) => {
                commerceHeaderStack.classList.toggle('is-stuck', !entry.isIntersecting);
                if (catalogState.isOpen) {
                    requestAnimationFrame(updateOverlayTop);
                }
            },
            { threshold: 0 }
        );
        stickyObserver.observe(commerceHeaderSentinel);
    }

    // 2. Overlay Top Calculation
    function updateOverlayTop() {
        if (!commerceHeaderStack) return;
        const stackRect = commerceHeaderStack.getBoundingClientRect();
        const stackBottom = Math.max(0, Math.min(window.innerHeight, stackRect.bottom));
        document.documentElement.style.setProperty('--category-overlay-top', `${Math.round(stackBottom)}px`);
    }

    // Recalculate top position on scroll
    window.addEventListener('scroll', () => {
        if (catalogState.isOpen) {
            updateOverlayTop();
        }
    }, { passive: true });

    // 3. Accessibility & Scroll Lock
    function lockScroll() {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        document.documentElement.style.setProperty('--scrollbar-width', `${scrollbarWidth}px`);
        document.documentElement.classList.add('category-scroll-locked');
        document.body.classList.add('category-scroll-locked');
    }

    function unlockScroll() {
        document.documentElement.classList.remove('category-scroll-locked');
        document.body.classList.remove('category-scroll-locked');
        document.documentElement.style.removeProperty('--scrollbar-width');
    }

    function updateAccessibility() {
        if (trigger) {
            trigger.setAttribute('aria-expanded', catalogState.isOpen ? 'true' : 'false');
        }
        if (overlay) {
            overlay.setAttribute('aria-hidden', catalogState.isOpen ? 'false' : 'true');
        }
    }

    function activateItem(item) {
        menuItems.forEach(currentItem => {
            currentItem.classList.toggle('is-active', currentItem === item);
        });
    }

    function clearActiveItems() {
        menuItems.forEach(item => {
            item.classList.remove('is-active');
        });
    }

    // 4. Update Mode and DOM based on State
    function canUseHeroMode() {
        if (!heroSlot || !commerceHeaderStack || !isDesktop()) return false;
        const heroRect = heroSlot.getBoundingClientRect();
        const stackRect = commerceHeaderStack.getBoundingClientRect();
        const visibleTop = Math.max(heroRect.top, stackRect.bottom);
        const visibleBottom = Math.min(heroRect.bottom, window.innerHeight);
        const visibleHeight = Math.max(0, visibleBottom - visibleTop);
        return (heroRect.top >= stackRect.bottom - 1 && visibleHeight >= 320);
    }

    function updateModeAndDOM() {
        if (!sharedMenu) return;

        const desktop = isDesktop();
        let targetMode = null;
        let targetSlot = null;

        if (desktop) {
            if (heroSlot && !catalogState.isOpen) {
                targetMode = 'hero';
                targetSlot = heroSlot;
            } else if (catalogState.isOpen) {
                if (canUseHeroMode()) {
                    targetMode = 'hero-open';
                    targetSlot = heroSlot;
                } else {
                    targetMode = 'overlay';
                    targetSlot = overlaySlot;
                }
            } else {
                targetMode = 'overlay';
                targetSlot = overlaySlot;
            }
        } else {
            targetMode = 'mobile';
            targetSlot = mainNavInner ? (trigger ? trigger.parentNode : null) : null;
        }

        // Move DOM only if needed
        if (targetSlot) {
            if (targetMode === 'mobile') {
                if (trigger && sharedMenu.parentNode !== targetSlot) {
                    targetSlot.insertBefore(sharedMenu, trigger.nextSibling);
                }
            } else {
                if (sharedMenu.parentNode !== targetSlot) {
                    targetSlot.appendChild(sharedMenu);
                }
            }
        }

        // Update Classes
        sharedMenu.className = 'vertical-menu';
        if (targetMode === 'hero-open') {
            sharedMenu.classList.add('catalog-menu--hero', 'catalog-menu--hero-open');
        } else {
            sharedMenu.classList.add(`catalog-menu--${targetMode}`);
        }
        
        // Update Overlay UI
        if (overlay) {
            overlay.classList.toggle('is-open', catalogState.isOpen);
        }
        
        // Update Hero Slot UI
        if (heroSlot) {
            heroSlot.classList.toggle('is-category-focused', catalogState.isOpen && targetMode === 'hero-open');
        }
        
        catalogState.mode = desktop ? (heroSlot ? 'hero' : 'overlay') : 'mobile';
    }

    // 5. Public API
    function openCatalog() {
        if (!isDesktop() || !sharedMenu || !overlay) return;
        
        catalogState.isOpen = true;
        updateOverlayTop();
        updateModeAndDOM();
        lockScroll();
        updateAccessibility();
        
        if (menuItems.length > 0 && !menuItems.some(item => item.classList.contains('is-active'))) {
            activateItem(menuItems[0]);
        }
    }

    function closeCatalog() {
        catalogState.isOpen = false;
        updateModeAndDOM();
        unlockScroll();
        updateAccessibility();
        clearActiveItems();
    }

    // 6. Event Listeners
    if (trigger) {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!isDesktop()) return;
            
            if (catalogState.isOpen) {
                closeCatalog();
            } else {
                openCatalog();
            }
        });
    }

    // Backdrop Click -> Close
    const backdrop = document.getElementById('categoryBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeCatalog();
        });
    }

    // Global Click Outside -> Close
    document.addEventListener('click', (e) => {
        if (!catalogState.isOpen) return;
        
        const isClickInsideMenu = sharedMenu && sharedMenu.contains(e.target);
        const isClickInsideTrigger = trigger && trigger.contains(e.target);
        
        if (!isClickInsideMenu && !isClickInsideTrigger) {
            closeCatalog();
        }
    });

    // Escape Key -> Close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && catalogState.isOpen) {
            closeCatalog();
        }
    });

    menuItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            if (isDesktop() && catalogState.isOpen) {
                activateItem(item);
            }
        });
        
        item.addEventListener('focusin', () => {
            if (isDesktop() && catalogState.isOpen) {
                activateItem(item);
            }
        });
        
        // Mobile Toggle Expand
        const toggleBtn = item.querySelector('.mobile-category-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (!isDesktop()) {
                    const isExpanded = item.classList.contains('is-expanded');
                    item.classList.toggle('is-expanded', !isExpanded);
                    toggleBtn.setAttribute('aria-expanded', !isExpanded);
                }
            });
        }
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const currentIsDesktop = isDesktop();
            
            if (currentIsDesktop) {
                menuItems.forEach(item => {
                    item.classList.remove('is-expanded');
                    const t = item.querySelector('.mobile-category-toggle');
                    if (t) t.setAttribute('aria-expanded', 'false');
                });
            } else {
                if (catalogState.isOpen) {
                    closeCatalog();
                }
            }
            
            updateModeAndDOM();
            
            if (catalogState.isOpen && currentIsDesktop) {
                requestAnimationFrame(updateOverlayTop);
            }
        }, 50);
    });

    // Init
    updateModeAndDOM();
});