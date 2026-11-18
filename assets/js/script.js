
document.addEventListener('DOMContentLoaded', function () {

    // Close mobile navigation after selecting a link
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    const navbarCollapse = document.querySelector('.navbar-collapse');

    navLinks.forEach(function (link) {

        link.addEventListener('click', function () {

            if (
                navbarCollapse &&
                navbarCollapse.classList.contains('show')
            ) {
                const collapse = bootstrap.Collapse.getOrCreateInstance(
                    navbarCollapse
                );

                collapse.hide();
            }

        });

    });

    // Animate sections when they enter the screen
    const sections = document.querySelectorAll(
        '.service-card, .leader-card, .sermon-card, .event-card'
    );

    const observer = new IntersectionObserver(function (entries) {

        entries.forEach(function (entry) {

            if (entry.isIntersecting) {

                entry.target.classList.add('visible');

                observer.unobserve(entry.target);

            }

        });

    }, {
        threshold: 0.15
    });

    sections.forEach(function (section) {
        section.classList.add('reveal');
        observer.observe(section);
    });

});


// ==========================================
// ELCK KISUMU - GALLERY IMAGE VIEWER
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    const galleryModal = document.getElementById('galleryModal');

    if (!galleryModal) {
        return;
    }

    galleryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) {
            return;
        }

        const image = button.getAttribute('data-image');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');

        const modalImage = document.getElementById('galleryModalImage');
        const modalTitle = document.getElementById('galleryModalLabel');
        const modalDescription = document.getElementById('galleryModalDescription');

        modalImage.src = image;
        modalImage.alt = title || 'Church photograph';
        modalTitle.textContent = title || 'ELCK KISUMU Gallery';
        modalDescription.textContent = description || '';
    });

    galleryModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('galleryModalImage').src = '';
    });
});
