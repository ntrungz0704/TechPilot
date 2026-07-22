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
    initRelatedCarousel();

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
     * 1. Khởi tạo toggle listener riêng cho từng TOC.
     * 2. Lấy danh sách headings được tham chiếu bởi các TOC link.
     * 3. Theo dõi cuộn trang (scroll position) để highlight chính xác 100% mục đang đọc.
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
            .filter(h => h !== null)
            .sort((a, b) => a.offsetTop - b.offsetTop);

        if (!headingEls.length) return;

        // ── Bước 3: Highlight TOC Active theo vị trí viewport thực tế ──────────
        function setActiveById(id) {
            document.querySelectorAll('.news-toc-item').forEach(item => item.classList.remove('is-active'));
            if (!id) return;
            const escapedId = id.replace(/[!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~]/g, '\\$&');
            document.querySelectorAll(`.news-toc-item a[href="#${escapedId}"]`).forEach(link => {
                const item = link.closest('.news-toc-item');
                if (item) {
                    item.classList.add('is-active');
                }
            });
        }

        let ticking = false;

        function updateActiveHeading() {
            const topThreshold = 160; // Thượng giới cách đỉnh viewport 160px (dưới sticky header)
            let currentId = null;

            for (let i = 0; i < headingEls.length; i++) {
                const rect = headingEls[i].getBoundingClientRect();
                if (rect.top <= topThreshold) {
                    currentId = headingEls[i].id;
                } else {
                    break;
                }
            }

            // Nếu cuộn gần chạm đáy bài viết, tự động highlight mục cuối
            const scrollBottom = window.innerHeight + window.scrollY;
            const docHeight    = document.documentElement.scrollHeight;
            if (scrollBottom >= docHeight - 150) {
                if (headingEls.length > 0) {
                    currentId = headingEls[headingEls.length - 1].id;
                }
            }

            if (!currentId && headingEls.length > 0) {
                currentId = headingEls[0].id;
            }

            setActiveById(currentId);
            ticking = false;
        }

        function onScroll() {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(updateActiveHeading);
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });

        // Chạy ngay khi tải trang
        updateActiveHeading();
    }

    /* ── Unified Share Button ───────────────────────────────────────────────
     * .share-unified-btn tự động copy URL bài viết, hiển thị Toast và feedback hiệu ứng nút.
     * Trên thiết bị di động (Mobile/Tablet) sẽ mở thêm menu chia sẻ gốc (navigator.share).
     */
    function initShareButton() {
        const shareBtns = document.querySelectorAll('.share-unified-btn, .share-native-btn, .copy-link-btn');
        if (!shareBtns.length) return;

        shareBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url   = window.location.href;
                const title = document.title;
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                // 1. Thử gọi Web Share API trên điện thoại di động
                if (isMobile && navigator.share) {
                    try {
                        await navigator.share({ title, url });
                        return;
                    } catch (err) {
                        if (err.name === 'AbortError') return; // User cancelled – silent
                        console.warn('[TechPilot] navigator.share error:', err);
                    }
                }

                // 2. Tự động Copy link vào clipboard & hiển thị Toast + Nút Feedback
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        const input = document.createElement('input');
                        input.style.cssText = 'position:fixed;top:-999px;left:-999px;opacity:0';
                        input.value = url;
                        document.body.appendChild(input);
                        input.select();
                        input.setSelectionRange(0, 99999);
                        document.execCommand('copy');
                        document.body.removeChild(input);
                    }

                    showToast('✓ Đã sao chép liên kết bài viết!');

                    // UI Feedback đổi icon và text tạm thời
                    const icon = btn.querySelector('i');
                    const textSpan = btn.querySelector('span');
                    const originalText = textSpan ? textSpan.textContent : '';

                    if (icon) icon.className = 'fa-solid fa-check';
                    if (textSpan) textSpan.textContent = 'Đã sao chép link!';

                    setTimeout(() => {
                        if (icon) icon.className = 'fa-solid fa-share-nodes';
                        if (textSpan && originalText) textSpan.textContent = originalText;
                    }, 2500);

                } catch (err) {
                    console.warn('[TechPilot] Copy failed:', err);
                    showToast('Không thể sao chép – vui lòng copy link thủ công.');
                }
            });
        });
    }

    function initCopyLink() {
        // Sub-handler integrated into initShareButton for unified performance
    }

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

    /* ── Horizontal Related Posts Carousel (Nav Arrows + Mouse Drag Scroll) ── */
    function initRelatedCarousel() {
        const section = document.querySelector('.news-related-bottom-section');
        if (!section) return;

        const track   = section.querySelector('.news-related-carousel-track');
        const prevBtn = section.querySelector('.news-carousel-prev');
        const nextBtn = section.querySelector('.news-carousel-next');

        if (!track) return;

        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => {
                const cardWidth = track.firstElementChild ? track.firstElementChild.offsetWidth + 20 : 300;
                track.scrollBy({ left: -cardWidth * 2, behavior: 'smooth' });
            });

            nextBtn.addEventListener('click', () => {
                const cardWidth = track.firstElementChild ? track.firstElementChild.offsetWidth + 20 : 300;
                track.scrollBy({ left: cardWidth * 2, behavior: 'smooth' });
            });
        }

        // Mouse Drag to Scroll
        let isDown = false;
        let startX;
        let scrollLeft;

        track.addEventListener('mousedown', (e) => {
            isDown = true;
            track.classList.add('is-dragging');
            startX = e.pageX - track.offsetLeft;
            scrollLeft = track.scrollLeft;
        });

        track.addEventListener('mouseleave', () => {
            isDown = false;
            track.classList.remove('is-dragging');
        });

        track.addEventListener('mouseup', () => {
            isDown = false;
            track.classList.remove('is-dragging');
        });

        track.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - track.offsetLeft;
            const walk = (x - startX) * 1.8;
            track.scrollLeft = scrollLeft - walk;
        });
    }
});
