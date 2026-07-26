document.addEventListener('DOMContentLoaded', function () {

    /* ============ DARK MODE TOGGLE ============ */
    const themeToggle = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('techpilot-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    function applyTheme(isDark) {
        document.documentElement.classList.toggle('dark-mode', isDark);

        if (!themeToggle) return;

        const icon = themeToggle.querySelector('i');
        const nextActionLabel = isDark
            ? 'Chuyển sang giao diện sáng'
            : 'Chuyển sang giao diện tối';

        if (icon) {
            icon.className = isDark
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';

            icon.setAttribute('aria-hidden', 'true');
        }

        themeToggle.setAttribute('aria-label', nextActionLabel);
        themeToggle.setAttribute('title', nextActionLabel);
        themeToggle.setAttribute('aria-pressed', String(isDark));
    }

    if (savedTheme) {
        applyTheme(savedTheme === 'dark');
    } else {
        applyTheme(prefersDark);
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const isDark = !document.documentElement.classList.contains('dark-mode');
            applyTheme(isDark);
            localStorage.setItem('techpilot-theme', isDark ? 'dark' : 'light');
        });
    }

    /* ============ FLASH SALE COUNTDOWN ============ */
    const countdownEl = document.getElementById('flashCountdown');
    if (countdownEl) {
        const endTimeStr = countdownEl.dataset.endTime;
        const endingTextEl = document.getElementById('flashEndingText');
        const hEl = countdownEl.querySelector('[data-countdown-hours]') || document.getElementById('cd-h');
        const mEl = countdownEl.querySelector('[data-countdown-minutes]') || document.getElementById('cd-m');
        const sEl = countdownEl.querySelector('[data-countdown-seconds]') || document.getElementById('cd-s');

        function pad(n) { return Math.max(0, n).toString().padStart(2, '0'); }

        function renderZero() {
            if (hEl) hEl.textContent = '00';
            if (mEl) mEl.textContent = '00';
            if (sEl) sEl.textContent = '00';
            countdownEl.classList.add('is-expired');
            if (endingTextEl) endingTextEl.textContent = 'Đã kết thúc';
        }

        if (!endTimeStr) {
            renderZero();
        } else {
            const isoStr = endTimeStr.includes('T') ? endTimeStr : (endTimeStr.replace(' ', 'T') + '+07:00');
            const endMs = new Date(isoStr).getTime();
            let intervalId = null;

            function tick() {
                if (!Number.isFinite(endMs)) {
                    renderZero();
                    if (intervalId !== null) {
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                    return false;
                }

                const diffMs = endMs - Date.now();

                if (diffMs <= 0) {
                    renderZero();
                    if (intervalId !== null) {
                        clearInterval(intervalId);
                        intervalId = null;
                    }
                    return false;
                }

                const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
                const h = Math.floor(totalSeconds / 3600);
                const m = Math.floor((totalSeconds % 3600) / 60);
                const s = totalSeconds % 60;

                if (hEl) hEl.textContent = pad(h);
                if (mEl) mEl.textContent = pad(m);
                if (sEl) sEl.textContent = pad(s);
                return true;
            }

            if (tick()) {
                intervalId = setInterval(tick, 1000);
            }
        }
    }

    /* ============ HERO CAROUSEL ============ */
    const carousel = document.getElementById('heroCarousel');
    if (carousel) {
        const slides = carousel.querySelectorAll('.carousel-slide');
        const dots = carousel.querySelectorAll('.carousel-dot');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(s => s.classList.remove('is-active'));
            dots.forEach(d => d.classList.remove('is-active'));
            slides[index].classList.add('is-active');
            dots[index].classList.add('is-active');
            currentSlide = index;
        }

        dots.forEach(dot => {
            dot.addEventListener('click', function () {
                const idx = parseInt(dot.getAttribute('data-index'), 10);
                showSlide(idx);
            });
        });

        // Tự động chuyển slide sau 5 giây
        setInterval(function () {
            let next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }, 5000);
    }

    /* ============ BEST SELLER TABS ============ */
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('is-active'));
            document.querySelectorAll('.tabs-content__panel').forEach(p => p.classList.remove('is-active'));

            btn.classList.add('is-active');
            const panelId = btn.getAttribute('data-tab');
            const panel = document.getElementById(panelId);
            if (panel) panel.classList.add('is-active');
        });
    });

    /* ============ TOGGLE SHOW/HIDE PASSWORD (login/register) ============ */
    document.querySelectorAll('.toggle-password').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const targetInput = document.getElementById(toggle.dataset.target);
            if (!targetInput) return;
            const icon = toggle.querySelector('i');

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

});
