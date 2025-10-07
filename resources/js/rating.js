document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('#star-rating .star');
    const ratingInput = document.getElementById('rating-value');

    if (!stars.length || !ratingInput) return;

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const value = parseInt(star.dataset.value);
            ratingInput.value = value;

            stars.forEach(s => {
                s.classList.toggle('text-yellow-400', parseInt(s.dataset.value) <= value);
                s.classList.toggle('text-gray-300', parseInt(s.dataset.value) > value);
            });
        });

        // Optional hover preview
        star.addEventListener('mouseover', () => {
            const hoverValue = parseInt(star.dataset.value);
            stars.forEach(s => {
                s.classList.toggle('text-yellow-300', parseInt(s.dataset.value) <= hoverValue);
            });
        });

        star.addEventListener('mouseleave', () => {
            const currentValue = parseInt(ratingInput.value) || 0;
            stars.forEach(s => {
                s.classList.remove('text-yellow-300');
                s.classList.toggle('text-yellow-400', parseInt(s.dataset.value) <= currentValue);
                s.classList.toggle('text-gray-300', parseInt(s.dataset.value) > currentValue);
            });
        });
    });
});
