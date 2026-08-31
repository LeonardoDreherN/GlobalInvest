/*
 * Global Invest Brasil - interações do front-end.
 * As cotações são obtidas do endpoint interno /api/cotacoes.
 * Nenhuma chave privada ou credencial é exposta no navegador.
 */

/* Banner de consentimento de cookies (LGPD). A escolha grava um cookie próprio,
   lido no servidor por /adsense-loader.php para só carregar anúncios após aceite total. */
(function cookieConsentBanner() {
  const STORAGE_KEY = "globalinvest:cookie-consent";
  if (localStorage.getItem(STORAGE_KEY)) return;

  function setConsentCookie(value) {
    document.cookie = `globalinvest_cookie_consent=${value}; path=/; max-age=31536000; SameSite=Lax`;
  }

  function recordConsent(preferences) {
    localStorage.setItem(STORAGE_KEY, preferences);
    setConsentCookie(preferences);
    fetch("/api/cookie-consent", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ preferences }),
    }).catch(() => {});
  }

  const banner = document.createElement("div");
  banner.className = "cookie-consent-banner";
  banner.setAttribute("role", "dialog");
  banner.setAttribute("aria-label", "Consentimento de cookies");
  banner.innerHTML = `
    <div class="cookie-consent-copy">
      <p>Usamos cookies para melhorar sua experiência e, quando ativado, exibir publicidade. Saiba mais na <a href="/privacidade.html">Política de Privacidade</a>.</p>
    </div>
    <div class="cookie-consent-actions">
      <button type="button" class="cookie-consent-btn essential" data-cookie-choice="rejected_optional">Somente essenciais</button>
      <button type="button" class="cookie-consent-btn accept" data-cookie-choice="accepted_all">Aceitar todos</button>
    </div>`;
  document.body.appendChild(banner);
  requestAnimationFrame(() => banner.classList.add("visible"));

  banner.addEventListener("click", (event) => {
    const button = event.target.closest("[data-cookie-choice]");
    if (!button) return;
    recordConsent(button.dataset.cookieChoice);
    banner.classList.remove("visible");
    setTimeout(() => banner.remove(), 300);
  });
})();
const marketData = [
  { symbol: "B3", tone: "b3", name: "Ibovespa", value: "128.250 pts", change: "+0,72%", direction: "up", note: "Índice de referência da bolsa brasileira." },
  { symbol: "30", tone: "dow", name: "Dow Jones", value: "44.780 pts", change: "-0,09%", direction: "down", note: "Índice de grandes companhias negociadas nos Estados Unidos." },
  { symbol: "NQ", tone: "nasdaq", name: "Nasdaq 100", value: "22.950 pts", change: "+0,38%", direction: "up", note: "Índice das maiores companhias não financeiras listadas na Nasdaq." },
  { symbol: "500", tone: "sp500", name: "S&P 500", value: "6.210 pts", change: "+0,44%", direction: "up", note: "Índice amplo das maiores empresas dos Estados Unidos." },
  { symbol: "₿", tone: "bitcoin", name: "Bitcoin", value: "US$ 118.000", change: "+1,24%", direction: "up", note: "Cotação demonstrativa do Bitcoin em dólares americanos." },
  { symbol: "$", tone: "dollar", name: "Dólar / Real", value: "R$ 5,42", change: "+0,18%", direction: "up", note: "Cotação demonstrativa do dólar comercial em reais." },
  { symbol: "€", tone: "euro", name: "Euro / Real", value: "R$ 6,31", change: "-0,12%", direction: "down", note: "Cotação demonstrativa do euro em reais." }
];

const quoteNumber = new Intl.NumberFormat("pt-BR", { maximumFractionDigits: 2 });
const quoteMoneyBRL = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL", maximumFractionDigits: 4 });
const quoteMoneyUSD = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "USD", maximumFractionDigits: 2 });

function formatQuoteValue(item) {
  if (item.unit === "BRL") return quoteMoneyBRL.format(item.value);
  if (item.unit === "USD") return quoteMoneyUSD.format(item.value);
  if (item.unit === "PERCENT") return `${quoteNumber.format(item.value)}%`;
  return `${quoteNumber.format(item.value)} pts`;
}

function formatQuoteChange(value) {
  if (!Number.isFinite(value)) return "—";
  return `${value < 0 ? "▼" : "▲"} ${value >= 0 ? "+" : ""}${quoteNumber.format(value)}%`;
}

const socialIcon = (path) => `<svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true"><path fill="currentColor" d="${path}"/></svg>`;

/* Dados estruturados (schema.org) da organização, presentes em todas as páginas
   que ainda não têm um bloco próprio (ex.: index.html e mentorias.html já embutem o seu). */
(function organizationSchema() {
  if (document.querySelector('script[type="application/ld+json"]')) return;
  const script = document.createElement("script");
  script.type = "application/ld+json";
  script.textContent = JSON.stringify({
    "@context": "https://schema.org",
    "@type": "Organization",
    name: "Global Invest Brasil",
    url: "https://globalinvestbr.com/",
    logo: "https://globalinvestbr.com/assets/images/logo-globalinvestbr-circular.png",
    email: "contato@globalinvestbrasil.com",
    address: { "@type": "PostalAddress", addressLocality: "Porto Alegre", addressRegion: "RS", addressCountry: "BR" },
    sameAs: [
      "https://www.youtube.com/@globalinvestjd",
      "https://www.instagram.com/jorgedadalt/",
      "https://www.facebook.com/globalinvestjd/",
      "https://www.linkedin.com/in/jorge-dadalt-51085244/",
      "https://www.tiktok.com/@jorge.dadalt?lang=pt-BR",
    ],
  });
  document.head.appendChild(script);
})();

/* Perfis públicos oficiais da Global Invest Brasil e de Jorge Dadalt. */
const socialNetworks = [
  { name: "YouTube", icon: socialIcon("M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.3 3.6-6.3 3.6Z"), url: "https://www.youtube.com/@globalinvestjd" },
  { name: "Instagram", icon: socialIcon("M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.9.3 2.6.7.7.4 1.2.9 1.6 1.6.4.7.6 1.4.7 2.6.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.9-.7 2.6a5.2 5.2 0 0 1-1.6 1.6c-.7.4-1.4.6-2.6.7-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1A7.1 7.1 0 0 1 4.1 21a5.2 5.2 0 0 1-1.6-1.6c-.4-.7-.6-1.4-.7-2.6-.1-1.3-.1-1.7-.1-4.9s0-3.6.1-4.9c.1-1.2.3-1.9.7-2.6A5.2 5.2 0 0 1 4.1 3c.7-.4 1.4-.6 2.6-.7C8 2.2 8.4 2.2 12 2.2Zm0 2.1c-3.1 0-3.5 0-4.8.1-1.1.1-1.6.2-2 .4-.5.2-.9.5-1.2.8-.4.4-.6.7-.8 1.2-.2.4-.4 1-.4 2-.1 1.2-.1 1.6-.1 4.8s0 3.5.1 4.8c.1 1.1.2 1.6.4 2 .2.5.5.9.8 1.2.4.4.7.6 1.2.8.4.2 1 .4 2 .4 1.2.1 1.6.1 4.8.1s3.5 0 4.8-.1c1.1-.1 1.6-.2 2-.4.5-.2.9-.5 1.2-.8.4-.4.6-.7.8-1.2.2-.4.4-1 .4-2 .1-1.2.1-1.6.1-4.8s0-3.5-.1-4.8c-.1-1.1-.2-1.6-.4-2-.2-.5-.5-.9-.8-1.2-.4-.4-.7-.6-1.2-.8-.4-.2-1-.4-2-.4-1.2-.1-1.6-.1-4.8-.1Zm0 3.2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 8.9a3.4 3.4 0 1 0 0-6.8 3.4 3.4 0 0 0 0 6.8Zm7.1-9.1a1.3 1.3 0 1 1-2.6 0 1.3 1.3 0 0 1 2.6 0Z"), url: "https://www.instagram.com/jorgedadalt/" },
  { name: "Facebook", icon: socialIcon("M14 8.5V6.6c0-.8.5-1 1-1h2.8V1.3L14.1 1C10.4 1 9 3.2 9 6.2v2.3H6v4.8h3V23h5v-9.7h3.4l.6-4.8H14Z"), url: "https://www.facebook.com/globalinvestjd/" },
  { name: "LinkedIn", icon: socialIcon("M5.4 7.8H1V22h4.4V7.8ZM3.2 1A2.6 2.6 0 1 0 3.2 6.2 2.6 2.6 0 0 0 3.2 1ZM22 13.9c0-4.3-2.3-6.3-5.4-6.3a4.7 4.7 0 0 0-4.2 2.3h-.1V7.8H8.1V22h4.4v-7c0-1.8.3-3.6 2.6-3.6 2.2 0 2.3 2.1 2.3 3.7V22H22v-8.1Z"), url: "https://www.linkedin.com/in/jorge-dadalt-51085244/" },
  { name: "TikTok", icon: socialIcon("M19.6 5.4A5.4 5.4 0 0 1 16.4 4V15a7 7 0 1 1-6-6.9v4a3.1 3.1 0 1 0 2 2.9V1h4a5.4 5.4 0 0 0 3.2 4.4Z"), url: "https://www.tiktok.com/@jorge.dadalt?lang=pt-BR" }
];

