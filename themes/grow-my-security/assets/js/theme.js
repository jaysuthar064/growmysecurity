document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const root = document.documentElement;
  const navToggle = document.querySelector('.gms-nav-toggle');
  const nav = document.querySelector('.gms-primary-nav');
  const navBackdrop = document.querySelector('.gms-nav-backdrop');
  const header = document.querySelector('.gms-site-header');
  const headerInner = header ? header.querySelector('.gms-header-inner') : null;
  const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  const prefersReducedMotion = () => reducedMotionQuery.matches;

  const syncViewportWidth = () => {
    const scrollbarWidth = Math.max(0, window.innerWidth - root.clientWidth);
    root.style.setProperty('--gms-scrollbar-width', `${scrollbarWidth}px`);
  };

  const getHeaderOffset = () => {
    const adminOffset = Number.parseFloat(getComputedStyle(root).getPropertyValue('--gms-admin-bar-offset')) || 0;
    const measuredHeader = headerInner || header;
    const headerHeight = measuredHeader ? Math.round(measuredHeader.getBoundingClientRect().height) : 0;
    return headerHeight + adminOffset + 18;
  };

  const syncHeaderMetrics = () => {
    if (!header) {
      return;
    }

    root.style.scrollPaddingTop = `${getHeaderOffset()}px`;
  };

  const syncHeaderState = () => {
    if (!header) {
      return;
    }

    header.classList.toggle('is-scrolled', window.scrollY > 18);
  };

  syncViewportWidth();
  syncHeaderMetrics();
  syncHeaderState();

  window.addEventListener('resize', syncViewportWidth, { passive: true });
  window.addEventListener('resize', syncHeaderMetrics, { passive: true });
  window.addEventListener('scroll', syncHeaderState, { passive: true });

  if ('ResizeObserver' in window && (headerInner || header)) {
    const headerObserver = new ResizeObserver(() => {
      syncHeaderMetrics();
      syncHeaderState();
    });

    headerObserver.observe(headerInner || header);
  }

  if (navToggle && nav) {
    const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const phoneNavQuery = window.matchMedia('(max-width: 480px)');
    let restoreFocusTarget = null;

    const getNavFocusables = () => {
      const navItems = Array.from(nav.querySelectorAll(focusableSelector)).filter((element) => {
        if (!(element instanceof HTMLElement)) {
          return false;
        }

        return !element.hidden && element.offsetParent !== null;
      });

      return [navToggle, ...navItems];
    };

    const syncNavState = (isOpen, options = {}) => {
      const { focusFirst = false, restoreFocus = true } = options;

      navToggle.setAttribute('aria-expanded', String(isOpen));
      nav.classList.toggle('is-open', isOpen);
      body.classList.toggle('has-open-nav', isOpen);

      if (navBackdrop) {
        navBackdrop.hidden = !isOpen;
      }

      if (isOpen) {
        restoreFocusTarget = document.activeElement instanceof HTMLElement ? document.activeElement : navToggle;

        if (focusFirst) {
          const [, ...navFocusables] = getNavFocusables();
          (navFocusables[0] || nav).focus();
        }

        return;
      }

      if (restoreFocus && restoreFocusTarget && typeof restoreFocusTarget.focus === 'function') {
        restoreFocusTarget.focus();
      }

      restoreFocusTarget = null;
    };

    const closeNav = (options = {}) => {
      syncNavState(false, options);
    };

    const openNav = () => {
      syncNavState(true, { focusFirst: true, restoreFocus: false });
    };

    navToggle.addEventListener('click', () => {
      const expanded = navToggle.getAttribute('aria-expanded') === 'true';

      if (expanded) {
        closeNav();
        return;
      }

      openNav();
    });

    nav.querySelectorAll('.menu-item-has-children').forEach((item) => {
      const link = item.querySelector(':scope > a');
      const submenu = item.querySelector(':scope > .gms-nav-submenu');

      if (!link || !submenu) {
        return;
      }

      const toggle = document.createElement('button');
      toggle.className = 'gms-mobile-submenu-toggle';
      toggle.type = 'button';
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', `Toggle ${link.textContent.trim()} submenu`);
      toggle.innerHTML = '<span aria-hidden="true"></span>';

      link.after(toggle);

      toggle.addEventListener('click', () => {
        if (!phoneNavQuery.matches) {
          return;
        }

        const isOpen = item.classList.toggle('is-submenu-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
      });
    });

    navBackdrop?.addEventListener('click', () => {
      closeNav({ restoreFocus: false });
    });

    document.addEventListener('click', (event) => {
      if (!nav.classList.contains('is-open')) {
        return;
      }

      if (nav.contains(event.target) || navToggle.contains(event.target) || navBackdrop?.contains(event.target)) {
        return;
      }

      closeNav({ restoreFocus: false });
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) {
        closeNav();
        return;
      }

      if (event.key !== 'Tab' || !nav.classList.contains('is-open')) {
        return;
      }

      const navFocusables = getNavFocusables();

      if (!navFocusables.length) {
        return;
      }

      const firstFocusable = navFocusables[0];
      const lastFocusable = navFocusables[navFocusables.length - 1];

      if (event.shiftKey && document.activeElement === firstFocusable) {
        event.preventDefault();
        lastFocusable.focus();
      } else if (!event.shiftKey && document.activeElement === lastFocusable) {
        event.preventDefault();
        firstFocusable.focus();
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 980 && nav.classList.contains('is-open')) {
        closeNav({ restoreFocus: false });
      }
    }, { passive: true });

    nav.querySelectorAll('a[href]').forEach((link) => {
      link.addEventListener('click', () => {
        if (phoneNavQuery.matches) {
          return;
        }

        closeNav({ restoreFocus: false });
      });
    });
  }

  const syncDisclosure = (item, buttonSelector, panelSelector, isOpen) => {
    const button = item.querySelector(buttonSelector);
    const panel = item.querySelector(panelSelector);

    if (!button || !panel) {
      return;
    }

    item.classList.toggle('is-open', isOpen);
    button.setAttribute('aria-expanded', String(isOpen));
    panel.hidden = !isOpen;
  };

  const syncFaqIcon = (button, isOpen) => {
    if (button.querySelector('.gms-homepage-faq__icon')) {
      return;
    }

    let label = button.querySelector('.gms-faq-question__label');

    if (!label) {
      label = document.createElement('span');
      label.className = 'gms-faq-question__label';

      while (button.firstChild) {
        label.appendChild(button.firstChild);
      }

      button.appendChild(label);
    }

    let icon = button.querySelector('.gms-faq-question__icon');

    if (!icon) {
      icon = document.createElement('span');
      icon.className = 'gms-faq-question__icon';
      icon.setAttribute('aria-hidden', 'true');
      button.appendChild(icon);
    }

    icon.textContent = isOpen ? '-' : '+';
  };

  const normalizedPath = window.location.pathname.replace(/\/+$/, '') || '/';
  const isFaqPage = normalizedPath === '/faq' || normalizedPath.endsWith('/faq');
  const ensureFaqSearchShell = (faqShell, index = 0) => {
    const faqList = faqShell.querySelector('.gms-faq-list');

    if (!faqList) {
      return;
    }

    faqShell.setAttribute('data-faq-search-shell', '');
    faqList.setAttribute('data-faq-search-list', '');

    if (!faqShell.querySelector('[data-faq-search-input]')) {
      const searchId = `gms-faq-search-runtime-${index + 1}`;
      const searchWrap = document.createElement('div');
      const searchLabel = document.createElement('label');
      const searchInput = document.createElement('input');

      searchWrap.className = 'gms-approved-faq-search';
      searchLabel.className = 'gms-approved-faq-search__label';
      searchLabel.htmlFor = searchId;
      searchLabel.textContent = 'Search FAQs';

      searchInput.id = searchId;
      searchInput.className = 'gms-approved-faq-search__input';
      searchInput.type = 'search';
      searchInput.placeholder = 'Type a keyword or question';
      searchInput.autocomplete = 'off';
      searchInput.setAttribute('data-faq-search-input', '');

      searchWrap.append(searchLabel, searchInput);
      faqList.before(searchWrap);
    }

    if (!faqShell.querySelector('[data-faq-search-empty]')) {
      const emptyState = document.createElement('p');

      emptyState.className = 'gms-approved-faq-empty';
      emptyState.hidden = true;
      emptyState.setAttribute('data-faq-search-empty', '');
      emptyState.setAttribute('aria-live', 'polite');
      emptyState.textContent = 'No FAQs matched your search.';

      faqList.after(emptyState);
    }
  };

  if (isFaqPage) {
    document.querySelectorAll('.gms-approved-faq-shell, .gms-faq-stacked').forEach((faqShell, index) => {
      ensureFaqSearchShell(faqShell, index);
    });

    if (window.elementorFrontend?.hooks) {
      window.elementorFrontend.hooks.addAction('frontend/element_ready/gms-faq.default', ($scope) => {
        if (!$scope?.[0]) {
          return;
        }

        const faqShell = $scope[0].querySelector('.gms-faq-stacked');

        if (faqShell) {
          ensureFaqSearchShell(faqShell);
        }
      });
    }
  }

  document.querySelectorAll('#gms-home-faq .gms-faq-list, .faq-section .gms-faq-list, .gms-service-detail__faq .gms-faq-list, .gms-approved-faq-shell .gms-faq-list').forEach((faqList) => {
    const items = faqList.querySelectorAll('.gms-faq-item');

    items.forEach((item) => {
      const button = item.querySelector('.gms-faq-question');

      if (!button || button.dataset.gmsFaqBound === 'true') {
        return;
      }

      button.dataset.gmsFaqBound = 'true';

      const isInitiallyOpen = item.classList.contains('is-open');
      syncDisclosure(item, '.gms-faq-question', '.gms-faq-answer', isInitiallyOpen);
      syncFaqIcon(button, isInitiallyOpen);

      button.addEventListener('click', () => {
        const isOpening = !item.classList.contains('is-open');

        items.forEach((otherItem) => {
          const otherButton = otherItem.querySelector('.gms-faq-question');
          const isOpen = otherItem === item && isOpening;

          syncDisclosure(otherItem, '.gms-faq-question', '.gms-faq-answer', isOpen);

          if (otherButton) {
            syncFaqIcon(otherButton, isOpen);
          }
        });
      });
    });
  });

  document.querySelectorAll('[data-faq-search-shell]').forEach((faqSearchShell) => {
    const input = faqSearchShell.querySelector('[data-faq-search-input]');
    const faqList = faqSearchShell.querySelector('[data-faq-search-list]');
    const emptyState = faqSearchShell.querySelector('[data-faq-search-empty]');

    if (!(input instanceof HTMLInputElement) || !faqList) {
      return;
    }

    const items = Array.from(faqList.querySelectorAll('.gms-faq-item'));

    const syncFaqSearch = () => {
      const query = input.value.trim().toLowerCase();
      let visibleCount = 0;

      items.forEach((item) => {
        const question = item.querySelector('.gms-faq-question__label, .gms-faq-question')?.textContent ?? '';
        const answer = item.querySelector('.gms-faq-answer')?.textContent ?? '';
        const matches = !query || `${question} ${answer}`.toLowerCase().includes(query);

        item.hidden = !matches;

        if (matches) {
          visibleCount += 1;
        }
      });

      if (emptyState) {
        emptyState.hidden = visibleCount !== 0;
      }
    };

    input.addEventListener('input', syncFaqSearch);
    input.addEventListener('search', syncFaqSearch);
  });

  document.querySelectorAll('.gms-service-accordion').forEach((accordion) => {
    const items = accordion.querySelectorAll('.gms-service-accordion__item');

    items.forEach((item, index) => {
      const button = item.querySelector('.gms-service-accordion__button');

      if (!button) {
        return;
      }

      syncDisclosure(item, '.gms-service-accordion__button', '.gms-service-accordion__panel', item.classList.contains('is-open') || index === 0);

      button.addEventListener('click', () => {
        const isOpening = !item.classList.contains('is-open');

        items.forEach((otherItem) => {
          syncDisclosure(otherItem, '.gms-service-accordion__button', '.gms-service-accordion__panel', otherItem === item && isOpening);
        });
      });
    });
  });

  const footerColumns = Array.from(document.querySelectorAll('.gms-site-footer .gms-footer-column'));

  if (footerColumns.length) {
    footerColumns.forEach((item) => {
      const button = item.querySelector('.gms-footer-column__toggle');

      if (!button || button.dataset.gmsFooterBound === 'true') {
        return;
      }

      button.dataset.gmsFooterBound = 'true';
      item.dataset.defaultOpen = item.classList.contains('is-open') ? 'true' : 'false';

      button.addEventListener('click', () => {
        if (window.innerWidth > 980) {
          return;
        }

        const isOpening = !item.classList.contains('is-open');

        footerColumns.forEach((otherItem) => {
          syncDisclosure(otherItem, '.gms-footer-column__toggle', '.gms-footer-column__panel', otherItem === item && isOpening);
        });
      });
    });

    let footerIsMobile = window.innerWidth <= 980;

    const syncFooterColumns = (isMobile) => {
      footerColumns.forEach((item) => {
        const defaultOpen = item.dataset.defaultOpen === 'true';
        syncDisclosure(item, '.gms-footer-column__toggle', '.gms-footer-column__panel', isMobile ? defaultOpen : true);
      });
    };

    syncFooterColumns(footerIsMobile);

    window.addEventListener('resize', () => {
      const isMobile = window.innerWidth <= 980;

      if (isMobile === footerIsMobile) {
        return;
      }

      footerIsMobile = isMobile;
      syncFooterColumns(isMobile);
    }, { passive: true });
  }

  document.querySelectorAll('[data-gms-slider]').forEach((slider) => {
    const slides = Array.from(slider.querySelectorAll('.gms-hero-slide'));
    const dots = Array.from(slider.querySelectorAll('.gms-hero-dot'));
    const status = slider.querySelector('[data-gms-slider-status]');
    let index = slides.findIndex((slide) => slide.classList.contains('is-active'));
    let intervalId = null;
    let touchStartX = null;

    if (!slides.length) {
      return;
    }

    if (index < 0) {
      index = 0;
    }

    const announce = (activeSlide) => {
      if (!status) {
        return;
      }

      const heading = activeSlide.querySelector('h1, h2, h3');
      const slideLabel = heading ? heading.textContent.trim() : `Slide ${index + 1}`;
      status.textContent = `Slide ${index + 1} of ${slides.length}: ${slideLabel}`;
    };

    const activate = (nextIndex, options = {}) => {
      const { stopAutoplay = false } = options;

      if (stopAutoplay) {
        stopAutoRotate();
      }

      slides.forEach((slide, slideIndex) => {
        const isActive = slideIndex === nextIndex;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', String(!isActive));

        if ('inert' in slide) {
          slide.inert = !isActive;
        } else {
          slide.toggleAttribute('inert', !isActive);
        }
      });

      dots.forEach((dot, dotIndex) => {
        const isActive = dotIndex === nextIndex;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-pressed', String(isActive));
      });

      index = nextIndex;
      announce(slides[nextIndex]);
    };

    const stopAutoRotate = () => {
      if (!intervalId) {
        return;
      }

      window.clearInterval(intervalId);
      intervalId = null;
    };

    const startAutoRotate = () => {
      if (slides.length < 2 || prefersReducedMotion()) {
        return;
      }

      stopAutoRotate();
      intervalId = window.setInterval(() => {
        activate((index + 1) % slides.length);
      }, 6500);
    };

    const goToRelativeSlide = (delta) => {
      activate((index + delta + slides.length) % slides.length, { stopAutoplay: true });
    };

    dots.forEach((dot, dotIndex) => {
      dot.addEventListener('click', () => {
        activate(dotIndex, { stopAutoplay: true });
      });
    });

    slider.addEventListener('mouseenter', stopAutoRotate);
    slider.addEventListener('mouseleave', startAutoRotate);
    slider.addEventListener('focusin', stopAutoRotate);
    slider.addEventListener('focusout', (event) => {
      if (!slider.contains(event.relatedTarget)) {
        startAutoRotate();
      }
    });

    slider.addEventListener('keydown', (event) => {
      if (slides.length < 2) {
        return;
      }

      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        goToRelativeSlide(-1);
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        goToRelativeSlide(1);
      } else if (event.key === 'Home') {
        event.preventDefault();
        activate(0, { stopAutoplay: true });
      } else if (event.key === 'End') {
        event.preventDefault();
        activate(slides.length - 1, { stopAutoplay: true });
      }
    });

    slider.addEventListener('touchstart', (event) => {
      if (event.touches.length !== 1) {
        return;
      }

      touchStartX = event.touches[0].clientX;
      stopAutoRotate();
    }, { passive: true });

    slider.addEventListener('touchend', (event) => {
      if (touchStartX === null) {
        return;
      }

      const delta = event.changedTouches[0].clientX - touchStartX;
      touchStartX = null;

      if (Math.abs(delta) > 48) {
        delta < 0 ? goToRelativeSlide(1) : goToRelativeSlide(-1);
        return;
      }

      startAutoRotate();
    }, { passive: true });

    slider.addEventListener('touchcancel', () => {
      touchStartX = null;
      startAutoRotate();
    }, { passive: true });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoRotate();
      } else {
        startAutoRotate();
      }
    });

    if (typeof reducedMotionQuery.addEventListener === 'function') {
      reducedMotionQuery.addEventListener('change', () => {
        if (prefersReducedMotion()) {
          stopAutoRotate();
        } else {
          startAutoRotate();
        }
      });
    }

    activate(index);
    startAutoRotate();
  });

  const getControlledVideo = (element) => {
    const frame = element.closest('[data-gms-video-frame], .gms-approved-video-card__frame');
    const stage = element.closest('.gms-approved-intro__banner-stage');

    return (
      (frame ? frame.querySelector('video') : null) ||
      (stage ? stage.querySelector('video') : null)
    );
  };

  document.querySelectorAll('[data-gms-video-toggle]').forEach((button) => {
    const video = getControlledVideo(button);
    const label = button.querySelector('.gms-approved-video-card__toggle-label');

    if (!(video instanceof HTMLVideoElement)) {
      return;
    }

    const syncVideoToggle = (isPlaying) => {
      button.setAttribute('aria-pressed', String(isPlaying));
      button.setAttribute('aria-label', isPlaying ? 'Pause video' : 'Play video');

      if (label) {
        label.textContent = isPlaying ? 'Pause' : 'Play';
      }
    };

    syncVideoToggle(!video.paused);

    button.addEventListener('click', async () => {
      if (video.paused) {
        try {
          await video.play();
          syncVideoToggle(true);
        } catch (error) {
          syncVideoToggle(false);
        }

        return;
      }

      video.pause();
      syncVideoToggle(false);
    });

    video.addEventListener('play', () => {
      syncVideoToggle(true);
    });

    video.addEventListener('pause', () => {
      syncVideoToggle(false);
    });
  });

  document.querySelectorAll('[data-gms-video-mute-toggle]').forEach((button) => {
    const video = getControlledVideo(button);
    const label = button.querySelector('.gms-approved-video-card__toggle-label');

    if (!(video instanceof HTMLVideoElement)) {
      return;
    }

    const syncMuteToggle = () => {
      const isMuted = video.muted;

      button.setAttribute('aria-pressed', String(isMuted));
      button.setAttribute('aria-label', isMuted ? 'Unmute video' : 'Mute video');

      if (label) {
        label.textContent = isMuted ? 'Muted' : 'Sound On';
      }
    };

    syncMuteToggle();

    button.addEventListener('click', async () => {
      video.muted = !video.muted;

      if (!video.muted && video.paused) {
        try {
          await video.play();
        } catch (error) {
          video.muted = true;
        }
      }

      syncMuteToggle();
    });

    video.addEventListener('volumechange', syncMuteToggle);
  });
  document.querySelectorAll('a[href]').forEach((anchor) => {
    const href = anchor.getAttribute('href');

    if (!href) {
      return;
    }

    let url;

    try {
      url = new URL(href, window.location.href);
    } catch (error) {
      return;
    }

    if (url.origin !== window.location.origin || url.pathname !== window.location.pathname || !url.hash) {
      return;
    }

    const target = document.querySelector(url.hash);

    if (!target) {
      return;
    }

    anchor.addEventListener('click', (event) => {
      event.preventDefault();

      const top = target.getBoundingClientRect().top + window.scrollY - getHeaderOffset();
      window.scrollTo({ top, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
      target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
      window.history.pushState({}, '', url.hash);
    });
  });

  const contactValidationForms = document.querySelectorAll('.gms-approved-contact-form, .gms-contact-widget__form');

  if (contactValidationForms.length) {
    const phonePattern = /^[0-9]+$/;

    const getFieldLabel = (field) => {
      const label = field.closest('label');
      const labelText = label?.querySelector('span')?.textContent || 'This field';

      return labelText.replace(/\s*\(Optional\)\s*/i, '').replace(/\s+/g, ' ').trim() || 'This field';
    };

    const getFieldErrorMessage = (field) => {
      const label = getFieldLabel(field);
      const value = (field.value || '').trim();

      if (field.name === 'phone' && value && !phonePattern.test(value)) {
        return 'Phone should contain numbers only.';
      }

      if (field.validity.valueMissing) {
        if (field.type === 'checkbox') {
          return 'This confirmation is required.';
        }

        return `${label} is required.`;
      }

      if (field.validity.typeMismatch && field.type === 'email') {
        return 'Enter a valid email address.';
      }

      if (field.validity.tooShort) {
        return `${label} is too short.`;
      }

      if (field.validity.patternMismatch) {
        return field.name === 'phone' ? 'Phone should contain numbers only.' : `${label} has an invalid format.`;
      }

      return '';
    };

    const clearFieldError = (field) => {
      const label = field.closest('label');
      const error = label?.querySelector('.gms-form-field-error');

      field.classList.remove('is-invalid');
      field.removeAttribute('aria-invalid');
      field.removeAttribute('aria-describedby');
      field.setCustomValidity('');

      if (label) {
        label.classList.remove('is-invalid');
      }

      if (error) {
        error.hidden = true;
        error.textContent = '';
      }
    };

    const showFieldError = (field, message) => {
      const label = field.closest('label');

      if (!label) {
        return;
      }

      let error = label.querySelector('.gms-form-field-error');

      if (!error) {
        error = document.createElement('p');
        error.className = 'gms-form-field-error';
        error.id = `gms-contact-error-${Math.random().toString(36).slice(2, 9)}`;
        label.append(error);
      }

      field.classList.add('is-invalid');
      field.setAttribute('aria-invalid', 'true');
      field.setAttribute('aria-describedby', error.id);
      label.classList.add('is-invalid');
      error.textContent = message;
      error.hidden = false;
    };

    const validateField = (field) => {
      if (field.disabled || field.type === 'hidden') {
        return true;
      }

      field.setCustomValidity('');

      if (field.name === 'phone') {
        const value = (field.value || '').trim();
        field.setCustomValidity(value && !phonePattern.test(value) ? 'Phone should contain numbers only.' : '');
      }

      const message = getFieldErrorMessage(field);

      if (message) {
        showFieldError(field, message);
        return false;
      }

      clearFieldError(field);
      return true;
    };

    contactValidationForms.forEach((form) => {
      form.noValidate = true;

      form.querySelectorAll('input, select, textarea').forEach((field) => {
        field.addEventListener('input', () => clearFieldError(field));
        field.addEventListener('change', () => validateField(field));
        field.addEventListener('blur', () => {
          if ((field.value || field.checked) && !field.validity.valid) {
            validateField(field);
          }
        });
      });

      form.addEventListener('submit', (event) => {
        const fields = Array.from(form.querySelectorAll('input, select, textarea'));
        let firstInvalid = null;

        fields.forEach((field) => {
          if (!validateField(field) && !firstInvalid) {
            firstInvalid = field;
          }
        });

        if (firstInvalid) {
          event.preventDefault();
          firstInvalid.focus({ preventScroll: true });
          firstInvalid.scrollIntoView({ block: 'center', behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
        }
      });
    });
  }

  // --- Legal Page Enhancements ---
  const legalProgress = document.getElementById('legal-progress');
  const legalContent = document.querySelector('.gms-legal-content');

  if (legalProgress) {
    const updateProgress = () => {
      const scrollH = document.documentElement.scrollHeight - window.innerHeight;
      const scrolled = Math.max(0, Math.min(100, (window.scrollY / scrollH) * 100));
      legalProgress.style.width = `${scrolled}%`;
    };

    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();
  }

  if (legalContent) {
    const animElements = legalContent.querySelectorAll('h2, h3, p, ul, ol');
    const observerOptions = {
      root: null,
      rootMargin: '0px 0px -40px 0px',
      threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          
          // If it's a heading, show it. If it's a child element, delay it.
          const isHeading = ['H2', 'H3'].includes(el.tagName);
          const delay = isHeading ? 0 : 250;
          
          setTimeout(() => {
            el.classList.add('is-visible');
          }, delay);
          
          observer.unobserve(el);
        }
      });
    }, observerOptions);

    animElements.forEach(el => {
      observer.observe(el);
    });
  }
});
