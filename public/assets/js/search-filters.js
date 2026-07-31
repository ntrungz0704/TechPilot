/**
 * TechPilot — Search Filter Chips Controller
 * Manages horizontal filter chips on the search/category page.
 */
(function() {
    'use strict';

    // Close all open dropdowns
    function closeAllDropdowns() {
        document.querySelectorAll('.filter-chip__dropdown.is-open').forEach(dd => {
            dd.classList.remove('is-open');
            dd.closest('.filter-chip').setAttribute('aria-expanded', 'false');
        });
    }

    // Toggle a specific dropdown
    function toggleDropdown(chip) {
        const dropdown = chip.querySelector('.filter-chip__dropdown');
        if (!dropdown) return;
        
        const isOpen = dropdown.classList.contains('is-open');
        closeAllDropdowns();
        
        if (!isOpen) {
            dropdown.classList.add('is-open');
            chip.setAttribute('aria-expanded', 'true');
        }
    }

    // Apply a filter value via URL params
    function applyFilter(key, value) {
        const url = new URL(window.location.href);
        if (value === '' || value === null) {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, value);
        }
        // Reset to page 1 when filtering
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Toggle a multi-value filter (comma-separated)
    function toggleMultiFilter(key, value) {
        const url = new URL(window.location.href);
        let current = url.searchParams.get(key) || '';
        let values = current ? current.split(',') : [];
        
        const idx = values.indexOf(value);
        if (idx >= 0) {
            values.splice(idx, 1);
        } else {
            values.push(value);
        }
        
        if (values.length > 0) {
            url.searchParams.set(key, values.join(','));
        } else {
            url.searchParams.delete(key);
        }
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Remove a specific filter
    function removeFilter(key) {
        applyFilter(key, null);
    }

    // Clear all filters (keep only cat and q)
    function clearAllFilters() {
        const url = new URL(window.location.href);
        const q = url.searchParams.get('q');
        const cat = url.searchParams.get('cat');
        
        // Remove all params
        for (const key of [...url.searchParams.keys()]) {
            url.searchParams.delete(key);
        }
        
        // Keep q and cat
        if (q) url.searchParams.set('q', q);
        if (cat) url.searchParams.set('cat', cat);
        
        window.location.href = url.toString();
    }

    // Initialize event listeners
    function init() {
        // Chip click handlers
        document.querySelectorAll('.filter-chip[data-dropdown]').forEach(chip => {
            chip.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown(this);
            });
        });

        // Dropdown item click handlers
        document.querySelectorAll('.filter-chip__dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const key = this.dataset.filterKey;
                const value = this.dataset.filterValue;
                const isMulti = this.dataset.multi === 'true';
                
                if (isMulti) {
                    toggleMultiFilter(key, value);
                } else {
                    applyFilter(key, value);
                }
            });
        });

        // Quick filter chips (non-dropdown)
        document.querySelectorAll('.filter-chip[data-filter-key]').forEach(chip => {
            if (chip.hasAttribute('data-dropdown')) return;
            chip.addEventListener('click', function() {
                const key = this.dataset.filterKey;
                const value = this.dataset.filterValue;
                
                if (this.classList.contains('is-active')) {
                    removeFilter(key);
                } else {
                    applyFilter(key, value);
                }
            });
        });

        // Active filter tag removal
        document.querySelectorAll('.active-filter-tag__remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const key = this.dataset.removeKey;
                removeFilter(key);
            });
        });

        // Clear all filters button
        document.querySelectorAll('.clear-all-filters').forEach(btn => {
            btn.addEventListener('click', clearAllFilters);
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.filter-chip')) {
                closeAllDropdowns();
            }
        });

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    }

    // Expose for external use
    window.TechPilotFilters = {
        applyFilter,
        toggleMultiFilter,
        removeFilter,
        clearAllFilters
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
