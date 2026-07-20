document.addEventListener('DOMContentLoaded', () => {
    const categoryMenuButton = document.getElementById('categoryMenuButton');
    const categoryMegaMenu   = document.getElementById('categoryMegaMenu');
    const categoryBackdrop   = document.getElementById('categoryBackdrop');
    const sharedCategoryMenu = document.getElementById('sharedCategoryMenu');

    if (!categoryMenuButton || !categoryMegaMenu || !categoryBackdrop) {
        return;
    }

    const menuItems = sharedCategoryMenu
        ? Array.from(sharedCategoryMenu.querySelectorAll('.vertical-menu__item'))
        : [];

    // 1. STATE MACHINE (Single Source of Truth)
    function openCategoryMenu() {
        categoryMegaMenu.hidden = false;
        categoryBackdrop.hidden = false;
        categoryMegaMenu.classList.add('is-open');
        categoryBackdrop.classList.add('is-open');
        categoryMenuButton.setAttribute('aria-expanded', 'true');
        document.body.classList.add('category-scroll-locked');
    }

    function closeCategoryMenu() {
        categoryMegaMenu.hidden = true;
        categoryBackdrop.hidden = true;
        categoryMegaMenu.classList.remove('is-open');
        categoryBackdrop.classList.remove('is-open');
        categoryMenuButton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('category-scroll-locked');
        clearActiveItems();
    }

    function toggleCategoryMenu() {
        if (!categoryMegaMenu.hidden) {
            closeCategoryMenu();
        } else {
            openCategoryMenu();
        }
    }

    function activateItem(item) {
        menuItems.forEach(i => i.classList.toggle('is-active', i === item));
    }

    function clearActiveItems() {
        menuItems.forEach(i => i.classList.remove('is-active'));
    }

    // Expose API for external handlers
    window.openCategoryMenu   = openCategoryMenu;
    window.closeCategoryMenu  = closeCategoryMenu;
    window.toggleCategoryMenu = toggleCategoryMenu;

    // 2. EVENT LISTENERS
    // Button click -> toggle
    categoryMenuButton.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleCategoryMenu();
    });

    // Keep menu open while hovering inside the mega menu panel
    categoryMegaMenu.addEventListener('mouseenter', () => {
        clearTimeout(hoverCloseTimer);
    });

    // Schedule close with a delay to allow mouse to travel between button and menu
    function scheduleClose() {
        hoverCloseTimer = setTimeout(() => {
            closeCategoryMenu();
        }, 220);
    }

    categoryMenuButton.addEventListener('mouseleave', scheduleClose);
    categoryMegaMenu.addEventListener('mouseleave', scheduleClose);

    // Backdrop click -> close
    categoryBackdrop.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeCategoryMenu();
    });

    // Click outside -> close
    document.addEventListener('click', (e) => {
        if (categoryMegaMenu.hidden) return;
        const isClickInsideMenu = categoryMegaMenu.contains(e.target);
        const isClickInsideButton = categoryMenuButton.contains(e.target);
        if (!isClickInsideMenu && !isClickInsideButton) {
            closeCategoryMenu();
        }
    });

    // Stop propagation inside mega menu except navigation links
    categoryMegaMenu.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link) {
            // Clicked a navigation link -> close menu and let browser navigate
            closeCategoryMenu();
        } else {
            e.stopPropagation();
        }
    });

    // Keyboard Escape -> close and return focus to button
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !categoryMegaMenu.hidden) {
            closeCategoryMenu();
            categoryMenuButton.focus();
        }
    });

    // Mouse hover over category items on desktop
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            if (!categoryMegaMenu.hidden) {
                activateItem(item);
            }
        });

        // Mobile accordion toggle
        const toggleBtn = item.querySelector('.mobile-category-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isExpanded = item.classList.contains('is-expanded');
                item.classList.toggle('is-expanded', !isExpanded);
                toggleBtn.setAttribute('aria-expanded', !isExpanded ? 'true' : 'false');
            });
        }
    });

    // Resize window -> close menu if switching to mobile or screen changes
    window.addEventListener('resize', () => {
        if (!categoryMegaMenu.hidden && window.innerWidth <= 575) {
            closeCategoryMenu();
        }
    });

    // Close menu on page load / bfcache restore
    window.addEventListener('pageshow', () => {
        closeCategoryMenu();
    });

    // Initial State: Hidden by default
    closeCategoryMenu();
});