document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('categoryMenuToggle');
    const dropdown = document.getElementById('categoryMegaDropdown') || document.getElementById('categoryStaticMenu');
    const overlay = document.getElementById('categoryMenuOverlay');
    
    if (!toggleBtn || !dropdown) return;

    let isOpen = false;
    let hoverTimeout = null;
    let activeRow = null;
    let activePanelId = null;

    const sidebarItems = dropdown.querySelectorAll('.category-sidebar__item');
    const megaPanels = dropdown.querySelectorAll('.category-mega__panel');

    // 1. OPEN / CLOSE MENU
    function openMenu() {
        isOpen = true;
        
        // Cập nhật ARIA
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.classList.add('is-active');
        
        if (overlay) overlay.hidden = false;
        
        if (!dropdown.classList.contains('is-static')) {
            dropdown.hidden = false;
            document.body.classList.add('category-scroll-locked');
        } else {
            dropdown.classList.add('is-highlighted');
        }

        // Reset trạng thái hover khi mở
        if (sidebarItems.length > 0) {
            activatePanel(sidebarItems[0]);
        }
    }

    function closeMenu() {
        isOpen = false;
        
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.classList.remove('is-active');
        
        if (overlay) overlay.hidden = true;
        
        if (!dropdown.classList.contains('is-static')) {
            dropdown.hidden = true;
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

    // Toggle click
    toggleBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleMenu();
    });

    // Đóng khi click ra ngoài hoặc click overlay
    overlay.addEventListener('click', closeMenu);

    document.addEventListener('click', (e) => {
        if (isOpen && !dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
            closeMenu();
        }
    });

    // Đóng khi ấn Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) {
            closeMenu();
            toggleBtn.focus();
        }
    });

    // 2. STATE MACHINE CHO MOUSE HOVER TRONG SIDEBAR
    function activatePanel(itemEl) {
        if (!itemEl) return;
        const targetId = itemEl.getAttribute('data-panel-id');
        if (targetId === activePanelId) return;

        // Xóa class active cũ
        if (activeRow) {
            activeRow.classList.remove('is-active');
        }
        megaPanels.forEach(p => p.classList.remove('is-active'));

        // Set class active mới
        activeRow = itemEl;
        activePanelId = targetId;
        activeRow.classList.add('is-active');
        
        const targetPanel = document.getElementById(targetId);
        if (targetPanel) {
            targetPanel.classList.add('is-active');
        }
        
        // Cập nhật state cho container (để static mode biết có mở panel)
        dropdown.classList.add('has-active-panel');
    }

    function deactivateAll() {
        if (activeRow) {
            activeRow.classList.remove('is-active');
        }
        megaPanels.forEach(p => p.classList.remove('is-active'));
        activeRow = null;
        activePanelId = null;
        dropdown.classList.remove('has-active-panel');
    }

    // Trên desktop static mode, khi chuột rời khỏi toàn bộ component thì đóng panel
    dropdown.addEventListener('mouseleave', () => {
        if (dropdown.classList.contains('is-static')) {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            deactivateAll();
        }
    });

    sidebarItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            // Dùng timeout nhỏ để chống flicker khi lướt chéo
            if (hoverTimeout) clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                activatePanel(item);
            }, 100); 
        });

        item.addEventListener('mouseleave', () => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
        });

        // Bật bằng keyboard
        item.addEventListener('focus', () => {
            activatePanel(item);
        });
    });

    // Ngăn chặn sự cố khi con trỏ di chuyển vào panel
    dropdown.querySelector('.category-dropdown__mega')?.addEventListener('mouseenter', () => {
        if (hoverTimeout) clearTimeout(hoverTimeout);
    });
});