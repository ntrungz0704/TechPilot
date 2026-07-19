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
    const mainNavInner = document.querySelector('.main-nav__inner');
    const mainNav = document.querySelector('.main-nav');
    const mainNavSentinel = document.getElementById('mainNavSentinel');
    
    const menuItems = sharedMenu ? Array.from(sharedMenu.querySelectorAll('.vertical-menu__item')) : [];

    let isOverlayOpen = false;
    let activeMode = null; // null | 'hero' | 'overlay'

    // 1. IntersectionObserver for Sticky Nav
    if (mainNavSentinel && mainNav) {
        const stickyObserver = new IntersectionObserver(
            ([entry]) => {
                mainNav.classList.toggle('is-stuck', !entry.isIntersecting);
                
                if (isOverlayOpen && activeMode === 'overlay') {
                    requestAnimationFrame(updateOverlayTop);
                }
            },
            { threshold: 0 }
        );
        stickyObserver.observe(mainNavSentinel);
    }
	    // 2. Component Position Initializer
    function initComponentPosition() {
        if (!isDesktop()) {
            if (sharedMenu && trigger && mainNavInner && sharedMenu.parentNode !== mainNavInner) {
                trigger.parentNode.insertBefore(sharedMenu, trigger.nextSibling);
                sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--hero-open', 'catalog-menu--overlay');
                sharedMenu.classList.add('catalog-menu--mobile');
            }
        } else {
            if (!isOverlayOpen && heroSlot && sharedMenu) {
                if (sharedMenu.parentNode !== heroSlot) {
                    heroSlot.appendChild(sharedMenu);
                }
                sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--overlay', 'catalog-menu--hero-open');
                sharedMenu.classList.add('catalog-menu--hero');
                activeMode = null;
            }
        }
    }

    function canUseHeroMode() {
        if (!heroSlot || !mainNav || !isDesktop()) return false;
        const heroRect = heroSlot.getBoundingClientRect();
        const navRect = mainNav.getBoundingClientRect();
        const visibleTop = Math.max(heroRect.top, navRect.bottom);
        const visibleBottom = Math.min(heroRect.bottom, window.innerHeight);
        const visibleHeight = Math.max(0, visibleBottom - visibleTop);

        return heroRect.top >= navRect.bottom - 1 && visibleHeight >= 320;
    }

    initComponentPosition();

    window.addEventListener('resize', () => {
        if (isOverlayOpen && !isDesktop()) {
            closeOverlay();
        }
        initComponentPosition();
        if (isOverlayOpen && isDesktop() && activeMode === 'overlay') {
            requestAnimationFrame(updateOverlayTop);
        }
    });

    // 3. Scroll Lock
    function getScrollbarWidth() {
        return window.innerWidth - document.documentElement.clientWidth;
    }

    function lockScroll() {
        const scrollbarWidth = getScrollbarWidth();
        document.documentElement.style.setProperty('--scrollbar-width', `${scrollbarWidth}px`);
        document.documentElement.classList.add('category-scroll-locked');
        document.body.classList.add('category-scroll-locked');
    }

    function unlockScroll() {
        document.documentElement.classList.remove('category-scroll-locked');
        document.body.classList.remove('category-scroll-locked');
        document.documentElement.style.removeProperty('--scrollbar-width');
    }

    function activateItem(item) {
        menuItems.forEach(i => i.classList.remove('is-active'));
        item.classList.add('is-active');
    }

    function initMegaPanelFocus() {
        if (!menuItems.some(i => i.classList.contains('is-active')) && menuItems.length > 0) {
            menuItems[0].classList.add('is-active');
        }
    }

    menuItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            if (isDesktop()) activateItem(item);
        });
        item.addEventListener('focusin', () => {
            if (isDesktop()) activateItem(item);
        });
    });

    function updateOverlayTop() {
        if (!mainNav) return;
        const navBottom = mainNav.getBoundingClientRect().bottom;
        document.documentElement.style.setProperty('--category-overlay-top', `${Math.max(0, navBottom)}px`);
    }

    function openMenu() {
        if (!sharedMenu || !isDesktop()) return;

        if (canUseHeroMode()) {
            activeMode = 'hero';
            if (sharedMenu.parentNode !== heroSlot) {
                heroSlot.appendChild(sharedMenu);
            }
            heroSlot.classList.add('is-category-focused');
            sharedMenu.classList.remove('catalog-menu--overlay', 'catalog-menu--mobile');
            sharedMenu.classList.add('catalog-menu--hero', 'catalog-menu--hero-open');
            
            overlay.classList.add('is-open'); 
            overlay.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            initMegaPanelFocus();
            isOverlayOpen = true;
        } else {
            activeMode = 'overlay';
            if (overlaySlot) {
                overlaySlot.appendChild(sharedMenu);
            }
            sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--hero-open', 'catalog-menu--mobile');
            sharedMenu.classList.add('catalog-menu--overlay');
            
            requestAnimationFrame(() => {
                updateOverlayTop();
                lockScroll();
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
                trigger.setAttribute('aria-expanded', 'true');
                initMegaPanelFocus();
                isOverlayOpen = true;
            });
        }
    }

    function closeOverlay() {
        if (!sharedMenu || !overlay) return;

        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        
        if (heroSlot) {
            heroSlot.classList.remove('is-category-focused');
        }

        if (activeMode === 'overlay') {
            unlockScroll();
        }
        
        menuItems.forEach(item => item.classList.remove('is-active'));
        
        if (heroSlot) {
            heroSlot.appendChild(sharedMenu);
            sharedMenu.classList.remove('catalog-menu--overlay', 'catalog-menu--hero-open');
            sharedMenu.classList.add('catalog-menu--hero');
        } else {
            sharedMenu.classList.remove('catalog-menu--hero-open');
        }
        
        isOverlayOpen = false;
        activeMode = null;
    }

    if (trigger) {
        trigger.addEventListener('click', (e) => {
            if (!isDesktop()) {
                sharedMenu.classList.toggle('is-mobile-expanded');
                return;
            }
            e.preventDefault();
            if (isOverlayOpen) closeOverlay();
            else openMenu();
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

    const mobileToggles = document.querySelectorAll('.mobile-category-toggle');
    mobileToggles.forAX�
���HO����K�Y]�[�\�[�\�	��X���
JHO�K��]�[�Y�][

N�ۜ�\�[�][HH���K����\�
	˝�\�X�[[Y[�W��][I�NY�
\\�[�][JH�]\����ۜ�\�^[�YH\�[�][K��\��\���۝Z[��	�\�Y^[�Y	�N�Y[�R][\˙�ܑXX�
][HO�][K��\��\���[[ݙJ	�\�Y^[�Y	�N�ۜ�H][K�]Y\�T�[X�܊	˛[ؚ[KX�]Y�ܞK]���I�NY�

H��]]�X�]J	�\�XKY^[�Y	�	٘[�I�NJN�Y�
Z\�^[�Y
H\�[�][K��\��\��Y
	�\�Y^[�Y	�N���K��]]�X�]J	�\�XKY^[�Y	�	��YI�NB�JNJNJN