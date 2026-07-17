/**
 * Home slider interaction for both legacy and tns-style sections.
 */
document.addEventListener('DOMContentLoaded', () => {
  const sliders = document.querySelectorAll('.product-list-scrollable, .tns-card-track');
  sliders.forEach((slider) => {
    let isPointerDown = false;
    let activePointerId = null;
    let startX = 0;
    let startScrollLeft = 0;
    let lastX = 0;
    let velocity = 0;
    let rafID = 0;
    let isDragging = false;
    const isInteractiveTarget = (el) => !!el?.closest('button, a, input, textarea, select, label, [data-no-drag]');

    slider.style.touchAction = 'pan-y';

    const cancelMomentum = () => {
      cancelAnimationFrame(rafID);
      rafID = 0;
    };

    const momentumScroll = () => {
      slider.scrollLeft += velocity;
      velocity *= 0.95;
      if (Math.abs(velocity) > 0.5) {
        rafID = requestAnimationFrame(momentumScroll);
      } else {
        slider.classList.remove('dragging');
      }
    };

    const onPointerDown = (clientX) => {
      isPointerDown = true;
      isDragging = false;
      slider.classList.remove('dragging');
      cancelMomentum();
      startX = clientX;
      startScrollLeft = slider.scrollLeft;
      lastX = clientX;
      velocity = 0;
    };

    const onPointerMove = (clientX, preventDefault) => {
      if (!isPointerDown) return;
      const moveDistance = Math.abs(clientX - startX);

      if (moveDistance > 5) {
        if (!isDragging) {
          isDragging = true;
          slider.classList.add('dragging');
        }
        preventDefault();
        slider.scrollLeft = startScrollLeft - (clientX - startX);
        velocity = lastX - clientX;
        lastX = clientX;
      }
    };

    const stopDragging = () => {
      if (!isPointerDown) return;
      isPointerDown = false;
      activePointerId = null;
      if (!isDragging || Math.abs(velocity) <= 0.5) {
        slider.classList.remove('dragging');
        isDragging = false;
        return;
      }

      momentumScroll();
      window.setTimeout(() => {
        isDragging = false;
      }, 0);
    };

    slider.addEventListener('pointerdown', (e) => {
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      if (isInteractiveTarget(e.target)) return;
      activePointerId = e.pointerId;
      slider.setPointerCapture(e.pointerId);
      onPointerDown(e.clientX);
    });

    slider.addEventListener('pointermove', (e) => {
      if (activePointerId !== null && e.pointerId !== activePointerId) return;
      onPointerMove(e.clientX, () => e.preventDefault());
    });

    slider.addEventListener('pointerup', (e) => {
      if (activePointerId !== null && e.pointerId !== activePointerId) return;
      if (slider.hasPointerCapture(e.pointerId)) {
        slider.releasePointerCapture(e.pointerId);
      }
      stopDragging();
    });

    slider.addEventListener('pointercancel', stopDragging);
    slider.addEventListener('lostpointercapture', stopDragging);
    slider.addEventListener('dragstart', (e) => e.preventDefault());

    slider.addEventListener('click', (e) => {
      if (!isDragging) return;
      e.preventDefault();
      e.stopPropagation();
      isDragging = false;
    }, true);
  });

  const tickerItems = document.querySelectorAll('.tns-hero-ticker .tns-ticker-item');
  if (tickerItems.length) {
    tickerItems.forEach((item) => {
      item.addEventListener('click', () => {
        tickerItems.forEach((x) => x.classList.remove('active'));
        item.classList.add('active');
      });
    });
  }

  const heroSlider = document.querySelector('[data-hero-slider]');
  if (heroSlider) {
    const slides = Array.from(heroSlider.querySelectorAll('[data-hero-slide]'));
    const dots = Array.from(heroSlider.querySelectorAll('[data-hero-dot]'));
    const prevBtn = heroSlider.querySelector('[data-hero-prev]');
    const nextBtn = heroSlider.querySelector('[data-hero-next]');
    let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('active')));
    if (activeIndex < 0) activeIndex = 0;
    let autoPlayId = 0;

    const renderHeroSlide = (nextIndex) => {
      if (!slides.length) return;
      activeIndex = (nextIndex + slides.length) % slides.length;

      slides.forEach((slide, index) => {
        slide.classList.toggle('active', index === activeIndex);
      });

      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === activeIndex);
      });
    };

    const restartAutoplay = () => {
      window.clearInterval(autoPlayId);
      if (slides.length <= 1) return;
      autoPlayId = window.setInterval(() => {
        renderHeroSlide(activeIndex + 1);
      }, 5000);
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        renderHeroSlide(activeIndex - 1);
        restartAutoplay();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        renderHeroSlide(activeIndex + 1);
        restartAutoplay();
      });
    }

    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        renderHeroSlide(index);
        restartAutoplay();
      });
    });

    heroSlider.addEventListener('mouseenter', () => window.clearInterval(autoPlayId));
    heroSlider.addEventListener('mouseleave', restartAutoplay);

    renderHeroSlide(activeIndex);
    restartAutoplay();
  }
});

window.scrollSlider = function (anchor, direction) {
  const root = document.getElementById(`scroll-${anchor}`);
  if (!root) return;

  const container = root.querySelector('.tns-card-track, .product-list-scrollable');
  if (!container) return;

  const scrollAmount = Math.max(container.clientWidth * 0.8, 260);
  container.scrollBy({
    left: direction * scrollAmount,
    behavior: 'smooth',
  });
};