/* Perfil oficial no X (Twitter) ainda não definido pelo parceiro: ícone exibido como "em breve", sem link quebrado. */
const socialNetworksComingSoon = [
  { name: "X", icon: socialIcon("M18.9 2.4h3.4l-7.4 8.5 8.7 11.5h-6.8l-5.3-7-6.1 7H1.9l7.9-9.1L1.4 2.4h7l4.8 6.4 5.7-6.4Zm-1.2 18h1.9L7.5 4.3H5.4l12.3 16.1Z") }
];

const canonicalMenu = [
  { label: "Início", href: "/index.html" },
  { label: "Produtos", href: "/produtos.html", submenu: [
    { label: "Livros", href: "/produtos.html?categoria=livros" },
    { label: "E-books", href: "/produtos.html?categoria=ebooks" },
    { label: "Cursos e palestras", href: "/produtos.html?categoria=cursos" },
    { label: "Mentorias", href: "/produtos.html?categoria=mentorias" },
    { label: "Sites e e-commerces", href: "/produtos.html?categoria=sites-ecommerce" }
  ] },
  { label: "Artigos Técnicos", href: "/publicacoes.html" },
  { label: "Proteja Seu Negócio", href: "/seu-negocio.html" },
  { label: "Blog", href: "/artigos.html" },
  { label: "Contato", href: "/contato.html" }
];

function normalizeMainMenu() {
  const current = location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll("[data-nav-links]").forEach((menu) => {
    menu.innerHTML = canonicalMenu.map((item) => {
      const file = item.href.split("/").pop();
      const inProductArea = current === "produtos.html" || location.pathname.includes("/produto/");
      const active = item.label === "Produtos" ? inProductArea : file === current;
      if (!item.submenu) return `<li><a href="${item.href}"${active ? ' aria-current="page"' : ""}>${item.label}</a></li>`;
      const submenu = item.submenu.map((child) => `<li><a href="${child.href}">${child.label}</a></li>`).join("");
      return `<li class="nav-dropdown"><a href="${item.href}"${active ? ' aria-current="page"' : ""}>${item.label}<span aria-hidden="true">⌄</span></a><ul class="submenu" aria-label="Categorias de produtos">${submenu}</ul></li>`;
    }).join("");
  });
}
normalizeMainMenu();

/* Categoria de produto (nome do banco) -> slug usado no filtro do catálogo. */
const productCategorySlug = (name) => ({
  "e-books": "ebooks",
  "livros": "livros",
  "cursos e palestras": "cursos",
  "mentorias": "mentorias",
  "sites e e-commerces": "sites-ecommerce",
  "sites e e-commerce": "sites-ecommerce"
}[String(name || "").trim().toLowerCase()] || publicationCategorySlug(name));

/* Catálogo de produtos: os submenus levam diretamente à categoria escolhida. */
function applyProductCategoryFilter() {
  if (document.body.dataset.productCatalog !== "true") return;
  const category = new URLSearchParams(location.search).get("categoria");
  const labels = {
    livros: "Livros",
    ebooks: "E-books",
    cursos: "Cursos e palestras",
    "sites-ecommerce": "Sites e e-commerce",
    mentorias: "Mentorias"
  };
  if (!(category && labels[category])) return;
  document.querySelectorAll("[data-product-category]").forEach((item) => {
    item.hidden = item.dataset.productCategory !== category;
  });
  document.querySelectorAll("[data-product-category-group]").forEach((item) => {
    item.hidden = item.dataset.productCategoryGroup !== category;
  });
  document.querySelectorAll(".service-catalog, .product-catalog").forEach((section) => {
    const hasVisibleProduct = [...section.querySelectorAll("[data-product-category]")]
      .some((item) => !item.hidden);
    section.hidden = !hasVisibleProduct;
  });
  const panel = document.querySelector("#catalog-filter");
  const summary = document.querySelector("[data-product-filter-summary]");
  if (panel && summary) {
    summary.textContent = `Produtos em destaque: ${labels[category]}`;
    panel.hidden = false;
  }
}
applyProductCategoryFilter();

const commerceAdsMarkup = () => `
    <article class="commerce-ad international-ad">
      <span class="commerce-ad-icon" aria-hidden="true">◎</span>
      <div class="commerce-ad-copy">
        <small><span>PUBLICIDADE GLOBAL INVEST BRASIL</span> MERCADO INTERNACIONAL</small>
        <strong>Leve seu e-commerce para novos mercados</strong>
        <p>Estratégia, tecnologia e gestão para preparar sua operação e vender além das fronteiras.</p>
      </div>
      <a href="/contato.html?assunto=ecommerce-internacional">Planejar expansão <span aria-hidden="true">→</span></a>
    </article>
    <article class="commerce-ad national-ad">
      <span class="commerce-ad-icon" aria-hidden="true">↗</span>
      <div class="commerce-ad-copy">
        <small><span>PUBLICIDADE GLOBAL INVEST BRASIL</span> MERCADO NACIONAL</small>
        <strong>Estruture seu e-commerce para vender mais no Brasil</strong>
        <p>Organize processos, canais e indicadores para crescer com controle e consistência.</p>
      </div>
      <a href="/contato.html?assunto=ecommerce-nacional">Fortalecer operação <span aria-hidden="true">→</span></a>
    </article>`;

/* Publicidade institucional exibida enquanto os blocos do Google AdSense não estão ativos. */
document.querySelectorAll(".ad-slot").forEach((slot) => {
  slot.classList.add("commerce-ad-slot");
  slot.setAttribute("aria-label", "Soluções Global Invest Brasil para e-commerce");
  slot.innerHTML = commerceAdsMarkup();
});

/* Publicidade aparece apenas nas seções autorizadas do portal. */
if (location.pathname.endsWith("/publicacoes.html") || location.pathname.endsWith("/artigos.html")) {
  const main = document.querySelector("main");
  if (main && !main.querySelector(".ad-slot")) {
    const slot = document.createElement("section");
    slot.className = "container ad-slot commerce-ad-slot";
    slot.setAttribute("aria-label", "Publicidade Global Invest Brasil");
    slot.innerHTML = commerceAdsMarkup();
    main.append(slot);
  }
}

