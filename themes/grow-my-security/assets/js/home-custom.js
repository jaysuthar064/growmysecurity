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



  var footerMobileQuery = typeof window.matchMedia === 'function' ? window.matchMedia('(max-width: 767px)') : null;
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

    syncFooterState();
    addMediaListener(footerMobileQuery, syncFooterState);
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
