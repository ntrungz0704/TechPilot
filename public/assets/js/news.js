/**
 * TechPilot News Module – news.js v2.0
 * Chỉ chạy khi có .news-page trong DOM.
 * Không dùng alert(), không làm nhảy layout.
 */

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('js');

    const newsPage = document.querySelector('.news-page');
    if (!newsPage) return; // Guard clause – không chạy trên trang không phải news

    initReadingProgress();
    initArticleToc();
    initShareButton();
    initCopyLink();

    /* ── Reading Progress Bar ───────────────────────────────────────────────
     * Tính tiến độ dựa trên article start và end.
     * Dùng requestAnimationFrame để throttle.
     * Không transition nếu prefers-reduced-motion.
     */
    function initReadingProgress() {
        const progressBar = document.getElementById('readingProgressBar');
        const article     = document.querySelector('.news-detail-content');
        if (!progressBar || !article) return;

        // Tắt transition nếu người dùng prefer reduced motion
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReduced) {
            progressBar.style.transition = 'none';
        }

        let rafScheduled = false;

        function updateProgress() {
            rafScheduled = false;
            const articleTop    = article.getBoundingClientRect().top + window.scrollY;
            const articleBottom = articleTop + article.offsetHeight;
            const scrollTop     = window.scrollY;
            const range         = articleBottom - articleTop;

            let progress = 0;
            if (range > 0) {
                progress = ((scrollTop - articleTop) / range) * 100;
            }

            progress = Math.max(0, Math.min(100, progress));
            progressBar.style.width = `${progress}%`;
        }

        function onScroll() {
            if (!rafScheduled) {
                rafScheduled = true;
                requestAnimationFrame(updateProgress);
            }
        }

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', updateProgress, { passive: true });

        // Tính ngay khi khởi tạo (ví dụ user reload giữa trang)
        updateProgress();
    }

    /* ── Article Table of Contents – Active tracking ────────────────────────
     * Dùng IntersectionObserver với rootMargin để bắt heading đang đọc.
     * Thuật toán ổn định: duy trì tập heading đang visible và
     * heading cuối cùng đã qua top viewport (fallback cuộn ngược).
     */
    function initArticleToc() {
        const toc = document.querySelector('.news-toc');
        if (!toc) return;

        // Mobile collapse toggle
        const toggle = toc.querySelector('.news-toc-toggle');
        const list   = toc.querySelector('.news-toc-list');
        if (toggle && list) {
            toggle.addEventListener('click', () => {
                const isOpen = toc.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        // IntersectionObserver guard
        if (!('IntersectionObserver' in window)) {
            return;
        }

        const tocLinks = toc.querySelectorAll('.news-toc-item a[href^="#"]');
        if (!tocLinks.length) return;

        const headingEls = Array.from(tocLinks).map(link => {
            const id = link.getAttribute('href').substring(1);
            return document.getElementById(id);
        }).filter(h => h !== null);

        if (!headingEls.length) return;

        // Tập heading đang nằm trong vùng nhìn thấy (visible set)
        const visibleSet = new Set();
        // Heading cuối cùng đã đi qua top của viewport (fallback cuộn lên)
        let lastPassedHeadingId = null;

        const observerOptions = {
            rootMargin: '-80px 0px -60% 0px',
            threshold:  0,
        };

        function setActiveById(id) {
            toc.querySelectorAll('.news-toc-item').forEach(item => item.classList.remove('is-active'));
            const link = toc.querySelector(`.news-toc-item a[href="#${CSS.escape(id)}"]`);
            if (link) {
                link.parentElement.classList.add('is-active');
            }
        }

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                const id = entry.target.id;
                if (entry.isIntersecting) {
                    visibleSet.add(id);
                    lastPassedHeadingId = id;
                } else {
                    visibleSet.delete(id);
                    // Nếu heading đi ra phía trên viewport → cập nhật lastPassed
                    if (entry.boundingClientRect.top < 0) {
                        lastPassedHeadingId = id;
                    }
                }
            });

            // Ưu tiên heading đầu tiên trong visible set (theo thứ tự DOM)
            let activeId = null;
            for (const el of headingEls) {
                if (visibleSet.has(el.id)) {
                    activeId = el.id;
                    break;
                }
            }

            // Fallback: dùng heading cuối cùng đã đi qua viewport
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
     * Nút .share-native-btn gọi navigator.share().
     * Nếu không hỗ trợ → fallback mở Facebook share.
     * Nếu user cancel (AbortError) → không báo lỗi.
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
                        console.warn('[TechPilot] navigator.share failed:', err);
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
     * Nút .copy-link-btn luôn copy URL.
     * Không bao giờ gọi navigator.share().
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