const contactSubject = document.querySelector("#assunto");
if (contactSubject) {
  const offerings = [
    ["", "Selecione o assunto"], ["atendimento-geral", "Atendimento geral"],
    ["livro", "Livro: Trabalhe Pouco, Ganhe Muito"], ["palestras", "Palestras Global Invest Brasil"],
    ["curso", "Curso de Gestão de Negócios Online"], ["ebook-ideia-ao-lucro", "E-book: Método Da Ideia ao Lucro"],
    ["mentoria-primeiro-negocio", "Mentoria: Meu Primeiro Negócio"], ["mentoria-organizando", "Mentoria: Organizando Meu Negócio"],
    ["mentoria-escalando", "Mentoria: Escalando Meu Negócio"], ["blindagem-patrimonial", "Mentoria: Blindagem Patrimonial"],
    ["publicacoes", "Publicações e conteúdo"], ["proteja-negocio", "Proteja Seu Negócio"],
    ["ecommerce-nacional", "Estruturação de e-commerce nacional"], ["ecommerce-internacional", "Planejamento de e-commerce internacional"],
    ["site-institucional", "Site institucional Global Invest Brasil"], ["construcao-ecommerce", "Construção de e-commerce Global Invest Brasil"],
    ["app-dedicado", "App dedicado Global Invest Brasil"],
    ["parcerias-publicidade", "Parcerias e publicidade"]
  ];
  const select = document.createElement("select");
  [...contactSubject.attributes].forEach((attribute) => select.setAttribute(attribute.name, attribute.value));
  select.removeAttribute("type");
  select.removeAttribute("maxlength");
  select.removeAttribute("placeholder");
  select.innerHTML = offerings.map(([value, label]) => `<option value="${value}">${label}</option>`).join("");
  contactSubject.replaceWith(select);
  const subjectKey = new URLSearchParams(location.search).get("assunto");
  if (subjectKey && offerings.some(([value]) => value === subjectKey)) select.value = subjectKey;
}

function formatArticleHeader(card) {
  const cover = card.querySelector(".article-cover");
  const meta = card.querySelector(".article-meta");
  if (!cover || !meta) return;
  const time = meta.querySelector("time");
  if (time) meta.textContent = time.textContent;
  if (meta.parentElement !== cover) cover.appendChild(meta);
}
document.querySelectorAll(".article-card").forEach(formatArticleHeader);

const publicationCategorySlug = (value) => String(value || "outros")
  .normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase()
  .replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");

// As publicações são exibidas da mais recente para a mais antiga. A numeração,
// porém, segue a linha do tempo: a matéria mais antiga recebe Nº 001.
const publicationNumber = (position, total) => `Nº ${String(total - position).padStart(3, "0")}`;

function ensurePublicationCategory(category) {
  const nav = document.querySelector(".article-category-nav");
  if (!nav || !category) return;
  const slug = publicationCategorySlug(category);
  if (nav.querySelector(`[data-article-filter="${slug}"]`)) return;
  const button = document.createElement("button");
  button.className = "article-filter";
  button.type = "button";
  button.setAttribute("role", "tab");
  button.setAttribute("aria-selected", "false");
  button.dataset.articleFilter = slug;
  button.textContent = category;
  nav.appendChild(button);
}

const initialPublicationCards = [...document.querySelectorAll(".article-card")];
initialPublicationCards.forEach((card, index) => {
  const cover = card.querySelector(".article-cover");
  if (!cover || cover.querySelector(".article-number")) return;
  const number = document.createElement("span");
  number.className = "article-number";
  number.textContent = publicationNumber(index, initialPublicationCards.length);
  cover.prepend(number);
});

