/*
 * Global Invest Brasil — comportamento específico de algumas páginas:
 * conteúdos vindos do banco (/api/content), abas das mentorias e diagnóstico de riscos.
 */
(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const MONTHS = ["jan.", "fev.", "mar.", "abr.", "maio", "jun.", "jul.", "ago.", "set.", "out.", "nov.", "dez."];

  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]);
  const fmtDate = (iso) => {
    const d = iso ? new Date(iso) : null;
    return d && !isNaN(d) ? `${d.getDate()} de ${MONTHS[d.getMonth()]} de ${d.getFullYear()}` : "";
  };
  const safeUrl = (u) => (/^(https?:\/\/|\/)/i.test(u || "") ? u : "");

  let contentPromise;
  const loadContent = () => {
    contentPromise ??= fetch("/api/content", { cache: "no-store" })
      .then((r) => (r.ok ? r.json() : null))
      .catch(() => null);
    return contentPromise;
  };

  const postCard = (item, lead = false) => {
    const img = safeUrl(item.image);
    return `<a class="post${lead ? " is-lead" : ""}" href="${esc(safeUrl(item.link) || "#")}">
      ${img ? `<div class="post-media"><img src="${esc(img)}" alt="${esc(item.image_alt || "")}" loading="lazy"></div>` : ""}
      <div class="post-body">
        ${item.category ? `<span class="post-cat">${esc(item.category)}</span>` : ""}
        <h3>${esc(item.title)}</h3>
        ${item.summary ? `<p>${esc(item.summary)}</p>` : ""}
        ${item.published_at ? `<span class="post-date">${fmtDate(item.published_at)}</span>` : ""}
      </div>
    </a>`;
  };

  const allPosts = (content) => {
    const pages = content?.pages || {};
    const tag = (list, kind) => (Array.isArray(list) ? list : []).map((p) => ({ ...p, kind }));
    return [...tag(pages.blog, "blog"), ...tag(pages.publicacoes, "publicacoes")]
      .sort((a, b) => new Date(b.published_at || 0) - new Date(a.published_at || 0));
  };

  /* Home: troca os livros pelos 3 conteúdos mais recentes, quando existirem */
  async function initLatest() {
    const grid = $("[data-latest]");
    if (!grid) return;
    const posts = allPosts(await loadContent());
    const n = parseInt(grid.dataset.latest, 10) || 3;
    if (posts.length >= n) grid.innerHTML = posts.slice(0, n).map((p) => postCard(p)).join("");
  }

  /* Conteúdos: lista com filtro por tipo e busca */
  async function initFeed() {
    const feed = $("[data-feed]");
    if (!feed) return;
    const content = await loadContent();
    const posts = allPosts(content);
    const chips = $$("[data-filter]");
    const search = $("[data-search]");
    let filter = "todos";

    const render = () => {
      const q = (search.value || "").trim().toLowerCase();
      const list = posts.filter((p) => (filter === "todos" || p.kind === filter) &&
        (!q || `${p.title} ${p.summary} ${p.category}`.toLowerCase().includes(q)));
      if (!list.length) {
        feed.innerHTML = posts.length
          ? `<div class="empty"><h3>Nada encontrado</h3><p>Nenhum conteúdo com esse termo. Tente outra palavra ou limpe a busca.</p></div>`
          : `<div class="empty"><h3>Novos artigos em breve</h3><p>Estamos preparando os próximos conteúdos. Assine a newsletter abaixo para receber quando forem publicados, ou veja os livros.</p></div>`;
        return;
      }
      const lead = filter === "todos" && !q && list.length > 3;
      feed.innerHTML = list.map((p, i) => postCard(p, lead && i === 0)).join("");
    };

    chips.forEach((chip) => chip.addEventListener("click", () => {
      filter = chip.dataset.filter;
      chips.forEach((c) => c.setAttribute("aria-pressed", String(c === chip)));
      render();
    }));
    search.addEventListener("input", render);
    render();

    // Livros com página de compra cadastrada no painel usam esse link.
    const products = content?.pages?.produtos || [];
    $$("[data-book]").forEach((a) => {
      const match = products.find((p) => (p.title || "").toLowerCase().includes(a.dataset.book.toLowerCase()));
      const url = safeUrl(match?.slug ? `/produto/${match.slug}` : "");
      if (url) a.href = url;
    });
  }

  /* Mentorias: abas por fase, com teclado e endereço (#blindagem etc.) */
  function initPhases() {
    const tabs = $$('[role="tab"]');
    if (!tabs.length) return;
    const select = (tab, focus = false) => {
      tabs.forEach((t) => {
        const on = t === tab;
        t.setAttribute("aria-selected", String(on));
        t.tabIndex = on ? 0 : -1;
        const panel = document.getElementById(t.getAttribute("aria-controls"));
        panel.hidden = !on;
        if (on) {
          panel.classList.remove("is-entering");
          void panel.offsetWidth;
          panel.classList.add("is-entering");
        }
      });
      if (focus) tab.focus();
    };
    tabs.forEach((tab, i) => {
      tab.addEventListener("click", () => {
        select(tab);
        history.replaceState(null, "", "#" + tab.getAttribute("aria-controls"));
      });
      tab.addEventListener("keydown", (e) => {
        const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
        if (!step) return;
        e.preventDefault();
        select(tabs[(i + step + tabs.length) % tabs.length], true);
      });
    });
    const fromHash = tabs.find((t) => "#" + t.getAttribute("aria-controls") === location.hash);
    if (fromHash) {
      select(fromHash);
      requestAnimationFrame(() => document.getElementById("fases")?.scrollIntoView());
    }
  }

  /* Diagnóstico: índice de proteção e indicação de mentoria */
  function initDiag() {
    const form = $("[data-diag]");
    if (!form) return;
    const risks = $$("[data-risk]", form);
    const gauge = $("[data-gauge]", form);
    const scoreEl = $("[data-score]", form);
    const verdict = $("[data-verdict]", form);
    const advice = $("[data-advice]", form);
    const cta = $("[data-cta]", form);
    const progress = $("[data-progress]", form);
    const mentoria = {
      organizando: ["Organizando Meu Negócio", "/mentorias.html#organizando"],
      escalando: ["Escalando Meu Negócio", "/mentorias.html#escalando"],
      blindagem: ["Blindagem Patrimonial", "/mentorias.html#blindagem"],
    };

    const update = () => {
      const answers = risks.map((r) => {
        const checked = $("input:checked", r);
        r.classList.toggle("is-answered", !!checked);
        return { name: r.dataset.risk, key: $("input", r).name, value: checked ? Number(checked.value) : null };
      });
      const done = answers.filter((a) => a.value !== null);
      progress.textContent = `${done.length} de ${risks.length} respondidas`;
      if (!done.length) return;

      const risk = done.reduce((s, a) => s + a.value, 0);
      const score = Math.round(100 - (risk / (done.length * 2)) * 100);
      scoreEl.textContent = score;
      gauge.style.strokeDashoffset = String(100 - score);
      gauge.style.stroke = score >= 75 ? "var(--ok)" : score >= 45 ? "var(--warn)" : "var(--orange)";

      if (done.length < risks.length) {
        verdict.textContent = "Continue respondendo";
        advice.textContent = "O índice vai se ajustando a cada resposta.";
        cta.hidden = true;
        return;
      }
      const weak = answers.filter((a) => a.value === 2).map((a) => a.name);
      const partial = answers.filter((a) => a.value === 1).map((a) => a.name);
      const focus = weak.length ? weak : partial;
      let pick = mentoria.organizando;
      const onlyGrowth = focus.length && focus.every((n) => ["Dependência do dono", "Crescimento sem estrutura"].includes(n));
      if (onlyGrowth) pick = mentoria.escalando;
      if (!focus.length) pick = mentoria.blindagem;

      if (score >= 75) verdict.textContent = "Base bem protegida";
      else if (score >= 45) verdict.textContent = "Proteção parcial";
      else verdict.textContent = "Exposição alta";
      advice.textContent = focus.length
        ? `Comece por: ${focus.slice(0, 3).join(", ").toLowerCase()}. A mentoria indicada é ${pick[0]}.`
        : `Os fundamentos estão em ordem. O próximo passo é proteger o patrimônio construído com a ${pick[0]}.`;
      cta.href = pick[1];
      cta.hidden = false;
    };
    form.addEventListener("change", update);
    form.addEventListener("submit", (e) => e.preventDefault());
  }

  document.addEventListener("DOMContentLoaded", () => {
    initLatest();
    initFeed();
    initPhases();
    initDiag();
  });
})();
