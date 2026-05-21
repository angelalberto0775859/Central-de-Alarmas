import fs from 'node:fs';
import vm from 'node:vm';

const siteUrl = 'https://centraldealarmas.com.mx';
const lastmod = '2026-05-21';
const dataSource = fs.readFileSync('js/blog-data.js', 'utf8');
const context = { window: {} };
vm.createContext(context);
vm.runInContext(dataSource, context);

const posts = context.window.CDABlogPosts;
if (!Array.isArray(posts) || posts.length === 0) {
    throw new Error('No blog posts found in js/blog-data.js');
}

const htmlEscape = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const stripAccents = (value) => String(value)
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');

const dateIso = (date) => {
    const months = {
        enero: '01',
        febrero: '02',
        marzo: '03',
        abril: '04',
        mayo: '05',
        junio: '06',
        julio: '07',
        agosto: '08',
        septiembre: '09',
        octubre: '10',
        noviembre: '11',
        diciembre: '12'
    };
    const match = String(date).toLowerCase().match(/^(\d{1,2})\s+([a-záéíóúñ]+)\s+(\d{4})$/);
    if (!match) return lastmod;
    const [, day, month, year] = match;
    return `${year}-${months[stripAccents(month)] || '01'}-${day.padStart(2, '0')}`;
};

const arrowIcon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';

const card = (post, nested = false, compact = false) => {
    const root = nested ? '../' : '';
    const url = `${root}blog/${post.slug}.html`;
    const img = `${root}${post.image}`;
    return `
            <article class="blog-card${compact ? ' compact' : ''}" data-aos="fade-up">
                <a class="blog-card-media" href="${url}">
                    <img src="${img}" alt="${htmlEscape(post.title)}">
                </a>
                <div class="blog-card-body">
                    <span class="blog-kicker">${htmlEscape(post.category)}</span>
                    <div class="blog-meta"><span>${htmlEscape(post.date)}</span><span>${htmlEscape(post.readTime)}</span></div>
                    <h3><a href="${url}">${htmlEscape(post.title)}</a></h3>
                    <p>${htmlEscape(post.excerpt)}</p>
                    <a class="blog-read-link" href="${url}">Seguir leyendo ${arrowIcon}</a>
                </div>
            </article>`;
};

const collectionSchema = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'Central News',
    url: `${siteUrl}/blog.html`,
    description: 'Guías de seguridad, monitoreo, videovigilancia, alarmas y prevención para hogares, negocios y empresas en México.',
    mainEntity: posts.map((post, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        url: `${siteUrl}/blog/${post.slug}.html`,
        name: post.title
    }))
};

let blogHtml = fs.readFileSync('blog.html', 'utf8');
blogHtml = blogHtml
    .replace(
        /<title>.*?<\/title>/,
        '<title>Blog de seguridad, alarmas y monitoreo | Central de Alarmas</title>'
    )
    .replace(
        /<meta name="description" content="[^"]*">/,
        '<meta name="description" content="Guías de seguridad, alarmas, monitoreo 24/7, videovigilancia, control de acceso e incendios para hogares, PyMEs y empresas en México.">'
    )
    .replace(
        /<meta property="og:title" content="[^"]*">/,
        '<meta property="og:title" content="Blog de seguridad | Central de Alarmas">'
    )
    .replace(
        /<meta property="og:description" content="[^"]*">/,
        '<meta property="og:description" content="Criterios prácticos para elegir alarmas, cámaras, monitoreo y soluciones de seguridad en México.">'
    )
    .replace(
        /<script type="application\/ld\+json">.*?<\/script>/,
        `<script type="application/ld+json">${JSON.stringify(collectionSchema)}</script>`
    )
    .replace(
        /<div class="blog-grid" id="blogGrid">[\s\S]*?<\/div>\s*<\/section>/,
        `<div class="blog-grid" id="blogGrid">${posts.map((post) => card(post)).join('\n')}</div>\n        </section>`
    );
fs.writeFileSync('blog.html', blogHtml);

const relatedCards = (current) => posts
    .filter((post) => post.slug !== current.slug)
    .slice(0, 8)
    .map((post) => card(post, true))
    .join('\n');

