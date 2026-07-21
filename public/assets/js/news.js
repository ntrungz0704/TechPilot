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
    initReadingProgress();

    /* ── Reading Progress Bar ──────────────────────────────────────────── */
    function initReadingProgress() {
        const progressBar = document.getElementById('readingProgressBar');
        const article = document.querySelector('.news-detail-content');
        if (!progressBar || !article) return;

        window.addEventListener('scroll', () => {
            const rect = article.getBoundingClientRect();
            const topPos = rect.top;
            const height = rect.height;
            const windowHeight = window.innerHeight;
            
            let progress = 0;
            if (topPos < windowHeight / 2) {
                const scrolled = (windowHeight / 2) - topPos;
                progress = (scrolled / height) * 100;
            }
            
            progress = Math.max(0, Math.min(100, progress));
            progressBar.style.width = `${progress}%`;
        }, { passive: true });
    }

    /* ── Article Table of Contents Mobile Toggle ───────────────────────── */
    function initArticleToc() {
        const toc = document.querySelector('.news-toc');
        if (!toc) return;

        const toggle = toc.querySelector('.news-toc-toggle');
        const list   = toc.querySelector('.news-toc-list');
        if (toggle && list) {
            toggle.addEventListener('click', () => {
                const isOpen = toc.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        const tocLinks = toc.querySelectorAll('.news-toc-item a');
        if (!tocLinks.length) return;

        const headings = Array.from(tocLinks).map(link => {
            const id = link.getAttribute('href').substring(1);
            return document.getElementById(id);
        }).filter(h => h !== null);

        if (!headings.length) return;

        const observerOptions = {
            rootMargin: '-80px 0px -80% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver(entries => {
            let activeId = null;
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    activeId = entry.target.id;
                }
            });
            
            if (activeId) {
                const activeLink = toc.querySelector(`.news-toc-item a[href="#${activeId}"]`);
                if (activeLink) {
                    toc.querySelectorAll('.news-toc-item').forEach(item => item.classList.remove('is-active'));
                    activeLink.parentElement.classList.add('is-active');
                }
            }
        }, observerOptions);

        headings.forEach(heading => observer.observe(heading));
    }

    /* ── Copy Link & Web Share API ─────────────────────────────────────── */
    function initCopyLink() {
        const copyBtns = document.querySelectorAll('.copy-link-btn');
        if (!copyBtns.length) return;

        let toastTimeout = null;

        copyBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = window.location.href;
                const title = document.title;

                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: title,
                            url: url
                        });
                        return; // Đã share thành công
                    } catch (err) {
                        // Nếu user bấm cancel thì bỏ qua, lỗi khác thì log
                        if (err.name !== 'AbortError') {
                            console.warn('[TechPilot] Share failed:', err);
                        } else {
                            return;
                        }
                    }
                }

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
