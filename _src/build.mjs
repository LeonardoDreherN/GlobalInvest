/*
 * Monta as páginas estáticas do site a partir de _src/pages/*.html.
 * Cada página começa com um comentário JSON de metadados:
 *   <!--{ "title": "...", "description": "...", "path": "/x.html", "nav": "solucoes" }-->
 * Uso: node _src/build.mjs  (grava os .html na raiz do projeto)
 */
import { readFileSync, writeFileSync, readdirSync, mkdirSync } from "node:fs";
import { dirname, join, relative } from "node:path";
import { fileURLToPath } from "node:url";

const SRC = dirname(fileURLToPath(import.meta.url));
const ROOT = join(SRC, "..");
const BASE_URL = "https://globalinvestbrasil.com";
const ASSET_VERSION = "2026-10-28";

const SITE = {
  email: "contato@globalinvestbrasil.com",
  city: "Florianópolis · SC",
  // Preencha com os perfis oficiais; redes sem endereço não aparecem no rodapé.
  social: { instagram: "", linkedin: "", youtube: "" },
};

const ICON_PATHS = {
  arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
  "arrow-up-right": '<path d="M7 17 17 7M8 7h9v9"/>',
  bag: '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
  code: '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
  box: '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
  headset: '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><path d="M4 14h3v5H5a1 1 0 0 1-1-1v-4ZM20 14h-3v5h2a1 1 0 0 0 1-1v-4Z"/><path d="M17 19c0 1.5-2 2-5 2"/>',
  sliders: '<path d="M4 7h10M18 7h2M4 17h4M12 17h8"/><circle cx="16" cy="7" r="2"/><circle cx="10" cy="17" r="2"/>',
  shield: '<path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
  trend: '<path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
  globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3Z"/>',
  layers: '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 13 9 5 9-5"/>',
  device: '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
  monitor: '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/>',
  compass: '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/>',
  book: '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2V5Z"/><path d="M4 19a2 2 0 0 1 2-2h13"/>',
  mic: '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M6 11a6 6 0 0 0 12 0M12 17v4"/>',
  users: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6.5 6.5 0 0 1 3.5 6"/>',
  check: '<path d="m5 12 4.5 4.5L19 7"/>',
  plus: '<path d="M12 5v14M5 12h14"/>',
  mail: '<rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m4 7 8 6 8-6"/>',
  pin: '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
  clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
  copy: '<rect x="8" y="8" width="12" height="12" rx="1.5"/><path d="M16 8V5.5A1.5 1.5 0 0 0 14.5 4h-9A1.5 1.5 0 0 0 4 5.5v9A1.5 1.5 0 0 0 5.5 16H8"/>',
  instagram: '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r=".8" fill="currentColor"/>',
  linkedin: '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path d="M8 10.5V16M8 7.5v.01M11.5 16v-5.5M11.5 13c0-1.5 1-2.5 2.3-2.5S16 11.3 16 13v3"/>',
  youtube: '<rect x="2.5" y="5.5" width="19" height="13" rx="3.5"/><path d="m10.5 9.5 4 2.5-4 2.5v-5Z"/>',
  menu: '<path d="M4 8h16M4 16h16"/>',
  close: '<path d="m6 6 12 12M18 6 6 18"/>',
  chevron: '<path d="m6 9 6 6 6-6"/>',
};

const icon = (name, cls = "icon") =>
  `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICON_PATHS[name] ?? ""}</svg>`;

const MARK = `<picture><source srcset="/assets/images/site/logo-gib.webp" type="image/webp"><img class="brand-mark" src="/assets/images/site/logo-gib.png" alt="" width="159" height="144"></picture>`;

const BRAND = (cls = "") =>
  `<a class="brand ${cls}" href="/" aria-label="Global Invest Brasil, início">${MARK}<span class="brand-name">Global Invest <b>Brasil</b></span></a>`;

