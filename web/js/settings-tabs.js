(() => {
    'use strict';

    const tabs = document.querySelectorAll('[data-settings-tabs] [role="tab"]');

    const activateTab = (activeTab) => {
        tabs.forEach((tab) => {
            const isActive = tab === activeTab;
            const panel = document.getElementById(tab.getAttribute('aria-controls'));

            tab.setAttribute('aria-selected', String(isActive));
            tab.setAttribute('tabindex', isActive ? '0' : '-1');
            tab.closest('.side-menu-item')?.classList.toggle('side-menu-item--active', isActive);
            panel.hidden = !isActive;
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab));
    });

    const hashTab = Array.from(tabs).find((tab) => tab.hash === window.location.hash);

    if (hashTab) {
        activateTab(hashTab);
    }
})();