fetch("/api/content", { cache: "no-store" }).then((response) => response.ok ? response.json() : null).then((content) => {
  if (!content) return;
  normalizeMainMenu();
  if (document.body.dataset.contentServerRendered === "true") return;
  const home = content.home || {};
  const eyebrow = document.querySelector(".home-page .eyebrow");
  const title = document.querySelector(".home-page h1");
  const description = document.querySelector(".home-page .hero-copy");
  if (eyebrow && home.eyebrow) eyebrow.textContent = home.eyebrow;
  if (title && home.title) {
    const fullTitle = String(home.title);
    const highlighted = fullTitle.match(/decisões melhores\.?$/i);
    title.textContent = "";
    if (highlighted && highlighted.index !== undefined) {
      title.append(document.createTextNode(fullTitle.slice(0, highlighted.index)));
      const accent = document.createElement("span");
      accent.className = "accent";
      accent.textContent = highlighted[0];
      title.append(accent);
    } else {
      title.textContent = fullTitle;
    }
  }
  if (description && home.description) description.textContent = home.description;

  const pageByFile = { "index.html": "inicio", "produtos.html": "produtos", "mentorias.html": "mentorias", "seu-negocio.html": "negocio", "artigos.html": "blog", "publicacoes.html": "publicacoes", "contato.html": "contato" };
  const currentFile = location.pathname.split("/").pop() || "index.html";
  // Mentorias ainda é página fixa; produtos, publicações e blog vêm do banco.
  const pageKey = currentFile === "mentorias.html" ? null : pageByFile[currentFile];
  const pageItems = pageKey && content.pages?.[pageKey] ? content.pages[pageKey] : [];

  // Catálogo: atualiza os cards curados de produtos.html com os dados do banco
  // (título, resumo, categoria, imagem) e aponta o botão para /produto/{slug}.
  // Cards sem produto correspondente no banco ficam como estão.
  if (pageKey === "produtos" && pageItems.length) {
    const slugToId = {
      "produto-livro": "livro", "produto-livro-impresso": "livro-impresso", "produto-palestras": "palestras", "produto-curso": "curso-gestao-online",
      "produto-ideia-ao-lucro": "ebook-ideia-ao-lucro", "produto-ideia-ao-lucro-impresso": "ideia-ao-lucro-impresso",
      "produto-o-seu-maior-patrimonio": "o-seu-maior-patrimonio", "produto-o-seu-maior-patrimonio-impresso": "o-seu-maior-patrimonio-impresso",
      "produto-primeiro-negocio": "meu-primeiro-negocio", "produto-organizando": "organizando-meu-negocio",
      "produto-escalando": "escalando-meu-negocio", "produto-blindagem-patrimonial": "blindagem-patrimonial",
      "produto-site": "fabricamos-seu-site", "produto-ecommerce": "construimos-ecommerce", "produto-app-dedicado": "criamos-app-dedicado"
    };
    const bySlug = new Map(pageItems.map((it) => [it.slug, it]));
    Object.entries(slugToId).forEach(([slug, elId]) => {
      const el = document.getElementById(elId);
      const item = bySlug.get(slug);
      if (!el || !item) return;
      const h2 = el.querySelector(".product-copy h2, h2");
      const para = el.querySelector(".product-copy > p, p");
      const img = el.querySelector("img");
      const cta = el.querySelector("a.btn, a.managed-link");
      if (h2 && item.title) h2.textContent = item.title;
      if (para && item.summary) para.textContent = item.summary;
      if (item.category) el.dataset.productCategory = productCategorySlug(item.category);
      if (img && item.image) img.setAttribute("src", item.image);
      if (cta) cta.setAttribute("href", slug === "produto-ideia-ao-lucro" ? "/da-ideia-ao-lucro/" : `/produto/${slug}`);
    });
    // as categorias agora vêm do banco; reaplica o filtro ?categoria= do catálogo
    applyProductCategoryFilter();
    return;
  }

  const staticIds = {
    produtos: {
      "produto-livro": "#livro", "produto-palestras": "#palestras", "produto-curso": "#curso-gestao-online",
      "produto-primeiro-negocio": "#meu-primeiro-negocio", "produto-organizando": "#organizando-meu-negocio", "produto-escalando": "#escalando-meu-negocio"
    },
    mentorias: { "mentoria-primeiro-negocio": "#primeiro-negocio", "mentoria-organizando": "#organizando", "mentoria-escalando": "#escalando", "mentoria-blindagem-patrimonial": "#blindagem-patrimonial" },
    publicacoes: {
      "publicacao-cinco-numeros": "#publicacao-cinco-numeros", "publicacao-negocio-online": "#publicacao-negocio-online",
      "publicacao-dependencia-fundador": "#publicacao-dependencia-fundador", "publicacao-risco-retorno": "#publicacao-risco-retorno",
      "publicacao-automatizar": "#publicacao-automatizar", "publicacao-reserva": "#publicacao-reserva",
      "publicacao-experiencia": "#publicacao-experiencia", "publicacao-crescer": "#publicacao-crescer"
    }
  };
  const known = staticIds[pageKey] || {};
  const records = new Map(pageItems.map(item => [item.id, item]));
  const productLandingPages = {
    "produto-livro": "/produto/produto-livro.html",
    "produto-palestras": "/produto/produto-palestras.html",
    "produto-curso": "/produto/produto-curso.html",
    "produto-primeiro-negocio": "/produto/produto-primeiro-negocio.html",
    "produto-organizando": "/produto/produto-organizando.html",
    "produto-escalando": "/produto/produto-escalando.html",
    "produto-blindagem-patrimonial": "/produto/produto-blindagem-patrimonial.html"
  };
  const mentoringLandingPages = {
    "mentoria-primeiro-negocio": "/produto/produto-primeiro-negocio.html",
    "mentoria-organizando": "/produto/produto-organizando.html",
    "mentoria-escalando": "/produto/produto-escalando.html",
    "mentoria-blindagem-patrimonial": "/produto/produto-blindagem-patrimonial.html"
  };
  Object.entries(known).forEach(([id, selector]) => {
    const element = document.querySelector(selector);
    const item = records.get(id);
    if (!element) return;
    if (!item || !item.visible) { element.remove(); return; }
    const heading = element.querySelector("h2");
    const paragraph = pageKey === "mentorias" ? element.querySelector(".detail-side p") : element.querySelector(".product-copy > p, .article-body > p");
    const category = element.querySelector(".kicker, .article-cover-label");
    const link = element.querySelector("a.btn, .article-status");
    const image = element.querySelector("img");
    if (heading) heading.textContent = item.title;
    if (paragraph) paragraph.textContent = item.summary;
    if (category && item.category) category.textContent = item.category;
    if (link) {
      link.textContent = pageKey === "produtos" ? "Conheça Agora" : (item.linkLabel || "Saiba mais");
      if (link.tagName === "A") {
        const destination = pageKey === "produtos"
          ? productLandingPages[item.id]
          : (pageKey === "mentorias" ? mentoringLandingPages[item.id] : item.link);
        link.setAttribute("href", destination || item.link || "#");
      }
    }
    if (image && item.image) image.setAttribute("src", item.image);
    if (pageKey === "publicacoes" || pageKey === "blog") {
      const slug = publicationCategorySlug(item.category);
      ensurePublicationCategory(item.category);
      element.dataset.articleCategory = slug;
      const meta = element.querySelector(".article-meta");
      if (meta && item.date) meta.textContent = new Date(`${item.date}T12:00:00`).toLocaleDateString("pt-BR", { day: "numeric", month: "short", year: "numeric" });
      const currentAction = element.querySelector(".article-status");
      if (currentAction) {
        const detailLink = document.createElement("a");
        detailLink.className = "article-status";
        detailLink.href = item.link || `/ler.php?slug=${encodeURIComponent(item.id)}`;
        detailLink.textContent = item.linkLabel && item.linkLabel !== "Em preparação" ? item.linkLabel : "Saiba mais";
        currentAction.replaceWith(detailLink);
      }
    }
  });

  // Publicações e Blog: a lista vem 100% do banco (/api/content). Se o banco
  // devolver itens, o grid é substituído; senão mantém o conteúdo de exemplo.
  if ((pageKey === "publicacoes" || pageKey === "blog") && pageItems.length) {
    const grid = document.querySelector("[data-article-grid]");
    if (grid) {
      const esc = (v) => String(v ?? "").replace(/[&<>"]/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
      const safe = (v) => /^(https?:|mailto:|tel:|\/|#)/i.test(String(v || "")) ? String(v) : "#";
      const total = pageItems.length;
      grid.innerHTML = pageItems.map((item, index) => {
        const catName = item.category || (pageKey === "blog" ? "Blog" : "Publicação");
        const cslug = publicationCategorySlug(catName);
        ensurePublicationCategory(catName);
        const day = String(item.published_at || item.date || "").slice(0, 10);
        const date = day ? new Date(`${day}T12:00:00`).toLocaleDateString("pt-BR", { day: "numeric", month: "short", year: "numeric" }) : "";
        const img = item.image || articleImages[cslug] || articleImages.gestao;
        return `<article class="article-card article-card--with-image" data-article-category="${esc(cslug)}"><div class="article-cover article-cover--image"><img src="${esc(img)}" alt="${esc(catName)}: ${esc(item.title)}" loading="lazy"></div><div class="article-body"><span class="article-number">${publicationNumber(index, total)}</span><span class="article-meta">${esc(catName)} · ${esc(date)}</span><h2>${esc(item.title)}</h2><p>${esc(item.summary || "")}</p><a class="article-status" href="${esc(safe(item.link))}">${pageKey === "blog" ? "Ler artigo" : "Saiba mais"}</a></div></article>`;
      }).join("");
      const countEl = document.querySelector("[data-article-count]");
      if (countEl) countEl.textContent = String(total);
      const emptyEl = document.querySelector("[data-article-empty]");
      if (emptyEl) emptyEl.hidden = true;
      document.dispatchEvent(new CustomEvent("globalinvest:publications-updated"));
    }
    return;
  }

  const managedItems = pageItems.filter(item => item.visible && !known[item.id]);
  if (managedItems.length) {
    const escapeHtml = (value) => String(value).replace(/[&<>"]/g, character => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[character]));
    const safeLink = (value) => /^(https?:|mailto:|tel:|\/|#)/i.test(String(value)) ? String(value) : "#";
    if (pageKey === "publicacoes" || pageKey === "blog") {
      const grid = document.querySelector("[data-article-grid]");
      if (grid) {
        grid.insertAdjacentHTML("beforeend", managedItems.map(item => {
          const slug = publicationCategorySlug(item.category);
          ensurePublicationCategory(item.category);
          const date = item.date ? new Date(`${item.date}T12:00:00`).toLocaleDateString("pt-BR", { day: "numeric", month: "short", year: "numeric" }) : "";
          const detailHref = safeLink(item.link || `/ler.php?slug=${encodeURIComponent(item.id)}`);
          return `<article class="article-card" data-article-category="${escapeHtml(slug)}"><div class="article-cover"><span class="article-cover-label">${escapeHtml(item.category || "Publicação")}</span><span class="article-meta">${escapeHtml(date)}</span></div><div class="article-body"><h2>${escapeHtml(item.title)}</h2><p>${escapeHtml(item.summary)}</p><a class="article-status" href="${escapeHtml(detailHref)}">${escapeHtml(item.linkLabel && item.linkLabel !== "Em preparação" ? item.linkLabel : "Saiba mais")}</a></div></article>`;
        }).join(""));
        const publicationCards = [...grid.querySelectorAll(".article-card")];
        publicationCards.forEach((card, index) => {
          if (card.querySelector(".article-number")) return;
          const number = document.createElement("span");
          number.className = "article-number";
          number.textContent = publicationNumber(index, publicationCards.length);
          card.querySelector(".article-cover")?.prepend(number);
        });
        document.dispatchEvent(new CustomEvent("globalinvest:publications-updated"));
      }
      return;
    }
    const section = document.createElement("section");
    section.className = "section managed-section";
    section.setAttribute("aria-label", "Conteúdos atualizados");
    section.innerHTML = `<div class="container"><div class="managed-grid">${managedItems.map(item => `<article class="managed-card"><span class="managed-kicker">${escapeHtml(item.category || "GLOBAL INVEST BRASIL")}</span>${item.image ? `<img class="managed-image" src="${escapeHtml(safeLink(item.image))}" alt="">` : ""}<h2>${escapeHtml(item.title)}</h2><p>${escapeHtml(item.summary)}</p>${pageKey === "produtos" ? `<a class="managed-link" href="${item.category === "Palestras" ? "/contato.html?assunto=palestras" : `/produto/${encodeURIComponent(item.id)}`}">Conheça Agora <span>→</span></a>` : item.link && item.linkLabel ? `<a class="managed-link" href="${escapeHtml(safeLink(item.link))}">${escapeHtml(item.linkLabel)} <span>→</span></a>` : ""}</article>`).join("")}</div></div>`;
    const main = document.querySelector("main");
    const finalAd = main?.querySelector(":scope > .ad-slot:last-of-type");
    if (main) main.insertBefore(section, finalAd || null);
  }
}).catch(() => {});

const isFullPublication = location.pathname.includes("/publicacao/") || /\/ler\.php$/.test(location.pathname);
if (isFullPublication) {
  const articleMain = document.querySelector("main");
  if (articleMain && !articleMain.querySelector(".article-end-ads")) {
    const ads = document.createElement("section");
    ads.className = "article-end-ads container";
    ads.setAttribute("aria-label", "Publicidade");
    ads.innerHTML = `<p class="article-ad-label">PUBLICIDADE</p><div class="ad-slot commerce-ad-slot" aria-label="Soluções Global Invest Brasil para e-commerce">${commerceAdsMarkup()}</div>`;
    articleMain.appendChild(ads);
  }
}

document.querySelectorAll(".footer-social-grid a").forEach((link) => {
  const label = link.textContent.trim().toLowerCase();
  const network = socialNetworks.find((item) => label.includes(item.name.toLowerCase()));
  if (!network) {
    link.remove();
    return;
  }
  const icon = link.querySelector(".social-brand-icon");
  link.href = network.url;
  link.target = "_blank";
  link.rel = "noopener noreferrer";
  link.removeAttribute("data-social-placeholder");
  link.setAttribute("aria-label", `Abrir ${network.name} da Global Invest Brasil`);
  if (icon) icon.innerHTML = network.icon;
});

/* Rodapé único: aplicado igualmente a páginas institucionais, matérias e landing pages. */
function renderUnifiedFooters() {
  const current = location.pathname.split("/").pop() || "index.html";
  const menuMarkup = canonicalMenu.map((item) => {
    const file = item.href.split("/").pop();
    return `<a href="${item.href}"${file === current ? ' aria-current="page"' : ""}>${item.label}</a>`;
  }).join("");
  const socialMarkup = socialNetworks.map((network) => `
    <a href="${network.url}" target="_blank" rel="noopener noreferrer" aria-label="Abrir ${network.name} da Global Invest Brasil">
      <span class="social-brand-icon" aria-hidden="true">${network.icon}</span><span>${network.name}</span>
    </a>`).join("") + socialNetworksComingSoon.map((network) => `
    <span class="social-coming-soon" aria-label="Perfil no ${network.name} em breve" title="Em breve">
      <span class="social-brand-icon" aria-hidden="true">${network.icon}</span><span>${network.name} <em>(em breve)</em></span>
    </span>`).join("");

  document.querySelectorAll(".site-footer").forEach((footer) => {
    footer.classList.add("site-footer--unified");
    footer.innerHTML = `
      <div class="container unified-footer">
        <div class="unified-footer-grid">
          <section class="unified-footer-section">
            <p class="footer-block-title">Navegação</p>
            <nav class="footer-navigation footer-navigation--unified" aria-label="Navegação do rodapé">${menuMarkup}</nav>
          </section>
          <section class="unified-footer-section">
            <p class="footer-block-title">Redes sociais</p>
            <div class="footer-social-grid footer-social-grid--unified">${socialMarkup}</div>
          </section>
          <section class="unified-footer-company" aria-label="Global Invest Brasil">
            <p class="footer-block-title">Global Invest Brasil</p>
            <h2>Educação e fomento a negócios</h2>
            <p>Conteúdo técnico, cursos, mentorias e publicações para decisões mais estruturadas.</p>
            <a class="footer-email" href="mailto:contato@globalinvestbrasil.com"><span class="footer-email-label">E-mail:</span><span class="footer-email-address">contato@globalinvestbrasil.com</span></a>
            <form class="footer-newsletter" data-newsletter-form>
              <label for="newsletter-email">Receba novidades e conteúdos por e-mail</label>
              <div class="honeypot" aria-hidden="true"><label>Não preencha este campo<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
              <div class="footer-newsletter-row">
                <input id="newsletter-email" name="email" type="email" placeholder="seu@email.com" required autocomplete="email">
                <button type="submit">Inscrever</button>
              </div>
              <p class="footer-newsletter-status" data-newsletter-status aria-live="polite"></p>
            </form>
          </section>
        </div>
        <div class="footer-policy-grid footer-policy-grid--unified">
          <a class="footer-privacy-link" href="/privacidade.html"><span class="footer-privacy-icon" aria-hidden="true">⌁</span><span class="footer-privacy-copy"><strong>Política de Privacidade</strong><small>Proteção de dados pessoais, cookies e direitos dos titulares.</small></span><span class="footer-privacy-arrow" aria-hidden="true">→</span></a>
          <a class="footer-privacy-link footer-uses-link" href="/politicas-de-uso.html"><span class="footer-privacy-icon" aria-hidden="true">§</span><span class="footer-privacy-copy"><strong>Políticas de Uso</strong><small>Regras, responsabilidades e condições para utilizar o portal.</small></span><span class="footer-privacy-arrow" aria-hidden="true">→</span></a>
        </div>
        <p class="footer-legal-line">© ${new Date().getFullYear()} Global Invest Brasil. Todos os direitos reservados.</p>
      </div>`;
  });
}

renderUnifiedFooters();

document.addEventListener("click", (event) => {
  const placeholder = event.target.closest('[data-social-placeholder="true"]');
  if (placeholder) event.preventDefault();
});

const marketDock = document.createElement("div");
marketDock.className = "quotes-dock";
marketDock.innerHTML = `
  <button class="quotes-tab" type="button" aria-expanded="false" aria-controls="quotes-panel">
    <span class="quotes-tab-icon" aria-hidden="true">↗</span>
    <span>Cotações</span>
  </button>
  <div class="quotes-overlay" data-quotes-close></div>
  <aside class="quotes-panel" id="quotes-panel" aria-hidden="true" aria-label="Cotações em acompanhamento">
    <div class="quotes-panel-head">
      <div><span class="quotes-kicker">Mercados agora</span><h2>Cotações em acompanhamento</h2></div>
      <button class="quotes-close" type="button" data-quotes-close aria-label="Fechar cotações">×</button>
    </div>
    <div class="quotes-table-head"><span>Ativo</span><span>Valor</span><span>Variação</span></div>
    <div class="quotes-list" data-quotes-list>
      ${marketData.map((item, index) => `
        <button class="quote-row" type="button" data-quote-index="${index}" aria-expanded="false">
          <span class="quote-identity"><span class="quote-symbol quote-symbol-${item.tone}" aria-hidden="true">${item.symbol}</span><strong>${item.name}</strong></span>
          <span class="quote-value">${item.value}</span>
          <span class="quote-change ${item.direction === "down" ? "down" : ""}">${item.direction === "down" ? "▼" : "▲"} ${item.change}</span>
          <span class="quote-detail">${item.note}</span>
        </button>`).join("")}
    </div>
    <div class="quotes-status" data-quotes-status aria-live="polite">Carregando dados das fontes...</div>
    <p class="quotes-disclaimer">Cotações informativas, sujeitas a atraso e revisão pela fonte. Não constituem recomendação de investimento nem devem fundamentar isoladamente decisões financeiras.</p>
  </aside>`;
document.body.appendChild(marketDock);

const quotesTab = marketDock.querySelector(".quotes-tab");
const quotesPanel = marketDock.querySelector(".quotes-panel");
const setQuotesOpen = (open) => {
  marketDock.classList.toggle("open", open);
  document.body.classList.toggle("quotes-open", open);
  quotesTab.setAttribute("aria-expanded", String(open));
  quotesPanel.setAttribute("aria-hidden", String(!open));
  if (open) marketDock.querySelector(".quotes-close").focus();
};
quotesTab.addEventListener("click", () => setQuotesOpen(!marketDock.classList.contains("open")));
marketDock.querySelectorAll("[data-quotes-close]").forEach((node) => node.addEventListener("click", () => setQuotesOpen(false)));
marketDock.querySelectorAll(".quote-row").forEach((row) => row.addEventListener("click", () => {
  const expanded = row.getAttribute("aria-expanded") === "true";
  marketDock.querySelectorAll(".quote-row").forEach((item) => item.setAttribute("aria-expanded", "false"));
  row.setAttribute("aria-expanded", String(!expanded));
}));
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape" && marketDock.classList.contains("open")) setQuotesOpen(false);
});

const quotesList = marketDock.querySelector("[data-quotes-list]");
const quotesStatus = marketDock.querySelector("[data-quotes-status]");

function activateQuoteRows() {
  marketDock.querySelectorAll(".quote-row").forEach((row) => row.addEventListener("click", () => {
    const expanded = row.getAttribute("aria-expanded") === "true";
    marketDock.querySelectorAll(".quote-row").forEach((item) => item.setAttribute("aria-expanded", "false"));
    row.setAttribute("aria-expanded", String(!expanded));
  }));
}

async function updateMarketQuotes() {
  try {
    const response = await fetch("/api/cotacoes", { headers: { accept: "application/json" } });
    if (!response.ok) throw new Error("As fontes não responderam agora");
    const data = await response.json();
    if (!Array.isArray(data.quotes) || !data.quotes.length) throw new Error("Nenhuma cotação disponível");
    quotesList.innerHTML = data.quotes.map((item, index) => {
      const negative = Number.isFinite(item.change) && item.change < 0;
      const observedDate = item.observedAt ? new Date(item.observedAt) : null;
      const observed = observedDate && Number.isFinite(observedDate.getTime()) ? observedDate.toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" }) : "horário não informado";
      return `<button class="quote-row" type="button" data-quote-index="${index}" aria-expanded="false">
        <span class="quote-identity"><span class="quote-symbol quote-symbol-${item.tone}" aria-hidden="true">${item.symbol}</span><strong>${item.name}</strong></span>
        <span class="quote-value">${formatQuoteValue(item)}</span>
        <span class="quote-change ${negative ? "down" : ""}">${formatQuoteChange(item.change)}</span>
        <span class="quote-detail"><b>Fonte:</b> <a href="${item.sourceUrl}" target="_blank" rel="noopener noreferrer">${item.source}</a><br>${item.delay}. Observação: ${observed}.${item.stale ? " Último valor válido disponível." : ""}</span>
      </button>`;
    }).join("");
    activateQuoteRows();
    const updated = new Date(data.updatedAt).toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" });
    const partial = data.failures?.length > 0 || data.quotes.some((item) => item.stale);
    quotesStatus.textContent = `${data.cache === "stale" ? "Último conjunto válido" : "Atualizado"}: ${updated}. ${data.quotes.length} indicadores disponíveis. Nova consulta automática em até 5 minutos.`;
    quotesStatus.classList.toggle("is-stale", data.cache === "stale" || partial);
  } catch (error) {
    quotesStatus.textContent = "Fontes temporariamente indisponíveis. Os valores de referência permanecem visíveis até a próxima tentativa.";
    quotesStatus.classList.add("is-stale");
  }
}

updateMarketQuotes();
setInterval(updateMarketQuotes, 5 * 60 * 1000);

const menuButton = document.querySelector("[data-menu-toggle]");
const navLinks = document.querySelector("[data-nav-links]");
menuButton?.addEventListener("click", () => {
  const open = navLinks.classList.toggle("open");
  menuButton.setAttribute("aria-expanded", String(open));
  menuButton.textContent = open ? "×" : "☰";
  document.body.classList.toggle("menu-open", open);
});
navLinks?.addEventListener("click", (event) => {
  if (event.target.closest("a") && navLinks.classList.contains("open")) {
    navLinks.classList.remove("open");
    document.body.classList.remove("menu-open");
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.textContent = "☰";
  }
});

const contactForm = document.querySelector("#contact-form");
contactForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  const status = contactForm.querySelector("[data-form-status]");
  const honeypot = contactForm.querySelector("#website");
  if (honeypot.value) {
    status.textContent = "Não foi possível enviar a mensagem.";
    status.className = "form-status error";
    return;
  }
  if (!contactForm.checkValidity()) {
    contactForm.reportValidity();
    status.textContent = "Revise os campos obrigatórios.";
    status.className = "form-status error";
    return;
  }
  const submitButton = contactForm.querySelector('button[type="submit"]');
  const data = new FormData(contactForm);
  submitButton.disabled = true;
  submitButton.textContent = "Enviando...";
  status.textContent = "Registrando sua mensagem com segurança...";
  status.className = "form-status";
  try {
    const response = await fetch("/api/contacts", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: data.get("nome"),
        email: data.get("email"),
        subject: data.get("assunto"),
        message: data.get("mensagem"),
        consent: data.get("consentimento") === "on",
        website: data.get("website"),
      }),
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(result.error || "Não foi possível enviar a mensagem.");
    contactForm.reset();
    status.textContent = "Mensagem enviada e registrada com sucesso. Nossa equipe retornará pelo e-mail informado.";
    status.className = "form-status success";
  } catch (error) {
    const pendingContacts = JSON.parse(localStorage.getItem("globalinvest:pending-contacts") || "[]");
    pendingContacts.push({
      name: data.get("nome"), email: data.get("email"), subject: data.get("assunto"),
      message: data.get("mensagem"), createdAt: new Date().toISOString(), status: "aguardando-sincronizacao"
    });
    localStorage.setItem("globalinvest:pending-contacts", JSON.stringify(pendingContacts));
    contactForm.reset();
    status.textContent = "Mensagem registrada nesta versão de avaliação. A sincronização com a área administrativa será concluída na hospedagem final.";
    status.className = "form-status success";
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = "Enviar mensagem";
  }
});

/* Captura de e-mail no rodapé (isca digital), independente do formulário de contato. */
document.addEventListener("submit", async (event) => {
  const form = event.target.closest("[data-newsletter-form]");
  if (!form) return;
  event.preventDefault();
  const status = form.querySelector("[data-newsletter-status]");
  const honeypot = form.querySelector('input[name="website"]');
  const button = form.querySelector('button[type="submit"]');
  const email = form.querySelector('input[name="email"]').value.trim();
  if (honeypot && honeypot.value) return;
  if (!email) return;
  button.disabled = true;
  status.textContent = "Enviando...";
  status.className = "footer-newsletter-status";
  try {
    const response = await fetch("/api/newsletter", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, source: location.pathname, website: honeypot ? honeypot.value : "" }),
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(result.error || "Não foi possível concluir a inscrição.");
    form.reset();
    status.textContent = "Inscrição confirmada. Obrigado!";
    status.className = "footer-newsletter-status success";
  } catch (error) {
    status.textContent = error.message || "Não foi possível concluir a inscrição agora.";
    status.className = "footer-newsletter-status error";
  } finally {
    button.disabled = false;
  }
});

