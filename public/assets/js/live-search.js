/**
 * TechPilot Live Instant Search (Universal Autocomplete)
 * Gõ chữ tới đâu hiển thị gợi ý sản phẩm ngay lập tức tới đó trên toàn hệ thống
 */
document.addEventListener('DOMContentLoaded', function () {
    const searchInputs = document.querySelectorAll(
        '#headerSearchForm input[name="q"], .mobile-search-bar input[name="q"], .header__search-input, form.search-bar input, input[name="search"]'
    );
    if (!searchInputs.length) return;

    const baseUrl = (window.APP_BASE_URL || '').replace(/\/$/, '');
    const defaultPlaceholder = baseUrl + '/assets/images/placeholders/laptop.png';

    // Helper escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function (m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m];
        });
    }

    // Highlight từ khóa khớp
    function highlightMatch(text, query) {
        if (!query || !text) return escapeHtml(text);
        const escapedText = escapeHtml(text);
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapedText.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    searchInputs.forEach(input => {
        if (input.dataset.liveSearchInitialized) return;
        input.dataset.liveSearchInitialized = 'true';
        input.setAttribute('autocomplete', 'off');

        const form = input.closest('form') || input.parentElement;
        let debounceTimer = null;
        let activeIndex = -1;
        let currentResults = [];

        // Tạo container Autocomplete Dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'search-autocomplete-dropdown';
        dropdown.setAttribute('role', 'listbox');
        dropdown.style.display = 'none';

        // Gắn dropdown đúng vị trí container có position relative
        form.style.position = 'relative';
        form.appendChild(dropdown);

        // Lấy danh mục được chọn (nếu có)
        function getSelectedCategory() {
            const catSelect = form.querySelector('select[name="cat"]');
            return catSelect ? catSelect.value : '';
        }

        // Đóng dropdown
        function closeDropdown() {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            activeIndex = -1;
            currentResults = [];
        }

        // Trạng thái đang tải
        function renderLoading(keyword) {
            dropdown.innerHTML = `
                <div class="search-autocomplete-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i> Đang tìm kiếm sản phẩm cho "<strong>${escapeHtml(keyword)}</strong>"...
                </div>
            `;
            dropdown.style.display = 'block';
        }

        // Render danh sách sản phẩm
        function renderResults(data, keyword) {
            const products = data.products || [];
            const total = data.total || 0;
            currentResults = products;
            activeIndex = -1;

            if (!products.length) {
                dropdown.innerHTML = `
                    <div class="search-autocomplete-empty">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <p>Không tìm thấy sản phẩm phù hợp với "<strong>${escapeHtml(keyword)}</strong>"</p>
                        <span class="empty-sub">Thử tìm kiếm từ khóa khác như "Laptop", "RTX 4060", "B760"...</span>
                    </div>
                `;
                dropdown.style.display = 'block';
                return;
            }

            let html = `<div class="search-autocomplete-header">Gợi ý sản phẩm cho "<strong>${escapeHtml(keyword)}</strong>" (${total} kết quả)</div>`;
            html += '<div class="search-autocomplete-list">';

            products.forEach((p, idx) => {
                const imgSrc = p.image || defaultPlaceholder;
                const discountHtml = p.has_discount && p.discount_percent > 0 
                    ? `<span class="product-badge-discount">-${p.discount_percent}%</span>` 
                    : '';
                const origPriceHtml = p.has_discount && p.original_price_formatted 
                    ? `<span class="search-item-price-old">${p.original_price_formatted}</span>` 
                    : '';

                html += `
                    <a href="${p.url}" class="search-autocomplete-item" data-index="${idx}" role="option">
                        <div class="search-item-thumb">
                            <img src="${imgSrc}" alt="" onerror="this.onerror=null;this.src='${defaultPlaceholder}';">
                        </div>
                        <div class="search-item-info">
                            ${p.category_name ? `<span class="search-item-cat">${escapeHtml(p.category_name)}</span>` : ''}
                            <div class="search-item-name">${highlightMatch(p.name, keyword)}</div>
                            <div class="search-item-price-row">
                                <span class="search-item-price-new">${p.price_formatted}</span>
                                ${origPriceHtml}
                                ${discountHtml}
                            </div>
                        </div>
                    </a>
                `;
            });

            html += '</div>';

            // Footer link xem tất cả kết quả
            const catVal = getSelectedCategory();
            let searchUrl = `${baseUrl}/home/search?q=${encodeURIComponent(keyword)}`;
            if (catVal) searchUrl += `&cat=${encodeURIComponent(catVal)}`;

            html += `
                <a href="${searchUrl}" class="search-autocomplete-footer" onclick="if(window.cleanSearchParams && this.closest('form')){cleanSearchParams(this.closest('form'));}">
                    <span>Xem tất cả ${total} sản phẩm</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            `;

            dropdown.innerHTML = html;
            dropdown.style.display = 'block';
        }

        // Thực hiện lệnh Fetch
        function performSearch() {
            const val = input.value.trim();
            if (!val) {
                closeDropdown();
                return;
            }

            renderLoading(val);

            const cat = getSelectedCategory();
            let apiUrl = `${baseUrl}/home/ajaxSearch?q=${encodeURIComponent(val)}`;
            if (cat) apiUrl += `&cat=${encodeURIComponent(cat)}`;

            fetch(apiUrl)
                .then(res => {
                    if (!res.ok) throw new Error('Network response failed');
                    return res.json();
                })
                .then(data => {
                    if (input.value.trim() === val) {
                        renderResults(data, val);
                    }
                })
                .catch(err => {
                    console.error('Live search error:', err);
                    closeDropdown();
                });
        }

        // Sự kiện gõ phím (input) -> Debounce 120ms
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            if (!this.value.trim()) {
                closeDropdown();
                return;
            }
            debounceTimer = setTimeout(performSearch, 120);
        });

        // Sự kiện Focus -> Hiện lại nếu có giá trị
        input.addEventListener('focus', function () {
            if (this.value.trim()) {
                performSearch();
            }
        });

        // Điều hướng phím mũi tên & Enter
        input.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.search-autocomplete-item');
            if (!items.length || dropdown.style.display === 'none') return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateHighlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateHighlight(items);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                const selectedItem = items[activeIndex];
                if (selectedItem) {
                    window.location.href = selectedItem.getAttribute('href');
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        function updateHighlight(items) {
            items.forEach((item, idx) => {
                if (idx === activeIndex) {
                    item.classList.add('is-active');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('is-active');
                }
            });
        }

        // Click ngoài -> Đóng dropdown
        document.addEventListener('click', function (e) {
            if (!form.contains(e.target) && !dropdown.contains(e.target)) {
                closeDropdown();
            }
        });
    });
});
