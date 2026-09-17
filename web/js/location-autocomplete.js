'use strict';

(() => {
    const locationInput = document.querySelector('[data-location-autocomplete]');

    if (!locationInput || typeof autoComplete === 'undefined') {
        return;
    }

    const latitudeInput = document.getElementById(locationInput.dataset.latitudeInput);
    const longitudeInput = document.getElementById(locationInput.dataset.longitudeInput);

    if (!latitudeInput || !longitudeInput) {
        return;
    }

    locationInput.addEventListener('input', () => {
        latitudeInput.value = '';
        longitudeInput.value = '';
    });

    new autoComplete({
        selector: () => locationInput,
        threshold: 3,
        debounce: 300,
        data: {
            src: async (query) => {
                const url = new URL(locationInput.dataset.suggestionsUrl, window.location.origin);
                url.searchParams.set('query', query);

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error('Не удалось загрузить адреса.');
                }

                return response.json();
            },
            keys: ['value'],
            cache: false,
        },
        resultsList: {
            maxResults: 5,
            noResults: true,
        },
        resultItem: {
            highlight: true,
        },
        events: {
            input: {
                selection: (event) => {
                    const suggestion = event.detail.selection.value;
                    locationInput.value = suggestion.value;
                    latitudeInput.value = suggestion.latitude;
                    longitudeInput.value = suggestion.longitude;
                },
            },
        },
    });
})();
