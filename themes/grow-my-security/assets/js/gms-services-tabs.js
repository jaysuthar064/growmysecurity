document.addEventListener('DOMContentLoaded', function () {
    var isElementorPreview = window.location.search.indexOf('elementor-preview=') !== -1 ||
        document.body.classList.contains('elementor-editor-active') ||
        document.documentElement.classList.contains('elementor-editor-active');

    if (isElementorPreview) {
        return;
    }

    var tabSets = document.querySelectorAll('[data-gms-tabs]');
    if (!tabSets.length) {
        return;
    }

    tabSets.forEach(function (tabsContainer) {
        var controls = tabsContainer.querySelectorAll('.gms-services-tabs__control');
        var panels = tabsContainer.querySelectorAll('.gms-services-tabs__panel');

        if (!controls.length || !panels.length) {
            return;
        }

        controls.forEach(function (control) {
            control.addEventListener('click', function () {
                var target = control.getAttribute('data-tab-target') || control.getAttribute('data-tab-control');

                controls.forEach(function (candidate) {
                    var isActive = candidate === control;
                    candidate.classList.toggle('is-active', isActive);
                    candidate.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function (panel) {
                    var isTarget = panel.getAttribute('data-tab-panel') === target;

                    if (isTarget) {
                        panel.hidden = false;
                        panel.offsetHeight;
                        panel.classList.add('is-active');
                    } else {
                        panel.classList.remove('is-active');
                        panel.hidden = true;
                    }
                });
            });
        });
    });
});