const NAV = [
  { key: "inicio", label: "Início", href: "/" },
  {
    key: "solucoes",
    label: "Soluções",
    href: "/solucoes.html",
    children: [
      { label: "E-commerce", note: "Lojas Shopify e Nuvemshop", href: "/ecommerce.html", icon: "bag" },
      { label: "Desenvolvimento", note: "Sites institucionais e apps", href: "/desenvolvimento.html", icon: "code" },
      { label: "Logística e importação", note: "Compra, frete e envio internacional", href: "/logistica.html", icon: "box" },
      { label: "Mentorias", note: "Do primeiro negócio ao patrimônio", href: "/mentorias.html", icon: "compass" },
      { label: "Proteja seu negócio", note: "Diagnóstico de riscos em 2 minutos", href: "/seu-negocio.html", icon: "shield" },
      { label: "Livros e e-books", note: "E-book gratuito e obras do Prof. Dadalt", href: "/conteudos.html#livros", icon: "book" },
    ],
  },
  { key: "quem-somos", label: "Quem somos", href: "/quem-somos.html" },
  { key: "conteudos", label: "Conteúdos", href: "/conteudos.html" },
];

function header(active) {
  const items = NAV.map((item) => {
    const cur = item.key === active ? ' aria-current="page"' : "";
    if (!item.children) return `<li><a class="nav-link" href="${item.href}"${cur}>${item.label}</a></li>`;
    const sub = item.children
      .map((c) => `<li><a href="${c.href}">${icon(c.icon)}<span><strong>${c.label}</strong><small>${c.note}</small></span></a></li>`)
      .join("");
    return `<li class="has-sub"><a class="nav-link" href="${item.href}"${cur}>${item.label}</a><button class="sub-toggle" type="button" aria-expanded="false" aria-label="Abrir ${item.label}">${icon("chevron")}</button><ul class="subnav">${sub}</ul></li>`;
  }).join("");
  return `<header class="site-header" data-header>
  <div class="wrap header-row">
    ${BRAND()}
    <nav class="main-nav" id="menu" aria-label="Navegação principal" data-menu>
      <ul class="nav-list">${items}</ul>
      <a class="btn btn-line nav-cta" href="/contato.html">Vamos conversar ${icon("arrow")}</a>
    </nav>
    <button class="menu-toggle" type="button" aria-controls="menu" aria-expanded="false" data-menu-toggle><span class="sr">Abrir menu</span>${icon("menu", "icon i-open")}${icon("close", "icon i-close")}</button>
  </div>
</header>`;
}

function footer() {
  const social = Object.entries(SITE.social)
    .filter(([, url]) => url)
    .map(([name, url]) => `<a href="${url}" target="_blank" rel="noopener" aria-label="${name}">${icon(name)}</a>`)
    .join("");
  const year = new Date().getFullYear();
  return `<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      ${BRAND("brand-footer")}
      <p class="footer-tag">Estratégia · Negócios · Tecnologia</p>
      <p class="footer-note">Atividade educacional e de consultoria em negócios. Não fazemos gestão de recursos de terceiros nem recomendação de valores mobiliários.</p>
    </div>
    <nav class="footer-col" aria-label="Navegação do rodapé">
      <h2>Navegação</h2>
      <ul><li><a href="/">Início</a></li><li><a href="/solucoes.html">Soluções</a></li><li><a href="/quem-somos.html">Quem somos</a></li><li><a href="/conteudos.html">Conteúdos</a></li><li><a href="/conteudos.html#livros">Livros e e-books</a></li><li><a href="/ebook-gratuito/">E-book gratuito</a></li><li><a href="/contato.html">Contato</a></li></ul>
    </nav>
    <nav class="footer-col" aria-label="Soluções">
      <h2>Soluções</h2>
      <ul><li><a href="/ecommerce.html">E-commerce</a></li><li><a href="/desenvolvimento.html">Desenvolvimento</a></li><li><a href="/logistica.html">Logística e importação</a></li><li><a href="/mentorias.html">Mentorias</a></li><li><a href="/seu-negocio.html">Proteja seu negócio</a></li></ul>
    </nav>
    <div class="footer-col">
      <h2>Fale com a gente</h2>
      <ul class="footer-contact">
        <li>${icon("mail")}<a href="mailto:${SITE.email}">${SITE.email}</a></li>
        <li>${icon("pin")}<span>${SITE.city}</span></li>
      </ul>
    </div>
    <form class="footer-news" data-newsletter novalidate>
      <h2><label for="newsletter-email">Receba nossos conteúdos</label></h2>
      <p>Novidades, tendências e análises de negócio direto no seu e-mail. Sem excesso.</p>
      <div class="news-row">
        <input id="newsletter-email" name="email" type="email" placeholder="Seu e-mail" autocomplete="email" required>
        <input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button type="submit" aria-label="Inscrever e-mail">${icon("arrow")}</button>
      </div>
      <p class="form-status" data-status aria-live="polite"></p>
    </form>
  </div>
  <div class="wrap footer-bottom">
    <p>© ${year} Global Invest Brasil. Todos os direitos reservados.</p>
    <p class="footer-legal"><a href="/privacidade.html">Política de privacidade</a><a href="/politicas-de-uso.html">Políticas de uso</a></p>
    ${social ? `<p class="footer-social">${social}</p>` : ""}
  </div>
</footer>`;
}