const articleHtml = (post) => {
    const iso = dateIso(post.date);
    const schema = {
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: post.title,
        description: post.excerpt,
        image: `${siteUrl}/${post.image}`,
        datePublished: iso,
        dateModified: lastmod,
        author: { '@type': 'Organization', name: 'Central de Alarmas' },
        publisher: {
            '@type': 'Organization',
            name: 'Central de Alarmas',
            logo: { '@type': 'ImageObject', url: `${siteUrl}/img/CDA_LOGO.svg` }
        },
        mainEntityOfPage: `${siteUrl}/blog/${post.slug}.html`
    };

    return `<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${htmlEscape(post.title)} | Central de Alarmas</title>
    <meta name="description" content="${htmlEscape(post.excerpt)}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="${siteUrl}/blog/${post.slug}.html">
    <meta property="og:type" content="article">
    <meta property="og:title" content="${htmlEscape(post.title)}">
    <meta property="og:description" content="${htmlEscape(post.excerpt)}">
    <meta property="og:url" content="${siteUrl}/blog/${post.slug}.html">
    <meta property="og:image" content="${siteUrl}/${post.image}">
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">${JSON.stringify(schema)}</script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="../css/styles.css" />
</head>
<body class="blog-page article-page">
    <header id="main-header" class="scrolled">
        <div class="logo"><a href="../index.html#inicio"><img src="../img/cda-logo-f.svg" alt="Central de Alarmas"></a></div>
        <nav id="main-nav">
            <a href="../index.html#servicios-preview">Servicios</a>
            <a href="../index.html#packages-section">Paquetes</a>
            <a href="../index.html#nosotros">Nosotros</a>
            <a href="../index.html#contacto">Contacto</a>
            <a href="../blog.html">News</a>
            <a href="../empleos.html" data-i18n="nav.jobs">Empleos</a>
            <a href="https://centraldealarmas.trytoku.com/" class="nav-cta">Pagar</a>
        </nav>
        <div class="hamburger" id="hamburger" aria-label="Abrir menu"><span></span><span></span><span></span></div>
    </header>
    <main>
        <article id="blogArticle" data-slug="${post.slug}">
            <section class="article-hero">
                <div class="article-hero-copy">
                    <a class="article-back" href="../blog.html">${arrowIcon} News</a>
                    <span class="blog-kicker">${htmlEscape(post.category)}</span>
                    <h1>${htmlEscape(post.title)}</h1>
                    <p>${htmlEscape(post.intro)}</p>
                    <div class="blog-meta"><span>${htmlEscape(post.date)}</span><span>${htmlEscape(post.readTime)}</span></div>
                </div>
                <div class="article-hero-media"><img src="../${post.image}" alt="${htmlEscape(post.title)}"></div>
            </section>
            <section class="article-body">
${post.sections.map((section) => `
                <div class="article-section">
                    <h2>${htmlEscape(section.heading)}</h2>
                    <p>${htmlEscape(section.body)}</p>
                </div>`).join('')}
                <aside class="article-takeaway"><span>Idea clave</span><p>${htmlEscape(post.takeaway)}</p></aside>
            </section>
        </article>
        <section class="related-section">
            <div class="section-header"><span class="label-tag">Mas lecturas</span><h2>Tambien te puede interesar</h2></div>
            <div class="blog-grid related-grid" id="relatedPosts">${relatedCards(post)}</div>
        </section>
    </main>
    <footer class="article-footer">
        <div class="footer-inner">
            <div class="footer-top">
                <div class="footer-brand"><div class="logo"><a href="../index.html#inicio"><img src="../img/CDA_LOGO.svg" alt="Central de Alarmas"></a></div><p style="margin-top:1rem;">Empresa mexicana de seguridad electronica con mas de 79 anos protegiendo lo que mas importa.</p></div>
                <div class="footer-col"><h4>Navegacion</h4><a href="../index.html#servicios-preview">Servicios</a><a href="../index.html#nosotros">Nosotros</a><a href="../index.html#contacto">Contacto</a><a href="../blog.html">News</a><a href="../empleos.html" data-i18n="nav.jobs">Empleos</a></div>
                <div class="footer-col"><h4>Legal</h4><a href="../pdf/POLÍTICA DE ATENCIÓN A CLIENTES 2025 HOME.pdf" target="_blank">Terminos y Condiciones</a><a href="../pdf/AVISO-DE-PRIVACIDAD.pdf" target="_blank">Politica de Privacidad</a></div>
            </div>
            <div class="footer-bottom"><div class="gsi"><img src="../img/GRUPO_LOGO.svg" alt="GSI"></div><p>© 2026 Central de Alarmas de Mexico, S.A. de C.V. Todos los derechos reservados.</p></div>
        </div>
    </footer>
    <script src="https://unpkg.com/aos@next/dist/aos.js" defer></script>
    <script src="../js/blog-data.js" defer></script>
    <script src="../js/blog.js" defer></script>
    <script src="../js/language-switcher.js" defer></script>
    <script src="../js/cookies.js" defer></script>
    <script src="../js/main.js" defer></script>
</body>
</html>
`;
};

posts.forEach((post) => {
    fs.writeFileSync(`blog/${post.slug}.html`, articleHtml(post));
});

const baseUrls = [
    { loc: '/', changefreq: 'weekly', priority: '1.0' },
    { loc: '/empresa.html', changefreq: 'monthly', priority: '0.8' },
    { loc: '/blog.html', changefreq: 'weekly', priority: '0.8' },
    { loc: '/empleos.html', changefreq: 'weekly', priority: '0.8' },
    { loc: '/vacantes/monitorista.html', changefreq: 'weekly', priority: '0.7' },
    { loc: '/vacantes/tecnico-instalador.html', changefreq: 'weekly', priority: '0.7' },
    { loc: '/vacantes/ejecutivo-cobranza.html', changefreq: 'weekly', priority: '0.7' },
    ...posts.map((post) => ({
        loc: `/blog/${post.slug}.html`,
        changefreq: 'monthly',
        priority: '0.6'
    }))
];

const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${baseUrls.map((item) => `  <url>
    <loc>${siteUrl}${item.loc === '/' ? '/' : item.loc}</loc>
    <lastmod>${lastmod}</lastmod>
    <changefreq>${item.changefreq}</changefreq>
    <priority>${item.priority}</priority>
  </url>`).join('\n')}
</urlset>
`;

fs.writeFileSync('sitemap.xml', sitemap);
