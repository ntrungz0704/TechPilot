const DESKTOP_MIN_WIDTH = 1025;

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
    
    // Chỉ chọn trong phạm vi sharedMenu
    const menuItems = sharedMenu ? sharedMenu.querySelectorAll('.vertical-menu__item') : [];

    let isOverlayOpen = false;

    function isMobile() {
        return window.innerWidth < DESKTOP_MIN_WIDTH;
    }

    // 1. IntersectionObserver for Sticky Nav
    if (mainNavSentinel && mainNav) {
        const observer = new IntersectionObserver(
            ([entry]) => {
                // Khi sentinel trượt ra ngoài (lên trên) => main-nav bị stuck (dính)
                if (entry.boundingClientRect.top < 0) {
                    mainNav.classList.add('is-stuck');
                } else {
                    mainNav.classList.remove('is-stuck');
                }
            },
            { threshold: [1], rootMargin: "0px 0px 0px 0px" }
        );
        observer.observe(mainNavSentinel);
    }

    // 2. Component Position Initializer
    function initComponentPosition() {
        if (isMobile()) {
            // Mobile: Đưa vào drawer menu (mainNavInner) nếu chưa có
            if (sharedMenu && trigger && mainNavInner && sharedMenu.parentNode !== mainNavInner) {
                trigger.parentNode.insertBefore(sharedMenu, trigger.nextSibling);
                sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--overlay');
                sharedMenu.classList.add('catalog-menu--mobile');
            }
        } else {
            // Desktop: Xác định mode dựa trên hero banner
            manageDesktopMode();
        }
    }

    function manageDesktopMode() {
        if (!sharedMenu) return;
        if (isMobile()) return;

        // Nếu ở trang chủ và chưa cuộn qua hết hero banner
        if (heroSlot) {
            const heroRect = heroSlot.getBoundingClientRect();
            // Header mainNav cao 50px
            // Nếu cạnh dưới của hero slot vẫn còn trên màn hình
            if (heroRect.bottom > 50) {
                // HERO MODE
                if (sharedMenu.parentNode !== heroSlot && !isOverlayOpen) {
                    heroSlot.appendChild(sharedMenu);
                    sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--overlay');
                    sharedMenu.classList.add('catalog-menu--hero');
                }
                return;
            }
        }
        
        // OVERLAY MODE (Cuộn qua hero hoặc không phải trang chủ)
        if (sharedMenu.parentNode !== overlaySlot && !isOverlayOpen) {
            overlaySlot.appendChild(sharedMenu);
            sharedMenu.classList.remove('catalog-menu--mobile', 'catalog-menu--hero');
            sharedMenu.classList.add('catalog-menu--overlay');
        }
    }

    initComponentPosition();

    window.addEventListener('resize', () => {
        if (isOverlayOpen && isMobile()) {
            closeOverlay();
        }
        initComponentPosition();
    });

    window.addEventListener('scroll', () => {
        if (!isMobile() && !isOverlayOpen) {
            manageDesktopMode();
        }
        if (isOverlayOpen && !isMobile()) {
            updateOverlayTop();
        }
    }, { passive: true });

    // 3. Scroll Lock bằng CSS Class
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

        // Cập nhật vị trí biến CSS cho overlay
        requestAnimationFrame(() => {
            updateOverlayTop();
            
            // Ép đưa menu vào overlay slot khi mở
            if (sharedMenu.parentNode !== overlaySlot) {
                overlaySlot.appendChild(sharedMenu);
                sharedMenu.classList.remove('catalog-menu--hero', 'catalog-menu--mobile');
                sharedMenu.classList.add('catalog-menu--overlay');
            }
            
            lockScroll();
            showOverlay();
        });
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
        
        unlockScroll();
        isOverlayOpen = false;
        
        // Trả component về đúng chỗ
        manageDesktopMode();
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
