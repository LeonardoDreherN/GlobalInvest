(function () {
  "use strict";

  const PRODUCT_LABELS = { site: "Site", ecommerce: "E-commerce", aplicativo: "Aplicativo" };
  const STORAGE_KEY = "gib_formulario_state_v1";

  const root = document.getElementById("wizard-root");
  if (!root) return;

  const state = {
    schema: null,
    product: null,
    client: {},
    answers: {},
    stepIndex: 0, // 0 = client step, then product steps, then review
    startedAt: Date.now(),
  };

  function loadDraft() {
    try {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) return;
      const draft = JSON.parse(raw);
      if (draft && typeof draft === "object") {
        state.product = draft.product || null;
        state.client = draft.client || {};
        state.answers = draft.answers || {};
        state.stepIndex = draft.stepIndex || 0;
      }
    } catch (e) { /* ignora rascunho corrompido */ }
  }

  function saveDraft() {
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
        product: state.product, client: state.client, answers: state.answers, stepIndex: state.stepIndex,
      }));
    } catch (e) { /* armazenamento indisponível — segue sem persistência */ }
  }

  function totalSteps() {
    if (!state.product) return 2; // escolha do produto + (placeholder)
    const productSteps = state.schema.products[state.product].steps.length;
    return 1 + productSteps + 1; // cliente + etapas do produto + revisão
  }

  function fieldsForStep(index) {
    if (index === 0) return state.schema.client_step.fields;
    const productSteps = state.schema.products[state.product].steps;
    const productStepIndex = index - 1;
    if (productStepIndex >= 0 && productStepIndex < productSteps.length) return productSteps[productStepIndex].fields;
    return null;
  }

  function stepTitle(index) {
    if (index === 0) return state.schema.client_step.title;
    const productSteps = state.schema.products[state.product].steps;
    const productStepIndex = index - 1;
    if (productStepIndex >= 0 && productStepIndex < productSteps.length) return productSteps[productStepIndex].title;
    return "Revisão e envio";
  }

  function dataForStep(index) { return index === 0 ? state.client : state.answers; }

  function isVisible(field, data) {
    if (!field.showIf) return true;
    const cond = field.showIf;
    const value = data[cond.key];
    if (cond.equals !== undefined) return value === cond.equals;
    if (cond.includes !== undefined) return Array.isArray(value) && value.includes(cond.includes);
    return true;
  }

  function el(tag, attrs, children) {
    const node = document.createElement(tag);
    if (attrs) for (const k in attrs) {
      if (k === "class") node.className = attrs[k];
      else if (k === "html") node.innerHTML = attrs[k];
      else node.setAttribute(k, attrs[k]);
    }
    (children || []).forEach((c) => { if (c) node.appendChild(c); });
    return node;
  }

  function renderField(field, data, onChange) {
    const wrap = el("div", { class: "field field-" + field.type + (field.type === "textarea" ? " field-full" : "") });
    const labelText = field.label + (field.required ? " *" : "");
    const id = "f_" + field.key;
    let input;

    if (field.type === "textarea") {
      input = el("textarea", { id, name: field.key, rows: "4" });
      input.value = data[field.key] || "";
      input.addEventListener("input", () => onChange(field.key, input.value));
    } else if (field.type === "select") {
      input = el("select", { id, name: field.key });
      input.appendChild(el("option", { value: "" }, [document.createTextNode("Selecione")]));
      (field.options || []).forEach((opt) => {
        const o = el("option", { value: opt }, [document.createTextNode(opt)]);
        if (data[field.key] === opt) o.selected = true;
        input.appendChild(o);
      });
      input.addEventListener("change", () => onChange(field.key, input.value));
    } else if (field.type === "yesno") {
      const group = el("div", { class: "pill-group", role: "radiogroup", "aria-label": field.label });
      ["Sim", "Não"].forEach((opt) => {
        const active = data[field.key] === opt;
        const btn = el("button", { type: "button", class: "pill" + (active ? " active" : ""), "aria-pressed": active ? "true" : "false" }, [document.createTextNode(opt)]);
        btn.addEventListener("click", () => { onChange(field.key, opt); rerenderStep(); });
        group.appendChild(btn);
      });
      wrap.appendChild(el("label", {}, [document.createTextNode(labelText)]));
      wrap.appendChild(group);
      return wrap;
    } else if (field.type === "checkbox") {
      const group = el("div", { class: "check-group" });
      const current = Array.isArray(data[field.key]) ? data[field.key] : [];
      (field.options || []).forEach((opt, i) => {
        const cid = id + "_" + i;
        const cb = el("input", { type: "checkbox", id: cid, value: opt });
        if (current.includes(opt)) cb.checked = true;
        cb.addEventListener("change", () => {
          const list = Array.isArray(data[field.key]) ? data[field.key].slice() : [];
          const idx = list.indexOf(opt);
          if (cb.checked && idx === -1) list.push(opt);
          if (!cb.checked && idx !== -1) list.splice(idx, 1);
          onChange(field.key, list);
          rerenderStep();
        });
        const lbl = el("label", { class: "check-item", for: cid }, [cb, document.createTextNode(" " + opt)]);
        group.appendChild(lbl);
      });
      wrap.appendChild(el("label", {}, [document.createTextNode(labelText)]));
      wrap.appendChild(group);
      return wrap;
    } else {
      const type = field.type === "tel" ? "tel" : field.type === "email" ? "email" : field.type === "url" ? "url" : field.type === "date" ? "date" : "text";
      input = el("input", { id, name: field.key, type });
      if (field.placeholder) input.setAttribute("placeholder", field.placeholder);
      if (field.autocomplete) input.setAttribute("autocomplete", field.autocomplete);
      input.value = data[field.key] || "";
      input.addEventListener("input", () => onChange(field.key, input.value));
    }

    if (field.required) input.setAttribute("required", "required");
    wrap.appendChild(el("label", { for: id }, [document.createTextNode(labelText)]));
    wrap.appendChild(input);
    return wrap;
  }

  let stepFormEl = null;

  function rerenderStep() { renderStep(false); }

  function progressPercent() {
    const total = totalSteps();
    return Math.round((state.stepIndex / (total - 1)) * 100);
  }

  function renderProgress() {
    const total = totalSteps();
    const pct = progressPercent();
    root.querySelector("[data-progress-label]").textContent = "Etapa " + (state.stepIndex + 1) + " de " + total;
    root.querySelector("[data-progress-pct]").textContent = pct + "% concluído";
    root.querySelector("[data-progress-bar]").style.width = pct + "%";
  }

  function renderChoiceStep() {
    stepFormEl.innerHTML = "";
    stepFormEl.appendChild(el("p", { class: "step-intro" }, [document.createTextNode("Qual produto digital você deseja contratar?")]));
    const cards = el("div", { class: "product-cards" });
    Object.keys(state.schema.products).forEach((key) => {
      const p = state.schema.products[key];
      const card = el("button", { type: "button", class: "product-card" }, [
        el("strong", {}, [document.createTextNode(p.label)]),
        el("span", {}, [document.createTextNode(p.description || "")]),
      ]);
      card.addEventListener("click", () => {
        state.product = key;
        state.stepIndex = 0;
        saveDraft();
        renderStep(true);
      });
      cards.appendChild(card);
    });
    stepFormEl.appendChild(cards);
    root.querySelector("[data-nav]").style.display = "none";
    root.querySelector("[data-progress-wrap]").style.display = "none";
  }

  function renderReviewStep() {
    stepFormEl.innerHTML = "";
    stepFormEl.appendChild(el("p", { class: "step-intro" }, [document.createTextNode("Revise suas informações antes de enviar. Você pode voltar e editar qualquer etapa.")]));

    const summary = el("div", { class: "review-summary" });

    function addBlock(title, fields, data, stepIdx) {
      const visible = fields.filter((f) => isVisible(f, data) && (data[f.key] !== undefined && data[f.key] !== "" && !(Array.isArray(data[f.key]) && data[f.key].length === 0)));
      if (!visible.length) return;
      const block = el("div", { class: "review-block" });
      const head = el("div", { class: "review-block-head" }, [
        el("h3", {}, [document.createTextNode(title)]),
        (function () { const b = el("button", { type: "button", class: "link-btn" }, [document.createTextNode("Editar")]); b.addEventListener("click", () => { state.stepIndex = stepIdx; renderStep(true); }); return b; })(),
      ]);
      block.appendChild(head);
      const dl = el("dl", {});
      visible.forEach((f) => {
        const val = Array.isArray(data[f.key]) ? data[f.key].join(", ") : data[f.key];
        dl.appendChild(el("dt", {}, [document.createTextNode(f.label)]));
        dl.appendChild(el("dd", {}, [document.createTextNode(String(val))]));
      });
      block.appendChild(dl);
      summary.appendChild(block);
    }

    addBlock("Seus dados", state.schema.client_step.fields, state.client, 0);
    state.schema.products[state.product].steps.forEach((step, i) => addBlock(step.title, step.fields, state.answers, i + 1));

    stepFormEl.appendChild(summary);

    const consentWrap = el("label", { class: "consent" });
    const consentInput = el("input", { type: "checkbox", id: "consent" });
    consentInput.checked = !!state.consent;
    consentInput.addEventListener("change", () => { state.consent = consentInput.checked; });
    consentWrap.appendChild(consentInput);
    consentWrap.appendChild(el("span", { html: 'Declaro que as informações fornecidas são verdadeiras e autorizo seu tratamento pela Global Invest Brasil para análise, contato e elaboração do escopo/orçamento solicitado, conforme a <a href="/privacidade.html" target="_blank" rel="noopener">Política de Privacidade</a>.' }));
    stepFormEl.appendChild(consentWrap);

    const honeypot = el("div", { class: "honeypot", "aria-hidden": "true" });
    const hpInput = el("input", { type: "text", id: "website", name: "website", tabindex: "-1", autocomplete: "off" });
    honeypot.appendChild(el("label", {}, [document.createTextNode("Não preencha este campo"), hpInput]));
    stepFormEl.appendChild(honeypot);

    root.dataset.honeypotId = "website";

    const errorBox = el("p", { class: "form-status", "data-review-error": "1", "aria-live": "polite" });
    stepFormEl.appendChild(errorBox);

    root.querySelector("[data-nav]").style.display = "flex";
    root.querySelector("[data-continue]").textContent = "Enviar levantamento";
    root.querySelector("[data-progress-wrap]").style.display = "block";
  }

  function renderSuccess(reference) {
    root.innerHTML =
      '<div class="success-card" id="print-area">' +
      '<span class="success-icon" aria-hidden="true">✓</span>' +
      "<h2>Recebemos o seu levantamento</h2>" +
      "<p>Obrigado. Seu levantamento foi recebido pela Global Invest Brasil. Nossa equipe analisará as informações para elaboração do escopo do seu projeto.</p>" +
      '<p class="reference">Código da solicitação: <strong>' + reference + "</strong></p>" +
      '<div class="success-actions no-print">' +
      '<button type="button" class="btn btn-primary" onclick="window.print()">Imprimir / salvar em PDF</button>' +
      '<a class="btn btn-light" href="/">Voltar ao site</a>' +
      "</div></div>";
    try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
  }

  function collectStepFields(index) {
    const fields = fieldsForStep(index);
    const data = dataForStep(index);
    return { fields, data };
  }

  function validateStep(index) {
    const { fields, data } = collectStepFields(index);
    for (const f of fields) {
      if (!isVisible(f, data)) continue;
      if (f.required) {
        const v = data[f.key];
        const empty = v === undefined || v === null || v === "" || (Array.isArray(v) && v.length === 0);
        if (empty) return f;
      }
      if (f.type === "email" && data[f.key]) {
        const v = String(data[f.key]);
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return f;
      }
    }
    return null;
  }

  function renderStep(scrollTop) {
    if (!state.product) { renderChoiceStep(); return; }
    const total = totalSteps();
    if (state.stepIndex >= total - 1) { renderReviewStep(); renderProgress(); if (scrollTop) window.scrollTo({ top: root.offsetTop - 20, behavior: "smooth" }); return; }

    const { fields, data } = collectStepFields(state.stepIndex);
    stepFormEl.innerHTML = "";
    root.querySelector("[data-step-title]").textContent = stepTitle(state.stepIndex);
    fields.forEach((f) => {
      if (!isVisible(f, data)) return;
      const node = renderField(f, data, (key, value) => { data[key] = value; saveDraft(); });
      stepFormEl.appendChild(node);
    });

    root.querySelector("[data-nav]").style.display = "flex";
    root.querySelector("[data-back]").style.visibility = state.stepIndex === 0 ? "hidden" : "visible";
    root.querySelector("[data-continue]").textContent = "Continuar";
    root.querySelector("[data-progress-wrap]").style.display = "block";
    renderProgress();
    if (scrollTop) window.scrollTo({ top: root.offsetTop - 20, behavior: "smooth" });
  }

  async function submit() {
    const errorBox = stepFormEl.querySelector("[data-review-error]");
    errorBox.textContent = "";
    errorBox.className = "form-status";

    if (!state.consent) { errorBox.textContent = "É necessário aceitar o tratamento dos dados para enviar."; errorBox.className = "form-status error"; return; }

    const honeypotEl = document.getElementById("website");
    const payload = {
      product: state.product,
      client: state.client,
      answers: state.answers,
      consent: !!state.consent,
      website: honeypotEl ? honeypotEl.value : "",
      form_started_at: state.startedAt,
    };

    const btn = root.querySelector("[data-continue]");
    btn.disabled = true; btn.textContent = "Enviando...";

    try {
      const res = await fetch("/api/briefing", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) });
      const data = await res.json();
      if (!res.ok || !data.ok) {
        errorBox.textContent = data.error || "Não foi possível enviar. Tente novamente.";
        errorBox.className = "form-status error";
        btn.disabled = false; btn.textContent = "Enviar levantamento";
        return;
      }
      renderSuccess(data.reference);
    } catch (e) {
      errorBox.textContent = "Falha de conexão. Verifique sua internet e tente novamente.";
      errorBox.className = "form-status error";
      btn.disabled = false; btn.textContent = "Enviar levantamento";
    }
  }

  function buildShell() {
    root.innerHTML =
      '<div class="wizard-progress" data-progress-wrap>' +
      '<div class="wizard-progress-labels"><span data-progress-label></span><span data-progress-pct></span></div>' +
      '<div class="wizard-progress-track"><div class="wizard-progress-bar" data-progress-bar></div></div>' +
      "</div>" +
      '<h2 class="step-title" data-step-title></h2>' +
      '<form class="wizard-form" data-form></form>' +
      '<div class="wizard-nav" data-nav>' +
      '<button type="button" class="btn btn-light" data-back>Voltar</button>' +
      '<button type="button" class="btn btn-primary" data-continue>Continuar</button>' +
      "</div>";
    stepFormEl = root.querySelector("[data-form]");

    root.querySelector("[data-back]").addEventListener("click", () => {
      if (state.stepIndex === 0) { state.product = null; saveDraft(); renderStep(true); return; }
      state.stepIndex -= 1; saveDraft(); renderStep(true);
    });

    root.querySelector("[data-continue]").addEventListener("click", () => {
      const total = totalSteps();
      if (state.stepIndex >= total - 1) { submit(); return; }
      const invalid = validateStep(state.stepIndex);
      if (invalid) {
        const inputEl = document.getElementById("f_" + invalid.key);
        if (inputEl && inputEl.reportValidity) { inputEl.setAttribute("required", "required"); inputEl.reportValidity(); }
        else alert("Preencha corretamente: " + invalid.label);
        return;
      }
      state.stepIndex += 1;
      saveDraft();
      renderStep(true);
    });
  }

  function applyUrlProduct() {
    const params = new URLSearchParams(location.search);
    const produto = (params.get("produto") || "").toLowerCase();
    if (["site", "ecommerce", "aplicativo"].includes(produto) && !state.product) {
      state.product = produto;
      state.stepIndex = 0;
    }
  }

  fetch("/assets/data/formulario-perguntas.json")
    .then((r) => r.json())
    .then((schema) => {
      state.schema = schema;
      loadDraft();
      applyUrlProduct();
      buildShell();
      renderStep(false);
    })
    .catch(() => {
      root.innerHTML = '<p class="form-status error">Não foi possível carregar o formulário. Atualize a página ou tente novamente em instantes.</p>';
    });
})();
