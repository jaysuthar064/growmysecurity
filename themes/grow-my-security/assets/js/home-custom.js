document.addEventListener('DOMContentLoaded', function () {
  var body = document.body;
  var isElementorPreview = window.location.search.indexOf('elementor-preview=') !== -1 || body.classList.contains('elementor-editor-active') || document.documentElement.classList.contains('elementor-editor-active');

  if (isElementorPreview) {
    return;
  }
  var toArray = function (nodeList) {
    return Array.prototype.slice.call(nodeList || []);
  };

  var addMediaListener = function (query, handler) {
    if (!query) {
      return;
    }

    if (typeof query.addEventListener === 'function') {
      query.addEventListener('change', handler);
      return;
    }

    if (typeof query.addListener === 'function') {
      query.addListener(handler);
    }
  };

  var nav = document.querySelector('[data-home-nav]');
  var navToggle = document.querySelector('[data-home-nav-toggle]');
  var navBackdrop = document.querySelector('[data-home-nav-backdrop]');
  var mobileNavQuery = typeof window.matchMedia === 'function' ? window.matchMedia('(max-width: 991px)') : null;

  if (nav && navToggle && navBackdrop) {
    var setNavState = function (isOpen) {
      var shouldOpen = !!isOpen && (!mobileNavQuery || mobileNavQuery.matches);

      nav.classList.toggle('is-open', shouldOpen);
      navToggle.setAttribute('aria-expanded', String(shouldOpen));
      navBackdrop.hidden = !shouldOpen;
      body.classList.toggle('has-homepage-nav-open', shouldOpen);
    };

    setNavState(false);

    navToggle.addEventListener('click', function () {
      setNavState(!nav.classList.contains('is-open'));
    });

    navBackdrop.addEventListener('click', function () {
      setNavState(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setNavState(false);
      }
    });

    toArray(nav.querySelectorAll('a')).forEach(function (link) {
      link.addEventListener('click', function () {
        setNavState(false);
      });
    });

    addMediaListener(mobileNavQuery, function () {
      if (!mobileNavQuery.matches) {
        setNavState(false);
      }
    });
  }

  var initSingleAccordion = function (containerSelector, itemSelector, triggerSelector, panelSelector) {
    var container = document.querySelector(containerSelector);

    if (!container) {
      return;
    }

    var items = toArray(container.querySelectorAll(itemSelector));

    if (!items.length) {
      return;
    }

    var setItemState = function (item, shouldOpen) {
      var trigger = item.querySelector(triggerSelector);
      var panel = item.querySelector(panelSelector);

      if (!trigger || !panel) {
        return;
      }

      item.classList.toggle('is-open', shouldOpen);
      trigger.setAttribute('aria-expanded', String(shouldOpen));
      panel.hidden = !shouldOpen;
    };

    items.forEach(function (item) {
      var trigger = item.querySelector(triggerSelector);

      if (!trigger) {
        return;
      }

      trigger.addEventListener('click', function () {
        var isOpening = !item.classList.contains('is-open');

        items.forEach(function (otherItem) {
          setItemState(otherItem, false);
        });

        if (isOpening) {
          setItemState(item, true);
        }
      });
    });
  };

  initSingleAccordion('[data-services-accordion]', '.gms-homepage-service', '[data-service-trigger]', '[data-service-panel]');
  initSingleAccordion('[data-faq-accordion]', '.gms-homepage-faq__item', '[data-faq-trigger]', '[data-faq-panel]');

  toArray(document.querySelectorAll('.gms-homepage-quote__form-card')).forEach(function (card) {
    var nameInput = card.querySelector('input[name="full_name"]');
    var emailInput = card.querySelector('input[name="email"]');
    var phoneInput = card.querySelector('input[name="phone"]');
    var cta = card.querySelector('.gms-homepage-button--primary');

    if (nameInput) {
      nameInput.required = true;
      nameInput.minLength = 2;
      nameInput.maxLength = 80;
      nameInput.pattern = "[A-Za-z .'-]{2,80}";
      nameInput.title = 'Use letters only for your name.';
      nameInput.addEventListener('input', function () {
        nameInput.value = nameInput.value.replace(/[^A-Za-z .'-]/g, '');
      });
    }

    if (emailInput) {
      emailInput.type = 'email';
      emailInput.required = true;
      emailInput.placeholder = 'name@company.com';
      emailInput.maxLength = 120;
    }

    if (phoneInput) {
      phoneInput.type = 'tel';
      phoneInput.required = true;
      phoneInput.inputMode = 'numeric';
      phoneInput.pattern = '[0-9]{10}';
      phoneInput.maxLength = 10;
      phoneInput.placeholder = '10 digit phone number';
      phoneInput.title = 'Enter exactly 10 digits.';
      phoneInput.addEventListener('input', function () {
        phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 10);
        phoneInput.setCustomValidity(phoneInput.value.length === 10 ? '' : 'Enter exactly 10 digits.');
      });
    }

    if (cta) {
      cta.addEventListener('click', function (event) {
        var fields = [nameInput, emailInput, phoneInput].filter(Boolean);

        if (phoneInput) {
          phoneInput.setCustomValidity(phoneInput.value.length === 10 ? '' : 'Enter exactly 10 digits.');
        }

        var invalidField = fields.find(function (field) {
          return typeof field.checkValidity === 'function' && !field.checkValidity();
        });

        if (invalidField) {
          event.preventDefault();
          invalidField.reportValidity();
        }
      });
    }
  });



  var footerMobileQuery = typeof window.matchMedia === 'function' ? window.matchMedia('(max-width: 980px)') : null;
  var footerNarrowQuery = typeof window.matchMedia === 'function' ? window.matchMedia('(max-width: 375px)') : null;
  var footerGrid = document.querySelector('.gms-homepage-footer__grid');
  var footerItems = toArray(document.querySelectorAll('.gms-homepage-footer__column')).map(function (column) {
    return {
      button: column.querySelector('[data-footer-toggle]'),
      column: column,
      defaultOpen: column.classList.contains('is-open'),
      panel: column.querySelector('[data-footer-panel]')
    };
  }).filter(function (item) {
    return item.button && item.panel;
  });

  if (footerItems.length) {
    var setFooterState = function (item, shouldOpen) {
      item.column.classList.toggle('is-open', shouldOpen);
      item.button.setAttribute('aria-expanded', String(shouldOpen));
      item.panel.hidden = !shouldOpen;
    };

    var syncFooterState = function () {
      if (!footerMobileQuery || !footerMobileQuery.matches) {
        footerItems.forEach(function (item) {
          item.button.disabled = true;
          item.button.setAttribute('aria-expanded', 'true');
          item.panel.hidden = false;
        });
        return;
      }

      footerItems.forEach(function (item) {
        item.button.disabled = false;
      });

      if (!footerItems.some(function (item) {
        return item.column.classList.contains('is-open');
      })) {
        footerItems.forEach(function (item) {
          item.column.classList.toggle('is-open', item.defaultOpen);
        });
      }

      footerItems.forEach(function (item) {
        setFooterState(item, item.column.classList.contains('is-open'));
      });
    };

    footerItems.forEach(function (item) {
      item.button.addEventListener('click', function () {
        if (!footerMobileQuery || !footerMobileQuery.matches) {
          return;
        }

        setFooterState(item, !item.column.classList.contains('is-open'));
      });
    });

    var syncFooterGridLayout = function () {
      if (!footerGrid) {
        return;
      }

      var shouldStack = footerNarrowQuery && footerNarrowQuery.matches;

      footerGrid.style.setProperty('--gms-homepage-footer-columns', shouldStack ? 'minmax(0, 1fr)' : '');

      if (shouldStack) {
        footerGrid.style.setProperty('display', 'flex', 'important');
        footerGrid.style.setProperty('flex-direction', 'column', 'important');
        footerGrid.style.setProperty('align-items', 'stretch', 'important');
        footerGrid.style.setProperty('justify-content', 'stretch', 'important');
        footerGrid.style.setProperty('gap', '24px', 'important');
      } else {
        footerGrid.style.removeProperty('display');
        footerGrid.style.removeProperty('flex-direction');
        footerGrid.style.removeProperty('align-items');
        footerGrid.style.removeProperty('justify-content');
        footerGrid.style.removeProperty('gap');
      }

      toArray(footerGrid.children).forEach(function (child) {
        if (shouldStack) {
          child.style.setProperty('grid-column', '1 / -1', 'important');
          child.style.setProperty('width', '100%', 'important');
          child.style.setProperty('min-width', '0', 'important');
          child.style.setProperty('max-width', '100%', 'important');
          child.style.setProperty('box-sizing', 'border-box', 'important');
          return;
        }

        child.style.removeProperty('grid-column');
        child.style.removeProperty('width');
        child.style.removeProperty('min-width');
        child.style.removeProperty('max-width');
        child.style.removeProperty('box-sizing');
      });
    };

    syncFooterState();
    syncFooterGridLayout();
    addMediaListener(footerMobileQuery, syncFooterState);
    addMediaListener(footerNarrowQuery, syncFooterGridLayout);
  }

  var problemItems = toArray(document.querySelectorAll('.gms-homepage-problem__item'));
  var problemList = document.querySelector('.gms-homepage-problem__list');

  if (problemList && problemItems.length) {
    var updateProblemProgress = function () {
      var listRect = problemList.getBoundingClientRect();
      var windowHeight = window.innerHeight || document.documentElement.clientHeight;
      var scrollStart = windowHeight * 0.69;
      var scrollEnd = windowHeight * 0.24;
      var progress = 0;

      if (listRect.top < scrollStart) {
        progress = (scrollStart - listRect.top) / Math.max(1, listRect.height + (scrollStart - scrollEnd));
      }

      if (listRect.bottom < scrollEnd) {
        progress = 1;
      }

      progress = Math.max(0, Math.min(1, progress));
      problemList.style.setProperty('--problem-progress', (progress * 100) + '%');
    };

    var syncProblemDots = function () {
      var listRect = problemList.getBoundingClientRect();
      var progressPx = (parseFloat(getComputedStyle(problemList).getPropertyValue('--problem-progress')) || 0) / 100 * listRect.height;

      problemItems.forEach(function (item) {
        var marker = item.querySelector('.gms-homepage-problem__marker');

        if (!marker) {
          return;
        }

        var markerRect = marker.getBoundingClientRect();
        var markerCenter = markerRect.top - listRect.top + (markerRect.height / 2);

        item.classList.toggle('is-active', progressPx >= markerCenter);
      });
    };

    var updateProblemAnimation = function () {
      updateProblemProgress();
      syncProblemDots();
    };

    window.addEventListener('scroll', updateProblemAnimation, { passive: true });
    window.addEventListener('resize', updateProblemAnimation);
    updateProblemProgress();
    syncProblemDots();
  }

  // Timeline Scroll Animation
  var timeline = document.querySelector('.gms-homepage-guarantee__timeline');
  var timelineSteps = toArray(document.querySelectorAll('.gms-homepage-guarantee__step'));
  var progressLine = document.querySelector('.gms-homepage-guarantee__progress-line');

  if (timeline && timelineSteps.length && progressLine) {
    var observerOptions = {
      root: null,
      rootMargin: '0px 0px -25% 0px',
      threshold: 0.1
    };

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-active');
        }
      });
    }, observerOptions);

    timelineSteps.forEach(function (step) {
      observer.observe(step);
    });

    var updateTimelineProgress = function () {
      var rect = timeline.getBoundingClientRect();
      var windowHeight = window.innerHeight;
      
      // Start filling when the top of the timeline reaches 70% of the viewport
      // Complete filling when the bottom reaches 30% of the viewport
      var scrollStart = windowHeight * 0.7;
      var scrollEnd = windowHeight * 0.3;
      
      var progress = 0;
      
      if (rect.top < scrollStart) {
        var distance = scrollStart - rect.top;
        var totalHeight = rect.height + (scrollStart - scrollEnd);
        progress = Math.min(1, distance / rect.height);
      }
      
      if (rect.bottom < scrollEnd) {
        progress = 1;
      }

      timeline.style.setProperty('--timeline-progress', (progress * 100) + '%');
    };

    window.addEventListener('scroll', updateTimelineProgress);
    window.addEventListener('resize', updateTimelineProgress);
    updateTimelineProgress();
  }
});
document.addEventListener('DOMContentLoaded', function () {
  var isElementorPreview = window.location.search.indexOf('elementor-preview=') !== -1 || document.body.classList.contains('elementor-editor-active') || document.documentElement.classList.contains('elementor-editor-active');

  if (isElementorPreview || typeof Swiper === 'undefined') {
    return;
  }

  var heroSlider = document.querySelector('.gms-hero-swiper');
  if (heroSlider) {
    new Swiper(heroSlider, {
      loop: true,
      effect: 'fade',
      fadeEffect: {
        crossFade: true
      },
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: '.gms-hero-button-next',
        prevEl: '.gms-hero-button-prev',
      },
      pagination: {
        el: '.gms-hero-pagination',
        clickable: true,
      },
      grabCursor: true,
    });
  }

  var testimonialsSlider = document.querySelector('.gms-testimonials-swiper');
  if (testimonialsSlider) {
    new Swiper(testimonialsSlider, {
      loop: true,
      slidesPerView: 1,
      spaceBetween: 24,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      navigation: {
        nextEl: '.gms-testimonials-button-next',
        prevEl: '.gms-testimonials-button-prev',
      },
      pagination: {
        el: '.gms-testimonials-swiper-pagination',
        clickable: true,
      },
      grabCursor: true,
      breakpoints: {
        768: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        }
      }
    });
  }
});
