/* Servidor estático só para pré-visualizar o site localmente: node _src/serve.mjs */
import { createServer } from "node:http";
import { readFile, stat } from "node:fs/promises";
import { extname, join, normalize } from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = join(fileURLToPath(new URL(".", import.meta.url)), "..");
const PORT = Number(process.env.PORT) || 4321;
const TYPES = { ".html": "text/html; charset=utf-8", ".css": "text/css", ".js": "text/javascript", ".svg": "image/svg+xml", ".png": "image/png", ".jpg": "image/jpeg", ".webp": "image/webp", ".ico": "image/x-icon", ".json": "application/json", ".webmanifest": "application/manifest+json" };

createServer(async (req, res) => {
  const path = decodeURIComponent(new URL(req.url, "http://x").pathname);
  if (path.startsWith("/api/")) {
    res.writeHead(200, { "Content-Type": "application/json" });
    return res.end(req.method === "POST" ? '{"ok":true}' : '{"home":[],"pages":{"produtos":[],"publicacoes":[],"blog":[]}}');
  }
  if (path.endsWith(".php")) { res.writeHead(200, { "Content-Type": "text/javascript" }); return res.end(""); }
  let file = normalize(join(ROOT, path));
  if (!file.startsWith(ROOT)) { res.writeHead(403); return res.end(); }
  try {
    if ((await stat(file)).isDirectory()) file = join(file, "index.html");
    res.writeHead(200, { "Content-Type": TYPES[extname(file)] || "application/octet-stream" });
    res.end(await readFile(file));
  } catch {
    res.writeHead(404, { "Content-Type": "text/html; charset=utf-8" });
    res.end(await readFile(join(ROOT, "404.html")));
  }
}).listen(PORT, () => console.log(`http://localhost:${PORT}`));
