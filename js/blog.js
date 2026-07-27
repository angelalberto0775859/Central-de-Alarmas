(function() {
    const posts = window.CDABlogPosts || [];
    const isNested = window.location.pathname.includes('/blog/');
    const assetBase = isNested ? '../' : '';

    function postUrl(post) {
        return `${assetBase}blog/${post.slug}.html`;
    }

    function imageUrl(post) {
        return `${assetBase}${post.image}`;
    }

    function arrowIcon() {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    function renderBlogList() {
        const grid = document.getElementById('blogGrid');
        if (!grid) return;

        renderTopicFilters(posts);
        renderPostCount(posts.length, 'Todos');
        renderCards(posts);
    }

    function renderCards(items) {
        const grid = document.getElementById('blogGrid');
        if (!grid) return;

        grid.innerHTML = items.map(post => `
            <article class="blog-card" data-aos="fade-up">
                <a class="blog-card-media" href="${postUrl(post)}">
                    <img src="${imageUrl(post)}" alt="${post.title}">
                </a>
                <div class="blog-card-body">
                    <span class="blog-kicker">${post.category}</span>
                    <div class="blog-meta">
                        <span>${post.date}</span>
                        <span>${post.readTime}</span>
                    </div>
                    <h3><a href="${postUrl(post)}">${post.title}</a></h3>
                    <p>${post.excerpt}</p>
                    <a class="blog-read-link" href="${postUrl(post)}">Seguir leyendo ${arrowIcon()}</a>
                </div>
            </article>
        `).join('');
    }

    function renderTopicFilters(items) {
        const filters = document.getElementById('topicFilters');
        if (!filters) return;

        const categories = ['Todos', ...new Set(items.map(post => post.category))];
        filters.innerHTML = categories.map((category, index) => `
            <button type="button" class="news-filter ${index === 0 ? 'active' : ''}" data-category="${category}">
                ${category}
            </button>
        `).join('');

        filters.querySelectorAll('.news-filter').forEach(button => {
            button.addEventListener('click', () => {
                filters.querySelectorAll('.news-filter').forEach(item => item.classList.remove('active'));
                button.classList.add('active');
                const category = button.dataset.category;
                const filtered = category === 'Todos' ? posts : posts.filter(post => post.category === category);
                renderCards(filtered);
                renderPostCount(category === 'Todos' ? posts.length : filtered.length, category);
            });
        });
    }

    function renderPostCount(count, category) {
        const postCount = document.getElementById('postCount');
        if (!postCount) return;

        postCount.textContent = category === 'Todos'
            ? `${count} notas editoriales disponibles`
            : `${count} notas en ${category}`;
    }

    function renderArticle() {
        const article = document.getElementById('blogArticle');
        if (!article) return;

        const slug = article.dataset.slug;
        const post = posts.find(item => item.slug === slug);
        if (!post) {
            article.innerHTML = `
                <div class="article-shell">
                    <p class="blog-kicker">Central News</p>
                    <h1>Articulo no encontrado</h1>
                    <p>No encontramos esta entrada. Puedes volver a News para consultar otros temas de seguridad.</p>
                    <a class="blog-read-link" href="../blog.html">Volver a News ${arrowIcon()}</a>
                </div>
            `;
            return;
        }

        const related = document.getElementById('relatedPosts');
        if (article.children.length > 0) {
            if (related) setupRelatedCarousel(related);
            return;
        }

        document.title = `${post.title} | Central de Alarmas`;
        const description = document.querySelector('meta[name="description"]');
        if (description) description.setAttribute('content', post.excerpt);

        article.innerHTML = `
            <section class="article-hero">
                <div class="article-hero-copy">
                    <a class="article-back" href="../blog.html">${arrowIcon()} News</a>
                    <span class="blog-kicker">${post.category}</span>
                    <h1>${post.title}</h1>
                    <p>${post.intro}</p>
                    <div class="blog-meta">
                        <span>${post.date}</span>
                        <span>${post.readTime}</span>
                    </div>
                </div>
                <div class="article-hero-media">
                    <img src="${imageUrl(post)}" alt="${post.title}">
                </div>
            </section>
            <section class="article-body">
                ${post.sections.map(section => `
                    <div class="article-section">
                        <h2>${section.heading}</h2>
                        <p>${section.body}</p>
                    </div>
                `).join('')}
                <aside class="article-takeaway">
                    <span>Idea clave</span>
                    <p>${post.takeaway}</p>
                </aside>
            </section>
        `;

        if (related) {
            related.innerHTML = posts
                .filter(item => item.slug !== post.slug)
                .slice(0, 8)
                .map(item => `
                    <article class="blog-card compact">
                        <a class="blog-card-media" href="${postUrl(item)}">
                            <img src="${imageUrl(item)}" alt="${item.title}">
                        </a>
                        <div class="blog-card-body">
                            <div class="blog-meta"><span>${item.category}</span><span>${item.readTime}</span></div>
                            <h3><a href="${postUrl(item)}">${item.title}</a></h3>
                            <a class="blog-read-link" href="${postUrl(item)}">Leer mas ${arrowIcon()}</a>
                        </div>
                    </article>
                `).join('');
            setupRelatedCarousel(related);
        }
    }

    function setupRelatedCarousel(track) {
        if (!track || track.dataset.carouselReady === 'true') return;

        const section = track.closest('.related-section');
        if (!section) return;

        const shell = document.createElement('div');
        shell.className = 'related-carousel';

        const controls = document.createElement('div');
        controls.className = 'related-carousel-controls';
        controls.innerHTML = `
            <button type="button" class="related-arrow" data-related-direction="-1" aria-label="Ver nota anterior">${arrowIcon()}</button>
            <button type="button" class="related-arrow" data-related-direction="1" aria-label="Ver nota siguiente">${arrowIcon()}</button>
        `;

        track.parentNode.insertBefore(shell, track);
        shell.appendChild(track);
        section.appendChild(controls);
        track.dataset.carouselReady = 'true';

        controls.querySelectorAll('.related-arrow').forEach(button => {
            button.addEventListener('click', () => {
                const direction = Number(button.dataset.relatedDirection);
                const distance = Math.max(track.clientWidth * 0.82, 280);
                track.scrollBy({ left: distance * direction, behavior: 'smooth' });
            });
        });

        startAutoCarousel(track);
    }

    function setupSuggestionsCarousel() {
        const track = document.getElementById('blogSuggestionsTrack');
        if (!track || track.dataset.carouselReady === 'true') return;
        track.dataset.carouselReady = 'true';
        startAutoCarousel(track);
    }

    function startAutoCarousel(track) {
        if (!track || track.dataset.autoCarousel === 'true') return;
        track.dataset.autoCarousel = 'true';

        let isPaused = false;
        const pause = () => { isPaused = true; };
        const resume = () => { isPaused = false; };

        track.addEventListener('mouseenter', pause);
        track.addEventListener('mouseleave', resume);
        track.addEventListener('focusin', pause);
        track.addEventListener('focusout', resume);

        setInterval(() => {
            if (isPaused || track.scrollWidth <= track.clientWidth) return;

            const distance = Math.max(track.clientWidth * 0.72, 260);
            const nearEnd = track.scrollLeft + track.clientWidth + distance >= track.scrollWidth - 8;
            track.scrollTo({
                left: nearEnd ? 0 : track.scrollLeft + distance,
                behavior: 'smooth'
            });
        }, 4200);
    }

    function renderArticleFooter() {
        if (!document.body.classList.contains('article-page') || document.querySelector('footer')) return;

        const footer = document.createElement('footer');
        footer.className = 'article-footer';
        footer.innerHTML = `
            <div class="footer-inner">
                <div class="footer-top">
                    <div class="footer-brand">
                        <div class="logo"><a href="../index.html#inicio"><img src="../img/CDA_LOGO.svg" alt="Central de Alarmas"></a></div>
                        <p style="margin-top:1rem;">Empresa mexicana de seguridad electronica con mas de 79 anos protegiendo lo que mas importa.</p>
                    </div>
                    <div class="footer-col">
                        <h4>Navegacion</h4>
                        <a href="../index.html#servicios-preview">Servicios</a>
                        <a href="../index.html#nosotros">Nosotros</a>
                        <a href="../index.html#certificaciones">Certificaciones</a>
                        <a href="../index.html#contacto">Contacto</a>
                        <a href="../blog.html">News</a>
                    </div>
                    <div class="footer-col">
                        <h4>Legal</h4>
                        <a href="../pdf/POLÍTICA DE ATENCIÓN A CLIENTES 2025 HOME.pdf" target="_blank">Terminos y Condiciones</a>
                        <a href="../aviso-de-privacidad.html" target="_blank">Politica de Privacidad</a>
                    </div>
                </div>
                <div class="footer-bottom">
                    <div class="gsi"><img src="../img/GRUPO_LOGO.svg" alt="GSI"></div>
                    <p>© 2026 Central de Alarmas de Mexico, S.A. de C.V. Todos los derechos reservados.</p>
                </div>
            </div>
        `;
        document.body.appendChild(footer);
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderBlogList();
        renderArticle();
        setupSuggestionsCarousel();
        renderArticleFooter();
    });
})();
