
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
