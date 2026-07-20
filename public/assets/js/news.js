/**
 * TechPilot News Module JavaScript
 * Isolated functionality for .news-page
 */

document.addEventListener('DOMContentLoaded', () => {
    const newsPage = document.querySelector('.news-page');
    if (!newsPage) return; // Guard clause

    initFAQ();
    initCopyLink();
    initMobileTOC();

    // --- Functions --- //

    function initFAQ() {
        const faqButtons = document.querySelectorAll('.faq-button');
        faqButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const contentId = this.getAttribute('aria-controls');
                const content = document.getElementById(contentId);

                // Close others (optional, comment out for multiple open)
                /*
                faqButtons.forEach(otherBtn => {
                    if (otherBtn !== this) {
                        otherBtn.setAttribute('aria-expanded', 'false');
                        const otherContent = document.getElementById(otherBtn.getAttribute('aria-controls'));
                        if (otherContent) otherContent.style.display = 'none';
                    }
                });
                */

                this.setAttribute('aria-expanded', !isExpanded);
                if (content) {
                    content.style.display = isExpanded ? 'none' : 'block';
                }
            });
        });
    }

    function initCopyLink() {
        const copyBtns = document.querySelectorAll('.copy-link-btn');
        let toastTimeout;

        copyBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    await navigator.clipboard.writeText(window.location.href);
                    showToast('Đã sao chép liên kết!');
                } catch (err) {
                    console.error('Failed to copy!', err);
                    // Fallback
                    const input = document.createElement('input');
                    input.value = window.location.href;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    document.body.removeChild(input);
                    showToast('Đã sao chép liên kết!');
                }
            });
        });

        function showToast(message) {
            let toast = document.getElementById('newsToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'newsToast';
                toast.className = 'toast-message';
                document.querySelector('.news-page').appendChild(toast);
            }
            toast.textContent = message;
            toast.classList.add('show');
            
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    }

    function initMobileTOC() {
        // Simple logic to scroll to headings smoothly
        const tocLinks = document.querySelectorAll('.toc-list a');
        tocLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId.startsWith('#')) {
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        targetEl.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
    }
});
