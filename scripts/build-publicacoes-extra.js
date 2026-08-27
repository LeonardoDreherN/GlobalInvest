/*
 * Extrai as publicações que estão embutidas em assets/js/new-publications*.js
 * (objeto `publications`) e gera database/publicacoes-extra.json no formato que
 * migrate-conteudo.php consome. Rode com: node scripts/build-publicacoes-extra.js
 */
const fs = require("fs");
const path = require("path");

const root = path.join(__dirname, "..");
const sources = ["assets/js/new-publications.js", "assets/js/new-publications-2.js"];

const meses = { janeiro: 1, fevereiro: 2, "março": 3, marco: 3, abril: 4, maio: 5, junho: 6, julho: 7, agosto: 8, setembro: 9, outubro: 10, novembro: 11, dezembro: 12 };
function parseData(texto) {
  const m = String(texto).match(/(\d{1,2})\s+de\s+([a-zç]+)\s+de\s+(\d{4})/i);
  if (!m) return null;
  const d = String(m[1]).padStart(2, "0");
  const mo = String(meses[m[2].toLowerCase()] || 1).padStart(2, "0");
  return `${m[3]}-${mo}-${d} 12:00:00`;
}

const rows = [];
for (const rel of sources) {
  const code = fs.readFileSync(path.join(root, rel), "utf8");
  const start = code.indexOf("const publications");
  const eq = code.indexOf("=", start) + 1;
  // fim do objeto: o "};" imediatamente antes de "const node"
  const nodePos = code.indexOf("const node", eq);
  const end = code.lastIndexOf("};", nodePos);
  const objLiteral = code.slice(eq, end + 1).trim();
  const publications = eval("(" + objLiteral + ")");
  for (const [slug, item] of Object.entries(publications)) {
    const content = item.sections
      .map(([heading, ...paragraphs]) => `<h2>${heading}</h2>` + paragraphs.map((p) => `<p>${p}</p>`).join(""))
      .join("");
    rows.push({
      slug,
      category: item.category || "",
      title: item.title || "",
      excerpt: item.deck || "",
      content,
      image_url: item.image ? `/assets/images/publicacoes/${item.image}` : "",
      image_alt: item.alt || "",
      published_at: parseData(item.date) || null,
    });
  }
}

const out = path.join(root, "database", "publicacoes-extra.json");
fs.writeFileSync(out, JSON.stringify(rows, null, 2) + "\n");
console.log(`${rows.length} publicações -> ${path.relative(root, out)}`);
for (const r of rows) console.log(`  ${r.slug}  [${r.category}]  ${r.published_at}`);
