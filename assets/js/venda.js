/*
 * Global Invest Brasil — páginas de venda (livros, e-books e landings).
 * Checkout na Hotmart, barra de compra fixa, captura do e-book
 * gratuito e calculadora do dia da virada.
 */
(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

  /* Botões [data-checkout="id"]: o href já aponta para o checkout da Hotmart,
     que deixa o comprador escolher Pix ou cartão. Aqui só marcamos o evento,
     para o link seguir funcionando mesmo sem JS. */
  function initCheckout() {
    $$("[data-checkout]").forEach((el) => el.addEventListener("click", () => {
      const handle = el.dataset.checkout;
      const value = parseFloat(el.dataset.value) || undefined;
      if (typeof window.fbq === "function") window.fbq("track", "InitiateCheckout", value ? { value, currency: "BRL" } : {});
      if (typeof window.gtag === "function") window.gtag("event", "begin_checkout", { value, currency: "BRL", items: [{ item_id: handle }] });
    }));
  }

  /* Barra de compra: aparece depois do topo e some quando a oferta está na tela */
  function initBuybar() {
    const bar = $("[data-buybar]");
    const hero = $(".sale-hero");
    if (!bar || !hero || !("IntersectionObserver" in window)) return;
    const offer = $("#oferta");
    let heroOut = false, offerIn = false;
    const sync = () => bar.classList.toggle("is-on", heroOut && !offerIn);
    new IntersectionObserver(([e]) => { heroOut = !e.isIntersecting; sync(); }).observe(hero);
    if (offer) new IntersectionObserver(([e]) => { offerIn = e.isIntersecting; sync(); }, { threshold: 0.15 }).observe(offer);
  }

  /* E-book gratuito: valida, grava como contato e leva para a página de download */
  function initLeadForm() {
    const form = $("[data-lead]");
    if (!form) return;
    const mask = (v) => {
      v = v.replace(/\D/g, "").slice(0, 11);
      if (v.length <= 2) return v.length ? `(${v}` : "";
      if (v.length <= 6) return `(${v.slice(0, 2)}) ${v.slice(2)}`;
      if (v.length <= 10) return `(${v.slice(0, 2)}) ${v.slice(2, 6)}-${v.slice(6)}`;
      return `(${v.slice(0, 2)}) ${v.slice(2, 7)}-${v.slice(7)}`;
    };
    form.whatsapp.addEventListener("input", () => { form.whatsapp.value = mask(form.whatsapp.value); });
    const rules = {
      nome: (v) => v.trim().length >= 2 || "Escreva seu nome.",
      email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) || "Confira o e-mail digitado.",
      whatsapp: (v) => v.replace(/\D/g, "").length >= 10 || "Informe o número com DDD.",
    };
    const check = (name) => {
      const input = form.elements[name];
      const res = rules[name](input.value);
      const field = input.closest(".field");
      field.classList.toggle("has-error", res !== true);
      $(".err", field).textContent = res === true ? "" : res;
      return res === true;
    };
    Object.keys(rules).forEach((n) => form.elements[n].addEventListener("blur", () => check(n)));
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const status = $("[data-status]", form);
      if (form.website.value) { location.href = "obrigado.html"; return; }
      const ok = Object.keys(rules).map(check).every(Boolean);
      if (!ok) { form.querySelector(".has-error input")?.focus(); return; }
      if (typeof window.fbq === "function") window.fbq("track", "Lead", { content_name: "Ebook Da Ideia ao Lucro" });
      const btn = $('button[type="submit"]', form);
      btn.disabled = true;
      btn.firstChild.textContent = "Enviando… ";
      try {
        await window.GIB.postJSON("/api/contacts", {
          name: form.nome.value.trim(), email: form.email.value.trim(), phone: form.whatsapp.value,
          subject: "E-book gratuito - Metodo da Ideia ao Lucro",
          message: `Solicitou o e-book gratuito pela landing /ebook-gratuito/. WhatsApp: ${form.whatsapp.value}`,
          consent: true, website: "", site: "gib",
        });
        location.href = "obrigado.html";
      } catch {
        btn.disabled = false;
        btn.firstChild.textContent = "Baixar o e-book gratuito ";
        window.GIB.setStatus(status, "Não conseguimos registrar agora. Tente de novo em instantes.", "error");
      }
    });
  }

  /* Calculadora: em que dia do mês a empresa empata e começa a lucrar */
  function initCalc() {
    const root = $("[data-calc]");
    if (!root) return;
    const el = (id) => document.getElementById(id);
    const money = (n) => "R$ " + Math.round(n).toLocaleString("pt-BR");
    const month = el("calc-month");
    month.innerHTML = Array.from({ length: 30 }, (_, i) => `<i>${i + 1}</i>`).join("");
    const days = $$("i", month);
    const run = () => {
      const price = +el("preco").value, fixed = +el("fixo").value, qty = +el("qtd").value;
      let cost = +el("custo").value;
      if (cost >= price) { cost = price - 1; el("custo").value = cost; }
      el("v-preco").textContent = money(price);
      el("v-custo").textContent = money(cost);
      el("v-fixo").textContent = money(fixed);
      el("v-qtd").textContent = qty.toLocaleString("pt-BR") + " un.";
      const margin = price - cost;
      const breakeven = Math.ceil(fixed / margin);
      const day = Math.ceil((breakeven / qty) * 30);
      el("r-margem").textContent = money(margin);
      el("r-pe").textContent = breakeven.toLocaleString("pt-BR");
      el("r-dia").textContent = day > 30 ? "Não vira" : "Dia " + day;
      days.forEach((d, k) => {
        const n = k + 1;
        d.className = day <= 30 && n === day ? "is-turn" : day <= 30 && n > day ? "is-yours" : "";
      });
      const profit = margin * qty - fixed;
      let txt;
      if (day > 30) txt = `Nesse cenário a empresa <b>não chega ao ponto de equilíbrio</b>: faltam ${(breakeven - qty).toLocaleString("pt-BR")} unidades no mês. Vender mais assim aprofunda a perda. O que precisa mudar é a margem ou o custo fixo.`;
      else if (day >= 26) txt = `A virada só acontece no <b>dia ${day}</b>. Sobram ${30 - day} dias de lucro no mês inteiro, e uma queda pequena nas vendas apaga tudo.`;
      else if (day >= 16) txt = `A virada cai no <b>dia ${day}</b> e o mês fecha com cerca de ${money(profit)} de lucro operacional. Dá para viver, mas qualquer desconto mal calculado come essa sobra.`;
      else txt = `A virada acontece já no <b>dia ${day}</b>, com cerca de ${money(profit)} de lucro no mês. A pergunta agora deixa de ser sobrevivência e passa a ser escala.`;
      el("calc-verdict").innerHTML = txt;
    };
    $$('input[type="range"]', root).forEach((i) => i.addEventListener("input", run));
    run();
  }

  document.addEventListener("DOMContentLoaded", () => {
    initCheckout();
    initBuybar();
    initLeadForm();
    initCalc();
  });
})();
