document.querySelectorAll('.news-item[data-url]').forEach((item) => {
    const openArticle = () => {
        window.open(item.dataset.url, '_blank', 'noopener,noreferrer');
    };

    item.addEventListener('click', (event) => {
        if (event.target.closest('a, button, input, select, textarea')) {
            return;
        }

        openArticle();
    });

    item.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.target.closest('a, button, input, select, textarea')) {
            return;
        }

        event.preventDefault();
        openArticle();
    });
});
