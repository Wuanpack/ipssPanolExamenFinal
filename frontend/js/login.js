document.addEventListener('DOMContentLoaded', () => {

    const carousels = document.querySelectorAll('.carousel-fondo');

    carousels.forEach(carousel => {
        const instance = new bootstrap.Carousel(carousel, {
            interval: false
        });

        setInterval(() => {
            instance.next();
        }, 5000);
    });
});