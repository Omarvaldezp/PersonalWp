// ========================================
// Professional Academic Website
// Dr. Omar Valdez Palazuelos
// ========================================

class AcademicWebsite {
    constructor() {
        this.init();
    }

    init() {
        console.log('✅ Inicializando sitio web académico profesional');
        this.setupNavigation();
        this.setupScrollEffects();
        this.setupFilters();
        this.setupForms();
        this.setupAnimations();
    }

    // === NAVIGATION ===
    setupNavigation() {
        const navbar = document.getElementById('navbar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');
        const navLinks = document.querySelectorAll('.nav-link');

        // Mobile menu toggle
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                mobileMenuBtn.classList.toggle('active');
            });
        }

        // Close mobile menu when clicking a link
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
            });
        });

        // Navbar scroll effect
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });

        // Active link on scroll
        this.setupActiveLink();
    }

    setupActiveLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    }

    // === SCROLL EFFECTS ===
    setupScrollEffects() {
        // Smooth reveal animations on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        const animatedElements = document.querySelectorAll(
            '.service-card, .stat-card, .course-card, .blog-card, .research-card'
        );

        animatedElements.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    }

    // === FILTERS ===
    setupFilters() {
        // Research filters
        this.setupCategoryFilter('.filter-btn', '.research-card');

        // Blog category filters
        this.setupCategoryFilter('.category-btn', '.blog-card');
    }

    setupCategoryFilter(buttonSelector, itemSelector) {
        const filterButtons = document.querySelectorAll(buttonSelector);
        const items = document.querySelectorAll(itemSelector);

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const filter = button.getAttribute('data-filter') || button.getAttribute('data-category');

                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Filter items
                items.forEach(item => {
                    const category = item.getAttribute('data-category');

                    if (filter === 'all' || category === filter) {
                        item.style.display = '';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'scale(1)';
                        }, 10);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // === FORMS ===
    setupForms() {
        // Contact form
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleContactForm(contactForm);
            });
        }

        // Newsletter form
        const newsletterForms = document.querySelectorAll('.newsletter-form');
        newsletterForms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleNewsletterForm(form);
            });
        });
    }

    handleContactForm(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Here you would typically send to a backend
        console.log('📧 Formulario de contacto enviado:', data);

        // Show success message (you can customize this)
        alert('¡Gracias por tu mensaje! Te contactaremos pronto.');
        form.reset();

        // TODO: Integrate with backend API
        // fetch('/api/contact', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify(data)
        // });
    }

    handleNewsletterForm(form) {
        const email = form.querySelector('input[type="email"]').value;

        console.log('📬 Suscripción al newsletter:', email);

        // Show success message
        alert('¡Gracias por suscribirte! Recibirás nuestras actualizaciones.');
        form.reset();

        // TODO: Integrate with newsletter service
        // fetch('/api/newsletter', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ email })
        // });
    }

    // === ANIMATIONS ===
    setupAnimations() {
        // Counter animation for stats
        this.animateCounters();

        // Parallax effect for hero background
        this.setupParallax();
    }

    animateCounters() {
        const counters = document.querySelectorAll('.stat-number');

        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    this.animateCounter(entry.target);
                    entry.target.classList.add('counted');
                }
            });
        }, observerOptions);

        counters.forEach(counter => observer.observe(counter));
    }

    animateCounter(element) {
        const target = parseInt(element.textContent);
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;

        const updateCounter = () => {
            current += increment;
            if (current < target) {
                element.textContent = Math.floor(current) + '+';
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target + '+';
            }
        };

        updateCounter();
    }

    setupParallax() {
        const heroBackground = document.querySelector('.hero-background');

        if (heroBackground) {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * 0.5;
                heroBackground.style.transform = `translateY(${rate}px)`;
            });
        }
    }
}

// === INITIALIZE APP ===
document.addEventListener('DOMContentLoaded', () => {
    const app = new AcademicWebsite();
    console.log('🚀 Sitio web cargado exitosamente');
});

// === UTILITY FUNCTIONS ===

// Smooth scroll to anchor
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// Prevent default for placeholder links
document.querySelectorAll('a[href="#"]').forEach(link => {
    link.addEventListener('click', (e) => {
        if (link.getAttribute('href') === '#') {
            e.preventDefault();
        }
    });
});

export default AcademicWebsite;
