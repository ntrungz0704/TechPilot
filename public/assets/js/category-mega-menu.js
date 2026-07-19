const DESKTOP_MIN_WIDTH = 1025;

function isDesktop() {
    return window.innerWidth >= DESKTOP_MIN_WIDTH;
}

document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('headerCategoryTrigger');
    const sharedMenu = document.getElementById('sharedCategoryMenu');
    const heroSlot = document.getElementById('heroCategorySlot');
    const overlaySlot = document.getElementById('overlayCategorySlot');
    const overlay = document.getElementById('globalCategoryOverlay');
    const backdrop = document.getElementById('categoryBackdrop');
    const mainNav = document.querySelector('.main-nav');
    const mainNavInner = document.querySelector('.main-nav__inner');
    const commerceHeaderSentinel = document.getElementById('commerceHeaderSentinel');
    const commerceHeaderStack = document.getElementById('commerceHeaderStack');

    const menuItems = sharedMenu
        ? Array.from(sharedMenu.querySelectorAll('.vertical-menu__item'))
        : [];

    let isMenuOpen = false;
    let activeMode = null; // null | 'hero' | 'overlay' | 'mobile'

    // STICKY NAVIGATION OBSERVER
    if (commerceHeaderSentinel && commerceHeaderStack && 'IntersectionObserver' in window) {
        const stickyObserver = new IntersectionObserver(
            ([entry]) => {
                commerceHeaderStack.classList.toggle('is-stuck', !entry.isIntersecting);

                if (isMenuOpen) {
                    requestAnimationFrame(updateOverlayTop);
                }
            },
            {
                threshold: 0
            }
        );

        stickyObserver.observe(commerceHeaderSentinel);
    }

    // INITIAL POSITION & RESIZE
    function updateInitialPosition() {
        if (isDesktop()) {
            if (heroSlot) {
                if (sharedMenu && sharedMenu.parentNode !== heroSlot) {
                    heroSlot.appendChild(sharedMenu);
                }
                if (sharedMenu) {
                    sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--overlay', 'catalog-menu--hero-open');
                    sharedMenu.classList.add('catalog-menu--hero');
                }
            } else {
                if (overlaySlot && sharedMenu && sharedMenu.parentNode !== overlaySlot) {
                    overlaySlot.appendChild(sharedMenu);
                }
                if (sharedMenu) {
                    sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--hero', 'catalog-menu--hero-open');
                    sharedMenu.classList.add('catalog-menu--overlay');
                }
            }
        } else {
            // Mobile: Put menu after trigger or in drawer
            if (mainNavInner && trigger && sharedMenu && sharedMenu.parentNode !== mainNavInner) {
                trigger.parentNode.insertBefore(sharedMenu, trigger.nextSibling);
            }
            if (sharedMenu) {
                sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--overlay', 'catalog-menu--hero-open');
                sharedMenu.classList.add('catalog-menu--mobile');
            }
        }
    }

    updateInitialPosition();

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const currentIsDesktop = isDesktop();
            
            if (currentIsDesktop) {
                // Mobile to Desktop
                menuItems.forEach(item => {
                    item.classList.remove('is-expanded');
                    const t = item.querySelector('.mobile-category-toggle');
                    if (t) t.setAttribute('aria-expanded', 'false');
                });
                
                if (heroSlot) {
                    if (sharedMenu && sharedMenu.parentNode !== heroSlot) {
                        heroSlot.appendChild(sharedMenu);
                    }
                    if (sharedMenu && !isMenuOpen) {
                        sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--overlay');
                        sharedMenu.classList.add('catalog-menu--hero');
                    }
                } else {
                    if (overlaySlot && sharedMenu && sharedMenu.parentNode !== overlaySlot) {
                        overlaySlot.appendChild(sharedMenu);
                    }
                    if (sharedMenu && !isMenuOpen) {
                        sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--hero');
                        sharedMenu.classList.add('catalog-menu--overlay');
                    }
                }
            } else {
                // Desktop to Mobile
                if (isMenuOpen) {
                    closeMenu();
                }
                
                if (mainNavInner && trigger && sharedMenu && sharedMenu.parentNode !== mainNavInner) {
                    trigger.parentNode.insertBefore(sharedMenu, trigger.nextSibling);
                }
                if (sharedMenu) {
                    sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--overlay', 'catalog-menu--hero-open');
                    sharedMenu.classList.add('catalog-menu--mobile');
                }
            }
            
            if (isMenuOpen && currentIsDesktop) {
                requestAnimationFrame(updateOverlayTop);
            }
        }, 50);
    });

    // OVERLAY TOP LOGIC
    function updateOverlayTop() {
        if (!commerceHeaderStack) {
            return;
        }

        const stackRect = commerceHeaderStack.getBoundingClientRect();
        const stackBottom = Math.max(
            0,
            Math.min(window.innerHeight, stackRect.bottom)
        );

        document.documentElement.style.setProperty(
            '--category-overlay-top',
            `${Math.round(stackBottom)}px`
        );
    }

    // SCROLL LOCK
    function lockScroll() {
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

        document.documentElement.style.setProperty(
            '--scrollbar-width',
            `${scrollbarWidth}px`
        );

        document.documentElement.classList.add('category-scroll-locked');
        document.body.classList.add('category-scroll-locked');
    }

    function unlockScroll() {
        document.documentElement.classList.remove('category-scroll-locked');
        document.body.classList.remove('category-scroll-locked');
        document.documentElement.style.removeProperty('--scrollbar-width');
    }

    // ACTIVE ITEM
    function activateItem(item) {
        menuItems.forEach(currentItem => {
            currentItem.classList.toggle('is-active', currentItem === item);
        });
    }

    menuItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            if (isDesktop() && isMenuOpen) {
                activateItem(item);
            } else if (isDesktop() && !isMenuOpen && activeMode === null) {
                // Do not active item just by hover when menu is not open yet
                // The requirements say: "Không active item chỉ bằng hover khi menu chưa mở"
            }
        });
        
        item.addEventListener('focusin', () => {
            if (isDesktop() && isMenuOpen) {
                activateItem(item);
            }
        });
    });

    // HERO MODE CHECK
    function canUseHeroMode() {
        if (!heroSlot || !commerceHeaderStack || !isDesktop()) {
            return false;
        }

        const heroRect = heroSlot.getBoundingClientRect();
        const stackRect = commerceHeaderStack.getBoundingClientRect();

        const visibleTop = Math.max(heroRect.top, stackRect.bottom);
        const visibleBottom = Math.min(heroRect.bottom, window.innerHeight);
        const visibleHeight = Math.max(0, visibleBottom - visibleTop);

        return (
            heroRect.top >= stackRect.bottom - 1 &&
            visibleHeight >= 320
        );
    }

    // OPEN MENU
    function openMenu() {
        if (!isDesktop() || !sharedMenu || !overlay) return;

        updateOverlayTop();

        if (canUseHeroMode()) {
            activeMode = 'hero';
            if (sharedMenu.parentNode !== heroSlot) {
                heroSlot.appendChild(sharedMenu);
            }
            
            sharedMenu.classList.remove('catalog-menu--overlay', 'catalog-menu--mobile');
            sharedMenu.classList.add('catalog-menu--hero', 'catalog-menu--hero-open');
            
            if (heroSlot) {
                heroSlot.classList.add('is-category-focused');
            }
            
            lockScroll();
            
            // Note: we might only want to open the backdrop here, but the requirement 
            // says to open overlay backdrop.
            overlay.classList.add('is-open');
            
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'true');
            }
            overlay.setAttribute('aria-hidden', 'false');
            
            if (menuItems.length > 0 && !menuItems.some(item => item.classList.contains('is-active'))) {
                activateItem(menuItems[0]);
            }
            
            isMenuOpen = true;
            
        } else {
            activeMode = 'overlay';
            if (overlaySlot && sharedMenu.parentNode !== overlaySlot) {
                overlaySlot.appendChild(sharedMenu);
            }
            
            sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--hero-open', 'catalog-menu--mobile');
            sharedMenu.classList.add('catalog-menu--overlay');
            
            lockScroll();
            
            overlay.classList.add('is-open');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'true');
            }
            overlay.setAttribute('aria-hidden', 'false');
            
            if (menuItems.length > 0 && !menuItems.some(item => item.classList.contains('is-active'))) {
                activateItem(menuItems[0]);
            }
            
            isMenuOpen = true;
        }
        
        requestAnimationFrame(updateOverlayTop);
    }

    // CLOSE MENU
    function closeMenu() {
        if (!sharedMenu || !overlay) return;
        
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        
        if (heroSlot) {
            heroSlot.classList.remove('is-category-focused');
        }
        
        sharedMenu.classList.remove('catalog-menu--hero-open');
        
        menuItems.forEach(item => {
            item.classList.remove('is-active');
            // item.classList.remove('is-expanded'); // the instruction says "Xóa is-expanded mobile item nếu cần."
        });
        
        unlockScroll();
        
        if (isDesktop()) {
            if (heroSlot) {
                if (sharedMenu.parentNode !== heroSlot) {
                    heroSlot.appendChild(sharedMenu);
                }
                sharedMenu.classList.remove('catalog-menu--overlay', 'catalog-menu--mobile');
                sharedMenu.classList.add('catalog-menu--hero');
            } else {
                if (overlaySlot && sharedMenu.parentNode !== overlaySlot) {
                    overlaySlot.appendChild(sharedMenu);
                }
                sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--mobile');
                sharedMenu.classList.add('catalog-menu--overlay');
            }
        }
        
        activeMode = null;
        isMenuOpen = false;
    }

    // TRIGGER EVENT
    if (trigger) {
        trigger.addEventListener('click', (event) => {
            if (!isDesktop()) {
                // Mobile behavior (maybe just toggle a class or it's handled by drawer)
                return;
            }
            
            event.preventDefault();
            
            if (isMenuOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        });
    }

    // BACKDROP & ESCAPE
    if (backdrop) {
        backdrop.addEventListener('click', () => {
            if (isMenuOpen) {
                closeMenu();
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isMenuOpen) {
            closeMenu();
            if (trigger) {
                trigger.focus();
            }
        }
    });

    // MOBILE ACCORDION
    const mobileToggles = document.querySelectorAll('.mobile-category-toggle');
    mobileToggles.forEach(toggle => {
        toggle.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();

            const item = toggle.closest('.vertical-menu__item');

            if (!item) {
                return;
            }

            menuItems.forEach(currentItem => {
                if (currentItem !== item) {
                    currentItem.classList.remove('is-expanded');

                    const currentToggle = currentItem.querySelector('.mobile-category-toggle');
                    if (currentToggle) {
                        currentToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            const expanded = item.classList.toggle('is-expanded');
            toggle.setAttribute('aria-expanded', String(expanded));
        });
    });
});