(function () {
    const ratings = document.querySelectorAll('.active-stars[data-rating-input]');

    ratings.forEach(function (rating) {
        const input = document.getElementById(rating.dataset.ratingInput);
        const stars = Array.from(rating.querySelectorAll('[data-score]'));

        if (!input) {
            return;
        }

        const selectScore = function (score) {
            input.value = score;
            input.dispatchEvent(new Event('change', {bubbles: true}));

            stars.forEach(function (star) {
                const starScore = Number(star.dataset.score);

                star.classList.toggle('fill-star', starScore <= score);
                star.setAttribute('aria-checked', starScore === score ? 'true' : 'false');
            });
        };

        stars.forEach(function (star) {
            star.addEventListener('click', function () {
                selectScore(Number(star.dataset.score));
            });

            star.addEventListener('keydown', function (evt) {
                if (evt.key === 'Enter' || evt.key === ' ') {
                    evt.preventDefault();
                    selectScore(Number(star.dataset.score));
                }
            });
        });
    });
}());