const editorialPublications = [
  { category: "gestao", label: "Gestão", date: "12 de ago. de 2026", title: "A disciplina operacional que protege a margem", summary: "Como rituais, custos de não qualidade e gestão de capacidade impedem que o crescimento transforme eficiência em desperdício.", href: "publicacao/gestao-disciplina-operacional.html" },
  { category: "investimentos", label: "Investimentos", date: "12 de ago. de 2026", title: "Uma tese de investimento não é uma previsão", summary: "Cenários, margem de segurança, liquidez e critérios de revisão para avaliar decisões sem confundir convicção com certeza.", href: "publicacao/investimentos-tese-cenarios.html" },
  { category: "negocios-digitais", label: "Negócios digitais", date: "12 de ago. de 2026", title: "Unit economics: o teste antes da escala", summary: "CAC, margem de contribuição, retenção e payback explicados a partir de situações práticas de operação digital.", href: "publicacao/negocios-digitais-unit-economics.html" },
  { category: "tecnologia", label: "Tecnologia", date: "12 de ago. de 2026", title: "Dados confiáveis antes da automação", summary: "Por que a arquitetura de decisões deve preceder ferramentas, integrações e promessas de eficiência automatizada.", href: "publicacao/tecnologia-arquitetura-decisoes.html" },
  { category: "carreira", label: "Carreira", date: "12 de ago. de 2026", title: "Competência que permanece útil em ciclos curtos", summary: "Como construir repertório técnico, julgamento e capital de confiança quando funções e mercados mudam mais rápido.", href: "publicacao/carreira-competencias-ciclos-curtos.html" },
  { category: "reflexoes", label: "Reflexões", date: "12 de ago. de 2026", title: "O custo invisível das decisões adiadas", summary: "Uma análise sobre inércia, reversibilidade e responsabilidade para escolher o momento certo de agir.", href: "publicacao/reflexoes-custo-decisoes-adiadas.html" },
  { category: "gestao", label: "Gestão", date: "11 de ago. de 2026", title: "Gestão que transforma estratégia em execução", summary: "Como governança, processos, indicadores e cadência decisória transformam prioridades em resultados consistentes.", href: "publicacao/gestao-governanca-execucao.html" },
  { category: "investimentos", label: "Investimentos", date: "11 de ago. de 2026", title: "Investir é administrar risco, tempo e liquidez", summary: "Uma abordagem técnica para construir decisões coerentes com objetivos, horizonte, liquidez e capacidade de risco.", href: "publicacao/investimentos-alocacao-risco.html" },
  { category: "negocios-digitais", label: "Negócios digitais", date: "11 de ago. de 2026", title: "Da promessa à operação digital escalável", summary: "Proposta de valor, economia unitária, aquisição, processos e dados para crescer sem perder controle.", href: "publicacao/negocios-digitais-operacao-escalavel.html" },
  { category: "tecnologia", label: "Tecnologia", date: "11 de ago. de 2026", title: "Tecnologia que produz capacidade, não complexidade", summary: "Arquitetura de dados, automação, segurança e governança conectadas ao modelo operacional.", href: "publicacao/tecnologia-dados-automacao.html" },
  { category: "carreira", label: "Carreira", date: "11 de ago. de 2026", title: "Carreira como capital profissional", summary: "Competências, reputação, autonomia e escolhas para construir uma trajetória resiliente.", href: "publicacao/carreira-capital-profissional.html" },
  { category: "reflexoes", label: "Reflexões", date: "11 de ago. de 2026", title: "Decidir bem quando não existe certeza", summary: "Julgamento, responsabilidade e método para escolhas importantes em ambientes de incerteza.", href: "publicacao/reflexoes-decisao-incerteza.html" }
];

