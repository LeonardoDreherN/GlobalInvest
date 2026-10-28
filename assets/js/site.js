/*
 * Global Invest Brasil — interações do site.
 * Fala só com a API própria (/api/*); nenhuma credencial fica no navegador.
 */
(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const store = {
    get(key) { try { return localStorage.getItem(key); } catch { return null; } },
    set(key, value) { try { localStorage.setItem(key, value); } catch { /* armazenamento bloqueado */ } },
  };

  async function postJSON(url, data) {
    const res = await fetch(url, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(data) });
    let body = {};
    try { body = await res.json(); } catch { /* resposta sem JSON */ }
    if (!res.ok || body.error) throw new Error(body.error || "Não foi possível enviar agora. Tente de novo em alguns minutos.");
    return body;
  }

  function setStatus(el, text, kind) {
    if (!el) return;
    el.textContent = text;
    el.className = "form-status" + (kind ? " " + kind : "");
  }

  /* ---------- Cabeçalho: fica sólido ao rolar e some ao descer ---------- */
  function initHeader() {
    const header = $("[data-header]");
    if (!header) return;
    let lastY = window.scrollY;
    const update = () => {
      const y = window.scrollY;
      header.classList.toggle("is-solid", y > 40);
      const goingDown = y > lastY && y > 480 && !document.body.classList.contains("menu-open");
      header.classList.toggle("is-hidden", goingDown);
      lastY = y;
    };
    update();
    window.addEventListener("scroll", update, { passive: true });
  }

  /* ---------- Menu ---------- */
  function initMenu() {
    const toggle = $("[data-menu-toggle]");
    if (toggle) {
      toggle.addEventListener("click", () => {
        const open = document.body.classList.toggle("menu-open");
        toggle.setAttribute("aria-expanded", String(open));
        $(".sr", toggle).textContent = open ? "Fechar menu" : "Abrir menu";
      });
    }
    $$(".has-sub").forEach((item) => {
      const btn = $(".sub-toggle", item);
      btn.addEventListener("click", () => {
        const open = item.classList.toggle("is-open");
        btn.setAttribute("aria-expanded", String(open));
      });
    });
    document.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") return;
      document.body.classList.remove("menu-open");
      toggle?.setAttribute("aria-expanded", "false");
      $$(".has-sub.is-open").forEach((i) => i.classList.remove("is-open"));
    });
  }

  /* ---------- Revelação ao rolar (o que já está na tela aparece direto) ---------- */
  function initReveal() {
    const items = $$("[data-reveal], .steps");
    if (!("IntersectionObserver" in window) || reduceMotion) {
      items.forEach((el) => el.classList.add("is-in"));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-in");
        io.unobserve(entry.target);
      });
    }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });
    items.forEach((el) => {
      const r = el.getBoundingClientRect();
      if (r.top < window.innerHeight * 0.95) el.classList.add("is-in");
      else io.observe(el);
    });
  }

  /* ---------- Parallax leve nas imagens de fundo ---------- */
  function initParallax() {
    if (reduceMotion) return;
    const layers = $$("[data-parallax]");
    if (!layers.length) return;
    let ticking = false;
    const run = () => {
      layers.forEach((el) => {
        const box = el.parentElement.getBoundingClientRect();
        if (box.bottom < 0 || box.top > window.innerHeight) return;
        const speed = parseFloat(el.dataset.parallax) || 0.15;
        el.style.transform = `translate3d(0, ${(-box.top * speed).toFixed(1)}px, 0)`;
      });
      ticking = false;
    };
    window.addEventListener("scroll", () => { if (!ticking) { ticking = true; requestAnimationFrame(run); } }, { passive: true });
    run();
  }

  /* ---------- Brilho que segue o cursor nos cards ---------- */
  function initSpotlight() {
    $$(".sol-card").forEach((card) => {
      card.addEventListener("pointermove", (e) => {
        const r = card.getBoundingClientRect();
        card.style.setProperty("--mx", `${e.clientX - r.left}px`);
        card.style.setProperty("--my", `${e.clientY - r.top}px`);
      });
    });
  }

  /* ---------- Newsletter ---------- */
  function initNewsletter() {
    $$("[data-newsletter]").forEach((form) => {
      const status = $("[data-status]", form);
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const email = form.email.value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          setStatus(status, "Digite um e-mail válido, como nome@empresa.com.", "error");
          form.email.focus();
          return;
        }
        const btn = $("button", form);
        btn.disabled = true;
        try {
          await postJSON("/api/newsletter", { email, website: form.website.value, source: location.pathname });
          setStatus(status, "Inscrição feita. O próximo conteúdo chega no seu e-mail.", "ok");
          form.reset();
        } catch (err) {
          setStatus(status, err.message, "error");
        } finally {
          btn.disabled = false;
        }
      });
    });
  }

  /* ---------- Formulário de contato ---------- */
  function initContact() {
    const form = $("[data-contact]");
    if (!form) return;
    const status = $("[data-status]", form);
    const params = new URLSearchParams(location.search);
    const preset = params.get("assunto");
    if (preset) {
      // Aceita tanto o nome do tema quanto os códigos usados nas páginas de produto (ex.: mentoria-organizando).
      const aliases = [[/mentoria/i, "Mentoria"], [/palestra/i, "Palestra"], [/ecommerce|e-commerce|loja/i, "E-commerce"], [/site|app|desenvolvimento/i, "Desenvolvimento"], [/log[ií]stica|importa/i, "Logística e importação"], [/livro|e-?book|curso/i, "Livros"]];
      const value = $('input[name="topic"]', form).some((i) => i.value === preset) ? preset : aliases.find(([re]) => re.test(preset))?.[1];
      const chip = $('input[name="topic"]', form).find((i) => i.value === value);
      if (chip) chip.checked = true;
    }

    const rules = {
      name: (v) => v.trim().length >= 2 || "Diga como podemos te chamar.",
      email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) || "Digite um e-mail válido, como nome@empresa.com.",
      message: (v) => v.trim().length >= 10 || "Conte um pouco mais sobre o que você precisa.",
    };
    const check = (name) => {
      const input = form.elements[name];
      const result = rules[name](input.value);
      const field = input.closest(".field");
      field.classList.toggle("has-error", result !== true);
      $(".err", field).textContent = result === true ? "" : result;
      return result === true;
    };
    Object.keys(rules).forEach((name) => form.elements[name].addEventListener("blur", () => check(name)));

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const valid = Object.keys(rules).map(check).every(Boolean);
      if (!valid) { setStatus(status, "Revise os campos marcados.", "error"); return; }
      if (!form.consent.checked) { setStatus(status, "Para enviar, aceite a Política de Privacidade.", "error"); return; }
      const topic = form.querySelector('input[name="topic"]:checked')?.value || "Outro assunto";
      const btn = $('button[type="submit"]', form);
      btn.disabled = true;
      setStatus(status, "Enviando…");
      try {
        await postJSON("/api/contacts", {
          name: form.name.value, email: form.email.value, phone: form.phone.value,
          subject: `${topic}${form.company.value ? " · " + form.company.value : ""}`,
          message: form.message.value, consent: true, website: form.website.value, site: "gib",
        });
        form.reset();
        setStatus(status, "Mensagem recebida. Vamos responder no e-mail informado.", "ok");
      } catch (err) {
        setStatus(status, err.message, "error");
      } finally {
        btn.disabled = false;
      }
    });
  }

  /* ---------- Copiar e-mail ---------- */
  function initCopy() {
    $$("[data-copy]").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const label = $("span", btn);
        try {
          await navigator.clipboard.writeText(btn.dataset.copy);
          label.textContent = "Copiado";
        } catch {
          label.textContent = btn.dataset.copy;
        }
        setTimeout(() => { label.textContent = "Copiar e-mail"; }, 2200);
      });
    });
  }

  /* ---------- Sumário das páginas longas ---------- */
  function initToc() {
    const toc = $("[data-toc]");
    const prose = $("[data-toc-source]");
    if (!toc || !prose) return;
    const heads = $$("h2", prose);
    heads.forEach((h, i) => { h.id = h.id || `secao-${i + 1}`; });
    toc.insertAdjacentHTML("beforeend", heads.map((h) => `<a href="#${h.id}">${h.textContent.replace(/^\d+\.\s*/, "")}</a>`).join(""));
    if (!("IntersectionObserver" in window)) return;
    const links = $$("a", toc);
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        links.forEach((a) => a.classList.toggle("is-active", a.hash === "#" + entry.target.id));
      });
    }, { rootMargin: "-20% 0px -70% 0px" });
    heads.forEach((h) => io.observe(h));
  }

  /* ---------- Cookies (LGPD) e Meta Pixel ---------- */
  function initMetaPixel() {
    if (window.fbq) return;
    /* eslint-disable */
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    /* eslint-enable */
    window.fbq("init", "1055068993986637");
    window.fbq("track", "PageView");
  }

  function initCookies() {
    const KEY = "globalinvest:cookie-consent";
    const saved = store.get(KEY);
    if (saved === "accepted_all") initMetaPixel();
    if (saved) return;
    const box = document.createElement("div");
    box.className = "cookie";
    box.setAttribute("role", "dialog");
    box.setAttribute("aria-label", "Preferências de cookies");
    box.innerHTML = `<p>Usamos cookies essenciais para o site funcionar e, com a sua permissão, cookies de medição e publicidade. Detalhes na <a href="/privacidade.html">Política de privacidade</a>.</p>
      <div class="btn-row"><button class="btn" type="button" data-choice="accepted_all">Aceitar todos</button><button class="btn btn-line" type="button" data-choice="rejected_optional">Só os essenciais</button></div>`;
    document.body.appendChild(box);
    requestAnimationFrame(() => requestAnimationFrame(() => box.classList.add("is-visible")));
    box.addEventListener("click", (e) => {
      const choice = e.target.closest("[data-choice]")?.dataset.choice;
      if (!choice) return;
      store.set(KEY, choice);
      document.cookie = `globalinvest_cookie_consent=${choice}; path=/; max-age=31536000; SameSite=Lax`;
      if (choice === "accepted_all") initMetaPixel();
      fetch("/api/cookie-consent", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ preferences: choice }) }).catch(() => {});
      box.classList.remove("is-visible");
      setTimeout(() => box.remove(), 500);
    });
  }

  /* Utilitários compartilhados com os scripts de página */
  window.GIB = { $, $$, postJSON, setStatus, store, reduceMotion };

  document.addEventListener("DOMContentLoaded", () => {
    initHeader();
    initMenu();
    initReveal();
    initParallax();
    initSpotlight();
    initNewsletter();
    initContact();
    initCopy();
    initToc();
    initCookies();
  });
})();
