/*
 * Extrai os produtos renderizados por assets/js/product-pages.js (objeto `pages`)
 * e gera database/produtos-extra.json no formato que migrate-produtos.php consome.
 * Rode com: node scripts/build-produtos-js.js
 */
const fs = require("fs");
const path = require("path");

const root = path.join(__dirname, "..");
const code = fs.readFileSync(path.join(root, "assets/js/product-pages.js"), "utf8");

const start = code.indexOf("const pages");
const eq = code.indexOf("=", start) + 1;
const nodePos = code.indexOf("const page ", eq);
const end = code.lastIndexOf("};", nodePos);
const pages = eval("(" + code.slice(eq, end + 1).trim() + ")");

// type do product-pages -> categoria cadastrada (product_categories)
function categoria(type) {
  const t = String(type || "").toLowerCase();
  if (t.startsWith("mentoria")) return "Mentorias";
  if (t.includes("curso")) return "Cursos e palestras";
  if (t.includes("palestra")) return "Cursos e palestras";
  if (t.includes("livro")) return "Livros";
  if (t.includes("book")) return "E-books";
  return "Cursos e palestras";
}

function normalizaUrl(u) {
  u = String(u || "").trim();
  if (/^https?:\/\//i.test(u)) return u;
  return u.replace(/^(\.\.\/)+/, "/");
}

function corpo(p) {
  const partes = [];
  if (p.aboutTitle) partes.push(`## ${p.aboutTitle}`);
  (p.about || []).forEach((par) => partes.push(par));
  if (p.benefits && p.benefits.length) {
    partes.push("## O que você encontra");
    partes.push(p.benefits.map(([t, d]) => `- **${t}** — ${d}`).join("\n"));
  }
  if (p.audience && p.audience.length) {
    partes.push("## Para quem é e como funciona");
    partes.push(p.audience.map(([label, texto]) => `- **${label}** — ${texto}`).join("\n"));
  }
  if (p.steps && p.steps.length) {
    partes.push("## Método de trabalho");
    partes.push(p.steps.map(([n, t, d]) => `${String(n).replace(/^0/, "")}. **${t}** — ${d}`).join("\n"));
  }
  if (p.notice) partes.push(`> ${p.notice}`);
  return partes.join("\n\n");
}

const rows = Object.entries(pages).map(([slug, p]) => ({
  slug,
  category: categoria(p.type),
  title: p.title || "",
  summary: p.lead || "",
  body: corpo(p),
  image_url: p.image ? `/assets/images/produtos/${p.image}` : "",
  purchase_url: normalizaUrl(p.ctaHref),
  cta_label: p.cta || "Quero saber mais",
  price: null,
}));

const out = path.join(root, "database", "produtos-extra.json");
fs.writeFileSync(out, JSON.stringify(rows, null, 2) + "\n");
console.log(`${rows.length} produtos -> ${path.relative(root, out)}`);
for (const r of rows) console.log(`  ${r.slug}  [${r.category}]  cta=${r.purchase_url}`);