const articleImages = {
  gestao: "/assets/images/publicacoes/gestao-editorial.webp",
  investimentos: "/assets/images/publicacoes/investimentos-editorial.webp",
  "negocios-digitais": "/assets/images/publicacoes/negocios-digitais-editorial.webp",
  tecnologia: "/assets/images/publicacoes/tecnologia-editorial.webp",
  carreira: "/assets/images/publicacoes/carreira-editorial.webp",
  reflexoes: "/assets/images/publicacoes/reflexoes-editorial.webp"
};

const blogPosts = [
  { category: "negocios", label: "Negócios", date: "16 de ago. de 2026", title: "Reuniões curtas, decisões melhores", summary: "Um roteiro prático para transformar encontros recorrentes em decisões claras, responsáveis definidos e próximas ações verificáveis.", href: "/artigos.html", image: articleImages.gestao },
  { category: "mercado", label: "Mercado", date: "14 de ago. de 2026", title: "Três perguntas antes de revisar seu preço", summary: "Antes de alterar uma tabela comercial, analise valor percebido, estrutura de custos e capacidade de entrega do negócio.", href: "/artigos.html", image: articleImages.investimentos },
  { category: "rotina", label: "Rotina", date: "10 de ago. de 2026", title: "Como organizar uma semana de trabalho sem perder prioridades", summary: "Uma abordagem simples para equilibrar demandas urgentes, projetos de crescimento e blocos de análise.", href: "/artigos.html", image: articleImages.carreira },
  { category: "negocios", label: "Negócios", date: "7 de ago. de 2026", title: "O que um cliente realmente compra", summary: "Mais do que uma oferta, o cliente escolhe segurança, clareza e a expectativa de uma solução bem executada.", href: "/artigos.html", image: articleImages["negocios-digitais"] },
  { category: "rotina", label: "Rotina", date: "4 de ago. de 2026", title: "Quando automatizar deixa de ser uma boa ideia", summary: "Automação funciona quando há processo estável; antes disso, pode apenas multiplicar falhas e ruídos.", href: "/artigos.html", image: articleImages.tecnologia },
  { category: "reflexao", label: "Reflexão", date: "1 de ago. de 2026", title: "A diferença entre estar ocupado e avançar", summary: "Uma reflexão objetiva sobre foco, critérios de prioridade e a disciplina de concluir o que realmente importa.", href: "/artigos.html", image: articleImages.reflexoes }
];

