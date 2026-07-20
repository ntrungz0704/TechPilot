/**
 * TechPilot News Module – news.js v1.2
 * Chỉ chạy khi có .news-page trong DOM.
 * Không dùng alert(), không làm nhảy layout.
 */

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('js');

    const newsPage = document.querySelector('.news-page');
    if (!newsPage) return; // Guard clause

    initCopyLink();
    initArticleToc();

    /* ── Article Table of Contents Mobile Toggle ───────────────────────── */
    function initArticleToc() {
        const toc = document.querySelector('.news-toc');
        if (!toc) return;

        const toggle = toc.querySelector('.news-toc-toggle');
        const list   = toc.querySelector('.news-toc-list');
        if (!toggle || !list) return;

        toggle.addEventListener('click', () => {
            const isOpen = toc.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    /* ── Copy Link ─────────────────────────────────────────────────────── */
    function initCopyLink() {
        const copyBtns = document.querySelectorAll('.copy-link-btn');
        if (!copyBtns.length) return;

        let toastTimeout = null;

        copyBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = window.location.href;

                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        // Fallback: tạo input tạm thời
                        const input = document.createElement('input');
                        input.style.cssText = 'position:fixed;top:-999px;left:-999px;opacity:0';
                        input.value = url;
                        document.body.appendChild(input);
                        input.select();
                        input.setSelectionRange(0, 99999);
                        document.execCommand('copy');
                        document.body.removeChild(input);
                    }
                    showToast('✓ Đã sao chép liên kết!');
                } catch (err) {
                    console.warn('[TechPilot] Copy failed:', err);
                    showToast('Không thể sao chép – vui lòng sao chép thủ công.');
                }
            });
        });

        function showToast(message) {
            let toast = document.getElementById('newsToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'newsToast';
                toast.className = 'toast-message';
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                toast.setAttribute('aria-atomic', 'true');
                document.body.appendChild(toast);
            }

            toast.textContent = message;
            toast.classList.add('show');

            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    }
});
