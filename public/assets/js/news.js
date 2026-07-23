/**
 * TechPilot News Module – news.js v2.1
 * Chỉ chạy khi có .news-page trong DOM.
 * Không dùng alert(), không làm nhảy layout.
 */

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('js');

    const newsPage = document.querySelector('.news-page');
    if (!newsPage) return; // Guard – không chạy trên trang không phải news

    initReadingProgress();
    initArticleToc();
    initShareButton();
    initCopyLink();

    /* ── Reading Progress Bar ───────────────────────────────────────────────
     * Logic đúng:
     *   - Bắt đầu đếm khi user cuộn qua đầu article.
     *   - Kết thúc khi article bottom đến bottom của viewport.
     *   - Xử lý bài ngắn hơn viewport (progress = 100% ngay khi article khớp viewport).
     *   - rAF throttle với cờ `ticking`.
     *   - Prefers-reduced-motion → transition: none (qua CSS).
     *   - Lắng nghe scroll, resize, và image load trong article.
     */
    function initReadingProgress() {
        const progressBar = document.getElementById('readingProgressBar');
        const article     = document.querySelector('.news-detail-content');
        if (!progressBar || !article) return;

        let ticking = false;

        function updateProgress() {
            ticking = false;

            const rect          = article.getBoundingClientRect();
            const articleTop    = rect.top + window.scrollY;    // offset từ top document
            const articleHeight = article.offsetHeight;
            const viewportH     = window.innerHeight;

            // Scrollable range: từ khi article bắt đầu vào viewport trên cùng
            // đến khi article bottom đến bottom viewport.
            const scrollStart = articleTop;
            const scrollEnd   = articleTop + articleHeight - viewportH;

            let progress = 0;
            if (scrollEnd <= scrollStart) {
                // Bài ngắn hơn viewport → 100% khi article đã hiển thị đủ
                progress = rect.top <= 0 ? 100 : 0;
            } else {
                progress = ((window.scrollY - scrollStart) / (scrollEnd - scrollStart)) * 100;
            }

            progress = Math.max(0, Math.min(100, progress));
            progressBar.style.width = `${progress}%`;
        }

        function scheduleUpdate() {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(updateProgress);
            }
        }

        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate, { passive: true });

        // Khi ảnh trong article load xong, chiều cao thay đổi → tính lại
        article.querySelectorAll('img').forEach(img => {
            if (!img.complete) {
                img.addEventListener('load', scheduleUpdate, { once: true });
            }
        });

        // Tính ngay lập tức (ví dụ user reload giữa trang)
        updateProgress();
    }

    /* ── Article Table of Contents – Dual TOC & Active tracking ─────────────
     * Support cả Mobile Accordion TOC & Desktop Sticky Sidebar TOC:
     *   1. Khởi tạo toggle listener riêng cho từng TOC.
     *   2. Lấy danh sách headings được tham chiếu bởi các TOC link.
     *   3. Dùng 1 shared IntersectionObserver duy nhất để theo dõi viewport.
     *   4. Cập nhật active class đồng bộ cho tất cả TOC links (querySelectorAll).
     */
    function initArticleToc() {
        const tocs = document.querySelectorAll('.news-toc');
        if (!tocs.length) return;

        // ── Bước 1: Toggle listener riêng cho từng TOC ─────────────────────
        tocs.forEach(toc => {
            const toggle = toc.querySelector('.news-toc-toggle');
            const list   = toc.querySelector('.news-toc-list');
            if (toggle && list) {
                toggle.addEventListener('click', () => {
                    const isOpen = toc.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }
        });

        // ── Bước 2: Gom toàn bộ TOC links & Target Headings ────────────────
        const allTocLinks = document.querySelectorAll('.news-toc-item a[href^="#"]');
        if (!allTocLinks.length) return;

        const headingIdSet = new Set();
        allTocLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.length > 1) {
                headingIdSet.add(href.substring(1));
            }
        });

        const headingEls = Array.from(headingIdSet)
            .map(id => document.getElementById(id))
            .filter(h => h !== null);

        if (!headingEls.length) return;

        // ── Bước 3: Kiểm tra IntersectionObserver support ──────────────────
        if (!('IntersectionObserver' in window)) {
            return;
        }

        // ── Bước 4: Shared Active tracking ─────────────────────────────────
        const visibleSet = new Set();
        let lastPassedHeadingId = null;

        const observerOptions = {
            rootMargin: '-80px 0px -60% 0px',
            threshold:  0,
        };

        function setActiveById(id) {
            document.querySelectorAll('.news-toc-item').forEach(item => item.classList.remove('is-active'));
            if (!id) return;
            const escapedId = id.replace(/[!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~]/g, '\\$&');
            document.querySelectorAll(`.news-toc-item a[href="#${escapedId}"]`).forEach(link => {
                if (link.parentElement) {
                    link.parentElement.classList.add('is-active');
                }
            });
        }

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                const id = entry.target.id;
                if (entry.isIntersecting) {
                    visibleSet.add(id);
                    lastPassedHeadingId = id;
                } else {
                    visibleSet.delete(id);
                    if (entry.boundingClientRect.top < 0) {
                        lastPassedHeadingId = id;
                    }
                }
            });

            let activeId = null;
            for (const el of headingEls) {
                if (visibleSet.has(el.id)) {
                    activeId = el.id;
                    break;
                }
            }

            if (!activeId && lastPassedHeadingId) {
                activeId = lastPassedHeadingId;
            }

            if (activeId) {
                setActiveById(activeId);
            }
        }, observerOptions);

        headingEls.forEach(el => observer.observe(el));
    }

    /* ── Share Button (navigator.share) ─────────────────────────────────────
     * .share-native-btn gọi navigator.share().
     * User cancel (AbortError) → silent, không toast.
     * Không hỗ trợ → fallback Facebook share URL.
     * Không giả vờ đã share nếu chỉ copy.
     */
    function initShareButton() {
        const shareBtns = document.querySelectorAll('.share-native-btn');
        if (!shareBtns.length) return;

        shareBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url   = window.location.href;
                const title = document.title;

                if (navigator.share) {
                    try {
                        await navigator.share({ title, url });
                        return;
                    } catch (err) {
                        if (err.name === 'AbortError') return; // User cancelled – silent
                        // Lỗi khác → fallback tiếp
                        console.warn('[TechPilot] navigator.share error:', err);
                    }
                }

                // Fallback: mở Facebook share URL
                const fbUrl = 'https://www.facebook.com/sharer/sharer.php?u='
                    + encodeURIComponent(url);
                window.open(fbUrl, '_blank', 'noopener,noreferrer');
            });
        });
    }

    /* ── Copy Link Button ────────────────────────────────────────────────────
     * .copy-link-btn LUÔN copy URL vào clipboard.
     * Tuyệt đối không gọi navigator.share().
     * ARIA label: "Sao chép liên kết bài viết".
     */
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
                        // Fallback execCommand cho trình duyệt cũ / HTTP context
                        const input = document.createElement('input');
                        input.style.cssText = 'position:fixed;top:-999px;left:-999px;opacity:0';
                        input.value = url;
                        document.body.appendChild(input);
                        input.select();
                        input.setSelectionRange(0, 99999);
                        // eslint-disable-next-line no-restricted-syntax
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