const isBlogPage = /\/artigos\.html$/.test(location.pathname) || location.pathname === "/artigos";

document.querySelectorAll("[data-article-grid]").forEach((grid) => {
  const collection = isBlogPage ? blogPosts : editorialPublications;
  grid.innerHTML = collection.map((item, index) => {
    const image = item.image || articleImages[item.category] || articleImages.gestao;
    return `<article class="article-card article-card--with-image" data-article-category="${item.category}"><div class="article-cover article-cover--image"><img src="${image}" alt="Imagem de apoio para ${item.label}: ${item.title}" loading="lazy"></div><div class="article-body"><span class="article-number">${publicationNumber(index, collection.length)}</span><span class="article-meta">${item.label} · ${item.date}</span><h2>${item.title}</h2><p>${item.summary}</p><a class="article-status" href="${item.href}">Saiba mais</a></div></article>`;
  }).join("");
});

const getArticleFilters = () => [...document.querySelectorAll("[data-article-filter]")];
const getArticleCards = () => [...document.querySelectorAll("[data-article-category]")];
const articleTitle = document.querySelector("[data-article-title]");
const articleCount = document.querySelector("[data-article-count]");
const articleEmpty = document.querySelector("[data-article-empty]");
const blogSearch = document.querySelector("[data-blog-search]");
const blogSort = document.querySelector("[data-blog-sort]");
let activeArticleCategory = location.hash.slice(1) || "all";

if (getArticleFilters().length && getArticleCards().length) {
  const showCategory = (category, updateHash = true) => {
    const articleFilters = getArticleFilters();
    const categoryLabels = Object.fromEntries(articleFilters.map((button) => [button.dataset.articleFilter, button.textContent.trim()]));
    const selected = categoryLabels[category] ? category : "all";
    activeArticleCategory = selected;
    const term = (blogSearch?.value || "").trim().toLocaleLowerCase("pt-BR");
    let visibleCount = 0;
    articleFilters.forEach((button) => {
      const active = button.dataset.articleFilter === selected;
      button.classList.toggle("active", active);
      button.setAttribute("aria-selected", String(active));
    });
    getArticleCards().filter((card) => card.isConnected).forEach((card) => {
      const inCategory = selected === "all" || card.dataset.articleCategory === selected;
      const visible = inCategory && (!term || card.textContent.toLocaleLowerCase("pt-BR").includes(term));
      card.hidden = !visible;
      if (visible) visibleCount += 1;
    });
    articleTitle.textContent = categoryLabels[selected];
    articleCount.textContent = visibleCount;
    articleEmpty.hidden = visibleCount !== 0;
    if (updateHash) history.replaceState(null, "", selected === "all" ? location.pathname : `#${selected}`);
  };
  document.querySelector(".article-category-nav")?.addEventListener("click", (event) => {
    const button = event.target.closest("[data-article-filter]");
    if (button) showCategory(button.dataset.articleFilter);
  });
  showCategory(location.hash.slice(1) || "all", false);
  document.addEventListener("globalinvest:publications-updated", () => showCategory(document.querySelector("[data-article-filter].active")?.dataset.articleFilter || "all", false));
}

