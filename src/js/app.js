// =============================================
// OmarValdez.com — Main Application
// Carga dinámica desde Supabase
// =============================================

(function () {
    'use strict';

    // --- DOM Ready ---
    document.addEventListener('DOMContentLoaded', async () => {
        initNavigation();
        initForms();

        // Cargar datos dinámicos desde Supabase
        await Promise.all([
            loadStats(),
            loadBlogPosts(),
            loadCourses(),
            loadResearch()
        ]);

        // Inicializar efectos DESPUÉS de cargar contenido dinámico
        initScrollEffects();
        initFilters();

        console.log('✅ Sitio web cargado exitosamente');
    });

    // =============================================
    // DYNAMIC DATA LOADERS
    // =============================================

    // --- Stats ---
    async function loadStats() {
        if (!window.API) return;
        try {
            const stats = await window.API.getSiteStats();
            if (!stats || !stats.length) return;

            const container = document.getElementById('statsContainer');
            if (!container) return;

            container.innerHTML = stats.map(s => {
                const num = parseInt(s.value);
                return `<div class="stat-card reveal">
                    <div class="stat-number" data-target="${num}">0</div>
                    <div class="stat-label">${esc(s.label)}</div>
                </div>`;
            }).join('');

            // Inicializar contadores después de renderizar
            initCounters();
        } catch (e) { console.error('Error cargando stats:', e); }
    }

    // --- Blog Posts ---
    async function loadBlogPosts() {
        if (!window.API) return;
        try {
            const posts = await window.API.getPosts('all', 20);
            if (!posts || !posts.length) return;

            const container = document.getElementById('blogGrid');
            if (!container) return;

            container.innerHTML = posts.map(p => {
                const iconMap = { blockchain: '<i class="fas fa-link"></i>', ia: '<i class="fas fa-robot"></i>', marketing: '<i class="fas fa-bullhorn"></i>', fintech: '<i class="fas fa-briefcase"></i>', ecommerce: '<i class="fas fa-shopping-cart"></i>' };
                const catLabel = { blockchain: 'Blockchain', ia: 'IA', marketing: 'Marketing', fintech: 'Fintech', ecommerce: 'eCommerce' };
                const icon = iconMap[p.category] || '<i class="fas fa-file-alt"></i>';
                const date = new Date(p.created_at).toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });

                return `<article class="blog-card reveal" data-category="${esc(p.category)}">
                    <div class="blog-thumb">${p.image_url ? `<img src="${esc(p.image_url)}" alt="${esc(p.title)}" style="width:100%;height:100%;object-fit:cover;">` : icon}</div>
                    <div class="blog-body">
                        <div class="blog-meta">
                            <span class="blog-cat">${catLabel[p.category] || p.category}</span>
                            <span class="blog-date">${date}</span>
                        </div>
                        <h3>${esc(p.title)}</h3>
                        <p class="excerpt">${esc(p.excerpt || '')}</p>
                        <a href="/post.html?slug=${esc(p.slug)}" class="blog-link">Leer artículo →</a>
                    </div>
                </article>`;
            }).join('');
        } catch (e) { console.error('Error cargando posts:', e); }
    }

    // --- Courses ---
    async function loadCourses() {
        if (!window.API) return;
        try {
            const courses = await window.API.getCourses();
            if (!courses || !courses.length) return;

            const container = document.getElementById('coursesGrid');
            if (!container) return;

            container.innerHTML = courses.map(c => {
                const topicsHtml = c.topics && c.topics.length
                    ? `<ul class="course-topics">${c.topics.map(t => `<li>${esc(t)}</li>`).join('')}</ul>`
                    : '';
                const badgeHtml = c.badge
                    ? `<span class="course-badge">${esc(c.badge)}</span>`
                    : '';
                const btnClass = c.featured ? 'btn btn-primary' : 'btn btn-outline';
                const ctaText = c.cta_text || 'Más Información';
                const ctaUrl = c.cta_url || '#contacto';

                return `<div class="course-card${c.featured ? ' featured' : ''} reveal">
                    ${badgeHtml}
                    <div class="course-icon">${c.icon || '<i class="fas fa-book"></i>'}</div>
                    <h3>${esc(c.title)}</h3>
                    <p>${esc(c.description || '')}</p>
                    ${topicsHtml}
                    <a href="${esc(ctaUrl)}" class="${btnClass}">${esc(ctaText)}${c.featured ? ' <span class="btn-arrow">→</span>' : ''}</a>
                </div>`;
            }).join('');
        } catch (e) { console.error('Error cargando cursos:', e); }
    }

    // --- Research ---
    async function loadResearch() {
        if (!window.API) return;
        try {
            const research = await window.API.getResearch('all');
            if (!research || !research.length) return;

            const container = document.getElementById('researchGrid');
            if (!container) return;

            const typeIcon = { 'Artículo': '<i class="fas fa-file-alt"></i>', 'Capítulo de Libro': '<i class="fas fa-book-open"></i>', 'Libro': '<i class="fas fa-book"></i>', 'Ponencia': '<i class="fas fa-microphone"></i>' };
            const catLabel = { marketing: 'Marketing Digital', ecommerce: 'Comercio Electrónico', innovacion: 'Innovación y Tecnología', gestion: 'Gestión Empresarial', salud: 'Sector Salud' };

            container.innerHTML = research.map(r => {
                const doiLink = r.doi
                    ? `<a href="https://doi.org/${esc(r.doi)}" class="research-doi-link" target="_blank" rel="noopener">https://doi.org/${esc(r.doi)}</a>`
                    : '';
                const articleLink = r.external_url
                    ? `<a href="${esc(r.external_url)}" class="research-view-btn" target="_blank" rel="noopener">Ver artículo completo →</a>`
                    : '';
                const pdfLink = r.pdf_url
                    ? `<a href="${esc(r.pdf_url)}" class="research-pdf-btn" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> PDF</a>`
                    : '';
                const identifiers = [];
                if (r.issn) identifiers.push(`ISSN: ${esc(r.issn)}`);
                if (r.isbn) identifiers.push(`ISBN: ${esc(r.isbn)}`);

                return `<article class="research-item reveal" data-category="${esc(r.type)}">
                    ${r.featured ? '<span class="research-featured-badge"><i class="fas fa-star"></i> Destacado</span>' : ''}
                    <div class="research-item-header">
                        <span class="research-type-badge">${typeIcon[r.type] || '<i class="fas fa-file-alt"></i>'} ${esc(r.type || 'Artículo')}</span>
                        <span class="research-year-badge">${r.year}</span>
                    </div>
                    <h3 class="research-item-title">${esc(r.title)}</h3>
                    ${r.authors ? `<p class="research-item-authors">${esc(r.authors)}</p>` : ''}
                    ${r.journal ? `<p class="research-item-journal"><em>${esc(r.journal)}</em>${r.volume_issue ? ' · ' + esc(r.volume_issue) : ''}</p>` : ''}
                    ${r.description ? `<div class="research-item-abstract"><strong>Resumen:</strong> ${esc(r.description)}</div>` : ''}
                    ${doiLink}
                    ${identifiers.length ? `<p class="research-item-ids">${identifiers.join(' · ')}</p>` : ''}
                    ${r.citation_apa ? `<div class="research-apa-box"><span class="research-apa-label">Citar (APA 7):</span><p class="research-apa-text">${esc(r.citation_apa)}</p><button class="research-copy-btn" onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent).then(()=>{this.innerHTML='<i class=\\'fas fa-check\\'></i> Copiado';setTimeout(()=>this.innerHTML='<i class=\\'fas fa-copy\\'></i> Copiar',2000)})"><i class="fas fa-copy"></i> Copiar</button></div>` : ''}
                    <div class="research-item-actions">
                        ${articleLink}
                        ${pdfLink}
                    </div>
                </article>`;
            }).join('');
        } catch (e) { console.error('Error cargando research:', e); }
    }

    // =============================================
    // NAVIGATION
    // =============================================
    function initNavigation() {
        const navbar = document.getElementById('navbar');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');
        const navLinks = document.querySelectorAll('.nav-links a');

        if (menuBtn) {
            menuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                menuBtn.classList.toggle('active');
                document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
            });
        }

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                menuBtn.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        window.addEventListener('scroll', () => {
            if (window.scrollY > 80) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        const sections = document.querySelectorAll('section[id]');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const top = section.offsetTop - 200;
                if (window.scrollY >= top) {
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

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }

    // =============================================
    // SCROLL REVEAL ANIMATIONS
    // =============================================
    function initScrollEffects() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -60px 0px'
        });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    }

    // =============================================
    // CATEGORY FILTERS (Research + Blog)
    // =============================================
    function initFilters() {
        setupFilter('.research-section .filter-btn', '#researchGrid .research-item');
        setupFilter('#blogFilters .filter-btn', '#blogGrid .blog-card');
    }

    function setupFilter(btnSelector, cardSelector) {
        const buttons = document.querySelectorAll(btnSelector);

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter');
                const cards = document.querySelectorAll(cardSelector);

                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                cards.forEach(card => {
                    const cat = card.getAttribute('data-category');
                    if (filter === 'all' || cat === filter) {
                        card.style.display = '';
                        requestAnimationFrame(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        });
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => { card.style.display = 'none'; }, 300);
                    }
                });
            });
        });
    }

    // =============================================
    // COUNTER ANIMATION
    // =============================================
    function initCounters() {
        const counters = document.querySelectorAll('.stat-number');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.counted) {
                    animateCounter(entry.target);
                    entry.target.dataset.counted = 'true';
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    }

    function animateCounter(el) {
        const target = parseInt(el.dataset.target);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;

        const update = () => {
            current += step;
            if (current < target) {
                el.textContent = Math.floor(current) + '+';
                requestAnimationFrame(update);
            } else {
                el.textContent = target + '+';
            }
        };
        update();
    }

    // =============================================
    // FORMS (Contact + Newsletter)
    // =============================================
    function initForms() {
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = contactForm.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.innerHTML = 'Enviando...';
                btn.disabled = true;

                const formData = {
                    name: contactForm.name.value,
                    email: contactForm.email.value,
                    type: contactForm.type.value,
                    message: contactForm.message.value
                };

                try {
                    if (window.API && typeof window.API.submitContact === 'function') {
                        const success = await window.API.submitContact(formData);
                        if (success) {
                            showToast('¡Gracias por tu mensaje! Te contactaré pronto.', 'success');
                            // GA4 Event: Contact form submission
                            if (typeof gtag === 'function') {
                                gtag('event', 'contact_form_submit', {
                                    event_category: 'engagement',
                                    event_label: formData.type,
                                    contact_type: formData.type
                                });
                            }
                            contactForm.reset();
                        } else {
                            throw new Error('Error al enviar');
                        }
                    } else {
                        console.log('📧 Contacto (modo local):', formData);
                        showToast('¡Gracias por tu mensaje! Te contactaré pronto.', 'success');
                        contactForm.reset();
                    }
                } catch (err) {
                    showToast('Error al enviar. Intenta de nuevo.', 'error');
                    console.error(err);
                }

                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        const newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const emailInput = newsletterForm.querySelector('input[type="email"]');
                const email = emailInput.value;

                try {
                    if (window.API && typeof window.API.subscribeNewsletter === 'function') {
                        const success = await window.API.subscribeNewsletter(email);
                        if (success) {
                            showToast('¡Suscripción exitosa! Recibirás nuestras actualizaciones.', 'success');
                            // GA4 Event: Newsletter signup
                            if (typeof gtag === 'function') {
                                gtag('event', 'newsletter_signup', {
                                    event_category: 'engagement',
                                    event_label: 'footer_form'
                                });
                            }
                            newsletterForm.reset();
                        } else {
                            throw new Error('Error');
                        }
                    } else {
                        console.log('📬 Newsletter (modo local):', email);
                        showToast('¡Suscripción exitosa!', 'success');
                        newsletterForm.reset();
                    }
                } catch (err) {
                    showToast('Error al suscribir. Intenta de nuevo.', 'error');
                }
            });
        }
    }

    // =============================================
    // UTILITIES
    // =============================================
    function esc(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast ' + type;
        void toast.offsetWidth;
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 4000);
    }

    window.showToast = showToast;

})();
