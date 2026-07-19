document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('headerCategoryTrigger');
    const sharedMenu = document.getElementById('sharedCategoryMenu');
    const heroSlot = document.getElementById('heroCategorySlot');
    const overlaySlot = document.getElementById('overlayCategorySlot');
    const overlay = document.getElementById('globalCategoryOverlay');
    const backdrop = document.getElementById('categoryBackdrop');
    const mainNavInner = document.querySelector('.main-nav__inner');
    const mainNav = document.querySelector('.main-nav');
    const heroSection = document.querySelector('.hero-section');
    const menuItems = document.querySelectorAll('.vertical-menu__item');

    let isOverlayOpen = false;
    let savedScrollY = 0;

    function isMobile() {
        return window.innerWidth < 768;
    }

    function getScrollbarWidth() {
        return window.innerWidth - document.documentElement.clientWidth;
    }

    function initComponentPosition() {
        if (isMobile()) {
            if (sharedMenu && trigger && mainNavInner && sharedMenu.parentNode !== mainNavInner) {
                trigger.parentNode.insertBefore(sharedMenu, trigger.nextSibling);
                sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--overlay');
                sharedMenu.classList.add('catalog-menu--mobile');
            }
        } else {
            if (heroSlot && sharedMenu.parentNode !== heroSlot && !isOverlayOpen) {
                heroSlot.appendChild(sharedMenu);
                sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--overlay');
                sharedMenu.classList.add('catalog-menu--hero');
            } else if (!heroSlot && sharedMenu.parentNode !== overlaySlot && !isOverlayOpen) {
                overlaySlot.appendChild(sharedMenu);
                sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--hero');
                sharedMenu.classList.add('catalog-menu--overlay');
            }
        }
    }

    initComponentPosition();

    window.addEventListener('resize', () => {
        if (isOverlayOpen && isMobile()) {
            closeOverlay();
        }
        initComponentPosition();
    });

    function alignHero() {
        if (!heroSection || !mainNav) return;
        const navRect = mainNav.getBoundingClientRect();
        const heroRect = heroSection.getBoundingClientRect();
        
        // Calculate the difference required to bring Hero 24px below nav
        const offset = heroRect.top - navRect.bottom - 24;
        
        if (offset !== 0) {
            window.scrollBy({ top: offset, behavior: 'instant' });
        }
    }

    function lockScroll() {
        savedScrollY = window.scrollY;
        const scrollbarWidth = getScrollbarWidth();
        
        document.documentElement.style.overscrollBehavior = 'none';
        
        document.body.style.overscrollBehavior = 'none';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${savedScrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.paddingRight = `${scrollbarWidth}px`;
    }

    function unlockScroll() {
        document.documentElement.style.overscrollBehavior = '';
        
        document.body.style.overscrollBehavior = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.body.style.paddingRight = '';
        
        window.scrollTo({ top: savedScrollY, behavior: 'instant' });
    }

    function activateItem(item) {
        menuItems.forEach(i => i.classList.remove('is-active'));
        item.classList.add('is-active');
    }

    function initMegaPanelFocus() {
        let hasActive = false;
        menuItems.forEach(item => {
            if (item.classList.contains('is-active')) {
                hasActive = true;
            }
        });
        if (!hasActive && menuItems.length > 0) {
            menuItems[0].classList.add('is-active');
        }
    }

    menuItems.forEach(item => {
        item.addEventListener('mouseenter', () => activateItem(item));
        item.addEventListener('focusin', () => activateItem(item));
    });

    function updateOverlayTop() {
        if (mainNav) {
            const navBottom = mainNav.getBoundingClientRect().bottom;
            document.documentElement.style.setProperty('--category-overlay-top', `${navBottom}px`);
        }
    }

    function openOverlay() {
        if (!sharedMenu || !overlay) return;
        if (isMobile()) return;

        updateOverlayTop();

        if (heroSlot) {
            alignHero();
            requestAnimationFrame(() => {
                alignHero(); // Double-check after potential sticky header shifts
                updateOverlayTop();
                lockScroll();
                
                heroSlot.classList.add('is-category-focused');
                sharedMenu.classList.add('catalog-menu--hero-open');
                
                showOverlay();
            });
        } else {
            lockScroll();
            overlaySlot.appendChild(sharedMenu);
            sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--mobile');
            sharedMenu.classList.add('catalog-menu--overlay');
            showOverlay();
        }
    }

    function showOverlay() {
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
        initMegaPanelFocus();
        isOverlayOpen = true;
    }

    function closeOverlay() {
        if (!sharedMenu || !overlay) return;

        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        
        if (heroSlot) {
            heroSlot.classList.remove('is-category-focused');
            sharedMenu.classList.remove('catalog-menu--hero-open');
        }
        
        unlockScroll();
        isOverlayOpen = false;
    }

    if (trigger) {
        trigger.addEventListener('click', (e) => {
            if (isMobile()) {
                sharedMenu.classList.toggle('is-mobile-expanded');
                return;
            }
            e.preventDefault();
            if (isOverlayOpen) closeOverlay();
            else openOverlay();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeOverlay);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOverlayOpen) {
            closeOverlay();
            trigger.focus();
        }
    });
});