if (isBlogPage) {
  document.querySelector(".article-category-nav")?.remove();
  const empty = document.querySelector("[data-article-empty]");
  if (empty) empty.hidden = true;
  const articleTitle = document.querySelector("[data-article-title]");
  if (articleTitle) articleTitle.textContent = "Publicações do Blog";
  const articleCount = document.querySelector("[data-article-count]");
  if (articleCount) articleCount.textContent = String(blogPosts.length);
}

/* Busca e ordenação do Blog: compatível com as categorias existentes. */
if (blogSearch || blogSort) {
  const applyBlogTools = () => {
    const grid = document.querySelector("[data-article-grid]");
    const cards = getArticleCards().filter((card) => card.isConnected);
    if (grid && blogSort?.value === "oldest") cards.slice().reverse().forEach((card) => grid.append(card));
    if (grid && blogSort?.value === "alpha") cards.slice().sort((a, b) => a.querySelector("h2")?.textContent.localeCompare(b.querySelector("h2")?.textContent, "pt-BR") || 0).forEach((card) => grid.append(card));
    if (grid && blogSort?.value === "alpha-desc") cards.slice().sort((a, b) => b.querySelector("h2")?.textContent.localeCompare(a.querySelector("h2")?.textContent, "pt-BR") || 0).forEach((card) => grid.append(card));
    if (grid && !["oldest", "alpha", "alpha-desc"].includes(blogSort?.value || "recent")) cards.slice().sort((a, b) => Number(b.querySelector(".article-number")?.textContent.replace(/\D/g, "") || 0) - Number(a.querySelector(".article-number")?.textContent.replace(/\D/g, "") || 0)).forEach((card) => grid.append(card));
    if (isBlogPage) {
      const term = (blogSearch?.value || "").trim().toLocaleLowerCase("pt-BR");
      let visibleCount = 0;
      cards.forEach((card) => {
        const visible = !term || card.textContent.toLocaleLowerCase("pt-BR").includes(term);
        card.hidden = !visible;
        if (visible) visibleCount += 1;
      });
      if (articleCount) articleCount.textContent = String(visibleCount);
      if (articleEmpty) {
        articleEmpty.hidden = visibleCount !== 0;
        articleEmpty.textContent = "Nenhuma publicação do Blog corresponde à sua busca.";
      }
      return;
    }
    const activeFilter = document.querySelector(`[data-article-filter="${activeArticleCategory}"]`) ? activeArticleCategory : "all";
    const activeButton = document.querySelector(`[data-article-filter="${activeFilter}"]`);
    if (activeButton) activeButton.click();
  };
  blogSearch?.addEventListener("input", applyBlogTools);
  blogSort?.addEventListener("change", applyBlogTools);
}

const productCategoryOverview = document.querySelector("[data-product-category-overview]");
if (productCategoryOverview && new URLSearchParams(location.search).get("categoria")) {
  productCategoryOverview.closest(".product-category-overview").hidden = true;
}

document.querySelectorAll(".footer-email").forEach((emailLink) => {
  if (emailLink.previousElementSibling?.classList.contains("footer-company-identity")) return;
  const identity = document.createElement("div");
  identity.className = "footer-company-identity";
  identity.setAttribute("aria-label", "Identificação da empresa");
  identity.innerHTML = "<small>Empresa:</small><strong>Global Invest Brasil</strong><span>Porto Alegre - Rio Grande do Sul</span>";
  emailLink.before(identity);
  emailLink.href = "mailto:contato@globalinvestbrasil.com";
  emailLink.innerHTML = "<span class=\"footer-email-label\">Email:</span><span class=\"footer-email-address\">contato@globalinvestbrasil.com</span>";
});

document.querySelectorAll('a[href^="mailto:"]:not(.footer-email)').forEach((emailLink) => {
  emailLink.href = "mailto:contato@globalinvestbrasil.com";
  emailLink.textContent = "contato@globalinvestbrasil.com";
});

function replacePublishedContactEmail(root = document.body) {
  if (!root) return;
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
  const matches = [];
  while (walker.nextNode()) {
    const node = walker.currentNode;
    const parent = node.parentElement;
    if (!parent || !node.nodeValue.includes("contato@globalinvestbr.com")) continue;
    if (parent.matches("script, style, title, textarea, option")) continue;
    matches.push(node);
  }
  matches.forEach((node) => {
    node.nodeValue = node.nodeValue.replaceAll("contato@globalinvestbr.com", "contato@globalinvestbrasil.com");
  });
}

replacePublishedContactEmail();

document.querySelectorAll(".footer-legal-line").forEach((node) => {
  node.textContent = "Global Invest Brasil - Todos os direitos reservados.";
});

function emphasizeGlobalInvestBrasil(root = document.body) {
  if (!root) return;
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
  const matches = [];
  while (walker.nextNode()) {
    const node = walker.currentNode;
    const parent = node.parentElement;
    if (!parent || !node.nodeValue.includes("Global Invest Brasil")) continue;
    if (parent.closest(".global-invest-name") || parent.matches("script, style, title, textarea, option")) continue;
    matches.push(node);
  }
  matches.forEach((node) => {
    const pieces = node.nodeValue.split("Global Invest Brasil");
    const fragment = document.createDocumentFragment();
    pieces.forEach((piece, index) => {
      if (piece) fragment.append(document.createTextNode(piece));
      if (index < pieces.length - 1) {
        const strong = document.createElement("strong");
        strong.className = "global-invest-name";
        strong.textContent = "Global Invest Brasil";
        fragment.append(strong);
      }
    });
    node.replaceWith(fragment);
  });
}

emphasizeGlobalInvestBrasil();
new MutationObserver((records) => {
  records.forEach((record) => record.addedNodes.forEach((node) => {
    if (node.nodeType === Node.ELEMENT_NODE) {
      replacePublishedContactEmail(node);
      emphasizeGlobalInvestBrasil(node);
    }
  }));
}).observe(document.body, { childList: true, subtree: true });

document.querySelectorAll("[data-year]").forEach((node) => node.textContent = new Date().getFullYear());

/* Checkout direto na Shopify. Os botões de compra apontam para a página do
   produto na loja (ex.: https://brcrystals.com/products/{handle}). No clique,
   resolvemos o ID da variante via {loja}/products/{handle}.js e mandamos o
   visitante direto para o checkout ({loja}/cart/{id}:1?return_to=/checkout).
   Funciona com qualquer domínio de loja. Se a consulta falhar (loja com
   senha, produto indisponível, offline), abre a página do produto normalmente. */
(function shopifyDirectCheckout() {
  const variantCache = new Map();

  function parseStoreLink(href) {
    let url;
    try { url = new URL(href, location.href); } catch (e) { return null; }
    if (url.origin === location.origin) return null; // link interno, não é a loja
    const m = url.pathname.match(/^\/products\/([a-z0-9_-]+)\/?$/i);
    return m ? { origin: url.origin, handle: m[1] } : null;
  }

  async function resolveVariantId(origin, handle) {
    const key = origin + "|" + handle;
    if (variantCache.has(key)) return variantCache.get(key);
    const response = await fetch(`${origin}/products/${handle}.js`, { headers: { accept: "application/json" } });
    if (!response.ok) throw new Error("produto indisponível");
    const data = await response.json();
    const variants = Array.isArray(data.variants) ? data.variants : [];
    const chosen = variants.find((variant) => variant.available !== false) || variants[0];
    if (!chosen || !chosen.id) throw new Error("variante não encontrada");
    variantCache.set(key, chosen.id);
    return chosen.id;
  }

  document.addEventListener("click", (event) => {
    const link = event.target.closest('a[href*="/products/"]');
    if (!link || link.dataset.checkoutBusy === "true") return;
    const store = parseStoreLink(link.getAttribute("href"));
    if (!store) return;
    event.preventDefault();
    link.dataset.checkoutBusy = "true";
    const newTab = link.target === "_blank";
    const go = (url) => (newTab ? window.open(url, "_blank", "noopener") : window.location.assign(url));
    resolveVariantId(store.origin, store.handle)
      .then((variantId) => go(`${store.origin}/cart/${variantId}:1?return_to=/checkout`))
      .catch(() => go(link.href))
      .finally(() => { link.dataset.checkoutBusy = "false"; });
  });
})();