function layout(meta, body) {
  const url = BASE_URL + meta.path.replace(/(^|\/)index\.html$/, "$1");
  const title = meta.path === "/index.html" || meta.fullTitle ? meta.title : `${meta.title} | Global Invest Brasil`;
  const ogImage = BASE_URL + (meta.image || "/assets/images/globalinvest-social.jpg");
  const scripts = (meta.scripts || []).map((s) => `<script src="${s}?v=${ASSET_VERSION}" defer></script>`).join("\n");
  return `<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>${title}</title>
<meta name="description" content="${meta.description}">
<meta name="robots" content="${meta.noindex ? "noindex" : "index,follow,max-image-preview:large"}">
<meta name="theme-color" content="#0b1a1c">
<link rel="canonical" href="${url}">
<meta property="og:type" content="${meta.ogType || "website"}">
${meta.head || ""}
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="Global Invest Brasil">
<meta property="og:title" content="${title}">
<meta property="og:description" content="${meta.description}">
<meta property="og:url" content="${url}">
<meta property="og:image" content="${ogImage}">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,300;0,6..72,400;0,6..72,500;1,6..72,300;1,6..72,400&display=swap">
<link rel="stylesheet" href="/assets/css/site.css?v=${ASSET_VERSION}">
<script>document.documentElement.classList.add("js")</script>
<script src="/assets/js/site.js?v=${ASSET_VERSION}" defer></script>
${scripts}
<script src="/adsense-loader.php" defer></script>
</head>
<body class="${meta.bodyClass || ""}">
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
${header(meta.nav)}
<main id="conteudo">
${body.trim()}
</main>
${footer()}
${meta.after || ""}
</body>
</html>
`;
}

function render(src) {
  const match = src.match(/^<!--(\{[\s\S]*?\})-->\s*/);
  if (!match) throw new Error("Página sem metadados");
  const meta = JSON.parse(match[1]);
  let body = src.slice(match[0].length);
  body = body.replace(/\{\{include:([\w./-]+)\}\}/g, (_, file) => readFileSync(join(SRC, file), "utf8"));
  body = body.replace(/\{\{icon:([\w-]+)\}\}/g, (_, name) => icon(name));
  body = body.replace(/\{\{email\}\}/g, SITE.email);
  return layout(meta, body);
}

const PAGES = join(SRC, "pages");
for (const entry of readdirSync(PAGES, { recursive: true }).filter((f) => f.endsWith(".html"))) {
  const html = render(readFileSync(join(PAGES, entry), "utf8"));
  const out = join(ROOT, entry);
  mkdirSync(dirname(out), { recursive: true });
  writeFileSync(out, html);
  console.log("ok", relative(ROOT, out).replace(/\\/g, "/"));
}

// Cabeçalho e rodapé também alimentam os templates PHP (blog, publicações e produtos).
writeFileSync(join(ROOT, "app", "site-shell.php"), `<?php
// Gerado por _src/build.mjs — não edite à mão.
const SITE_ASSET_VERSION = '${ASSET_VERSION}';
function site_header(string $active = ''): string { return ${phpString(header("__ACTIVE__"))}; }
function site_footer(): string { return ${phpString(footer())}; }
`);
console.log("ok app/site-shell.php");

function phpString(html) {
  return "'" + html.replace(/\\/g, "\\\\").replace(/'/g, "\\'") + "'";
}
