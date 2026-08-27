(() => {
  const root = document.querySelector("[data-landing-content]");
  if (!root) return;

  const pages = {
    "produto-palestras": {
      type: "Palestras estratégicas", title: "Palestras que conectam conhecimento, decisão e execução.",
      lead: "Conteúdo sob medida para equipes, eventos e comunidades que precisam transformar informação qualificada em prioridades claras, conversa produtiva e ação consistente.",
      image: "palestra.webp", imageAlt: "Palestras Global Invest Brasil: muito conteúdo e experiência", imageClass: "lecture-art",
      cta: "Solicitar proposta", ctaHref: "../contato.html?assunto=palestras", back: "../produtos.html",
      proof: [["Conteúdo sob medida", "Temas e exemplos alinhados ao público"], ["Aplicação prática", "Ideias transformadas em prioridades"], ["Formato flexível", "Presencial, online ou híbrido"]],
      aboutTitle: "Mais do que inspiração: uma conversa preparada para gerar movimento.",
      about: ["Uma palestra relevante começa antes do palco. A Global Invest Brasil parte do contexto do público, dos objetivos do encontro e das decisões que precisam amadurecer. O resultado é uma apresentação com repertório técnico, linguagem clara e exemplos conectados à realidade de quem participa.", "Cada entrega pode combinar gestão, negócios digitais, tecnologia, planejamento, cenários e proteção patrimonial. O foco não é oferecer fórmulas prontas, mas ampliar a qualidade da reflexão e criar uma base comum para os próximos passos da organização."],
      benefits: [["Diagnóstico do contexto", "Definição de objetivos, nível de profundidade e recorte temático."], ["Roteiro com propósito", "Conteúdo estruturado para informar, provocar e orientar decisões."], ["Encaminhamentos úteis", "Síntese final que ajuda o público a sair do evento com prioridades claras."]],
      audience: [["Para quem é", "Empresas, associações, escolas, eventos corporativos e comunidades que buscam conteúdo de alto nível com relevância prática."], ["Como funciona", "Briefing, desenho do tema, preparação do roteiro e entrega no formato mais adequado ao público."]],
      steps: [["01", "Briefing estratégico", "Objetivos, perfil do público e contexto."], ["02", "Desenho do roteiro", "Tema, profundidade e exemplos aplicáveis."], ["03", "Entrega", "Palestra presencial, online ou híbrida."], ["04", "Próximos passos", "Síntese e caminhos para continuar a conversa."]]
    },
    "produto-curso": {
      type: "Curso online", title: "Gestão de Negócios Online: método para estruturar, administrar e crescer.",
      lead: "Uma formação prática para empreendedores que precisam organizar posicionamento, planejamento, finanças, processos, vendas, tecnologia e indicadores em uma rotina de gestão consistente.",
      image: "curso-gestao-negocios-online.webp", imageAlt: "Curso online Gestão de Negócios Online", imageClass: "tall-art",
      cta: "Quero participar do curso", ctaHref: "https://gems-br.myshopify.com/products/curso-de-gestao-de-negocios-online", ctaExternal: true, back: "../produtos.html",
      proof: [["Módulos organizados", "Conteúdo construído por etapas"], ["Aplicação prática", "Use no próprio negócio"], ["Visão integrada", "Gestão, vendas e tecnologia"]],
      aboutTitle: "O negócio digital exige método, não improviso.",
      about: ["Muitos negócios começam com uma boa ideia e encontram dificuldade justamente no momento de transformar intenção em operação. Este curso organiza os elementos que sustentam uma empresa digital: proposta de valor, metas, precificação, controles financeiros, processos, aquisição de clientes e acompanhamento de indicadores.", "A formação foi pensada para quem quer enxergar as conexões entre decisão comercial, rotina operacional e crescimento. Em vez de tratar cada tema de forma isolada, apresenta uma sequência de trabalho que pode ser adaptada à maturidade e à realidade de cada negócio."],
      benefits: [["Posicionamento e proposta", "Clareza sobre público, problema, oferta e diferenciação."], ["Finanças e indicadores", "Números que ajudam a acompanhar margem, caixa e desempenho."], ["Processos e tecnologia", "Rotinas e ferramentas para ganhar consistência sem perder controle."]],
      audience: [["Para quem é", "Empreendedores, gestores e profissionais que desejam organizar ou profissionalizar um negócio digital."], ["Como funciona", "Conteúdo estruturado em módulos, com exemplos, exercícios de reflexão e orientação para aplicação."]],
      steps: [["01", "Posicionamento", "Defina proposta, público e direção."], ["02", "Planejamento", "Transforme objetivos em prioridades."], ["03", "Gestão", "Organize finanças, processos e indicadores."], ["04", "Crescimento", "Acompanhe, corrija e avance com método."]]
    },
    "produto-o-seu-maior-patrimonio": {
      type: "Curso online", title: "O Seu Maior Patrimônio: muito além do dinheiro no banco.",
      lead: "Um curso para repensar o que realmente sustenta uma vida e um negócio de longo prazo — critério, conhecimento e responsabilidade como os verdadeiros ativos que protegem decisões, relações e resultados ao longo do tempo.",
      image: "o-seu-maior-patrimonio.webp", imageAlt: "Curso O Seu Maior Patrimônio", imageClass: "tall-art",
      cta: "Quero fazer o curso", ctaHref: "https://gems-br.myshopify.com/products/o-seu-maior-patrimonio", ctaExternal: true, back: "../produtos.html",
      proof: [["Visão de longo prazo", "Patrimônio além do número em extrato"], ["Critério antes de fórmula", "Método para decidir, não atalhos"], ["Aplicação prática", "Reflexão conectada ao dia a dia"]],
      aboutTitle: "Patrimônio é mais do que número em extrato.",
      about: ["Mais do que acumular bens, o professor Jorge Dadalt propõe uma reflexão sobre critério, conhecimento e responsabilidade como os verdadeiros ativos que protegem decisões, relações e resultados ao longo do tempo.", "Uma formação para quem busca construir patrimônio com consciência — não apenas números, mas capacidade de decidir bem."],
      benefits: [["Nova definição de patrimônio", "Entenda o que sustenta resultado e continuidade além do saldo bancário."], ["Critério para decisões", "Desenvolva parâmetros próprios para escolhas de longo prazo."], ["Aplicação no dia a dia", "Conecte a reflexão à rotina pessoal e à gestão do negócio."]],
      audience: [["Para quem é", "Empresários, profissionais e investidores que quer ampliar sua visão sobre construção de patrimônio."], ["Como funciona", "Conteúdo estruturado para refletir, aplicar e revisar critérios de decisão de longo prazo."]],
      steps: [["01", "Reflexão", "O que realmente é patrimônio."], ["02", "Critério", "Parâmetros para decidir bem."], ["03", "Aplicação", "Conecte com sua realidade."], ["04", "Continuidade", "Revise e sustente escolhas no tempo."]]
    },
    "produto-primeiro-negocio": {
      type: "Mentoria 01", title: "Meu Primeiro Negócio: da ideia à primeira estrutura comercial.",
      lead: "Uma mentoria para quem quer começar com mais clareza, testar uma proposta de valor e construir uma base realista para os primeiros 90 dias de operação.",
      image: "mentoria-primeiro-negocio.webp", imageAlt: "Mentoria Meu Primeiro Negócio", imageClass: "tall-art",
      cta: "Quero conversar sobre a mentoria", ctaHref: "../contato.html?assunto=mentoria-primeiro-negocio", back: "../mentorias.html",
      proof: [["Direção clara", "Perfil, público e proposta"], ["Validação", "Teste da ideia com método"], ["Primeiros 90 dias", "Plano inicial de execução"]],
      aboutTitle: "Começar bem significa aprender antes de escalar.",
      about: ["A primeira fase de um negócio pede escolhas simples, mas decisivas: qual problema será atendido, para quem, por qual proposta e com quais recursos. A mentoria organiza essas perguntas em uma sequência de trabalho que reduz dispersão e ajuda a transformar hipótese em uma oferta testável.", "O processo não promete resultado automático. Ele oferece critério para construir uma estrutura comercial inicial, estimar necessidades básicas, definir prioridades e iniciar a operação com mais consciência sobre risco, esforço e oportunidade."],
      benefits: [["Proposta e público", "Delimite o problema, o perfil do cliente e a oferta inicial."], ["Validação orientada", "Teste hipóteses, organize evidências e ajuste o caminho."], ["Plano de partida", "Defina prioridades comerciais e operacionais para os primeiros meses."]],
      audience: [["Para quem é", "Pessoas que desejam transformar uma ideia em negócio e empreendedores em fase inicial de organização."], ["Como funciona", "Encontros orientados por diagnóstico, com tarefas objetivas e plano de ação adaptado ao estágio do projeto."]],
      steps: [["01", "Ideia", "Problema, público e oportunidade."], ["02", "Validação", "Hipóteses, testes e aprendizado."], ["03", "Estrutura", "Oferta, canais e rotina inicial."], ["04", "Lançamento", "Plano de ação para entrar em campo."]]
    },
    "produto-organizando": {
      type: "Mentoria 02", title: "Organizando Meu Negócio: processos claros para crescer com controle.",
      lead: "Uma mentoria para empresas que já operam, mas precisam melhorar rotina, controles, visão financeira e acompanhamento de indicadores para decidir com mais segurança.",
      image: "mentoria-organizando-negocio.webp", imageAlt: "Mentoria Organizando Meu Negócio", imageClass: "tall-art",
      cta: "Quero organizar meu negócio", ctaHref: "../contato.html?assunto=mentoria-organizando", back: "../mentorias.html",
      proof: [["Processos", "Mais eficiência no dia a dia"], ["Controle financeiro", "Visão completa do negócio"], ["Indicadores", "Números para decidir melhor"]],
      aboutTitle: "Crescimento sem rotina cria esforço; organização cria capacidade.",
      about: ["Quando uma empresa passa a vender mais, as decisões deixam de caber na memória do fundador. A mentoria ajuda a mapear atividades críticas, organizar responsáveis, estabelecer controles e dar visibilidade às informações que realmente interessam à gestão.", "O trabalho combina leitura do momento atual com construção de uma rotina viável. A intenção é reduzir retrabalho, melhorar a previsibilidade e permitir que o empreendedor avance sem depender exclusivamente de intervenções urgentes."],
      benefits: [["Mapa de processos", "Identifique fluxos essenciais, gargalos e responsáveis."], ["Rotina financeira", "Estruture dados de faturamento, custos, caixa e margem."], ["Painel de indicadores", "Defina métricas simples para acompanhar o que importa."]],
      audience: [["Para quem é", "Empresas em operação que precisam transformar improviso em processo e ampliar o controle gerencial."], ["Como funciona", "Diagnóstico, definição de prioridades, encontros de trabalho e acompanhamento da implantação das rotinas combinadas."]],
      steps: [["01", "Diagnóstico", "Leitura de operação, processos e números."], ["02", "Prioridades", "Definição do que organizar primeiro."], ["03", "Implantação", "Rotinas, responsáveis e controles."], ["04", "Acompanhamento", "Revisão de indicadores e ajustes."]]
    },
    "produto-escalando": {
      type: "Mentoria 03", title: "Escalando Meu Negócio: crescimento sustentável, sem perder o controle.",
      lead: "Uma mentoria para negócios que precisam crescer com mais disciplina comercial, capacidade operacional, automação e indicadores que antecipem riscos e oportunidades.",
      image: "mentoria-escalando-negocio.webp", imageAlt: "Mentoria Escalando Meu Negócio", imageClass: "tall-art",
      cta: "Quero escalar com método", ctaHref: "../contato.html?assunto=mentoria-escalando", back: "../mentorias.html",
      proof: [["Estratégia de escala", "Crescimento com direção"], ["Automação", "Eficiência todos os dias"], ["Controle", "Indicadores e capacidade operacional"]],
      aboutTitle: "Escalar é ampliar capacidade sem transformar crescimento em desorganização.",
      about: ["A expansão exige mais do que aumentar vendas. É preciso avaliar canais, margem, capacidade de entrega, processos, dados e as pessoas que sustentam a operação. Nesta mentoria, o crescimento é tratado como um projeto de gestão, não como uma corrida por volume.", "O objetivo é desenhar um caminho consistente para ampliar o negócio: escolher prioridades, definir metas acompanháveis, fortalecer a operação e identificar onde tecnologia e automação podem reduzir fricção sem afastar a empresa do cliente."],
      benefits: [["Plano de crescimento", "Metas, hipóteses e marcos para ampliar com método."], ["Canais e oferta", "Leitura de aquisição, retenção, margem e potencial de expansão."], ["Automação responsável", "Processos mais leves, dados mais confiáveis e decisões mais rápidas."]],
      audience: [["Para quem é", "Empresas com operação validada que buscam ampliar receita e capacidade de forma estruturada."], ["Como funciona", "Diagnóstico de escala, definição de alavancas prioritárias e encontros para acompanhar execução e aprendizado."]],
      steps: [["01", "Cenário", "Canais, operação, margem e capacidade."], ["02", "Alavancas", "Escolha das prioridades de crescimento."], ["03", "Execução", "Metas, automação e governança."], ["04", "Revisão", "Indicadores, aprendizado e correções."]]
    },
    "produto-blindagem-patrimonial": {
      type: "Mentoria 04", title: "Blindagem Patrimonial: estrutura, prevenção e continuidade.",
      lead: "Uma mentoria educacional para empresários e famílias que desejam compreender alternativas de governança, sucessão e organização patrimonial com visão estratégica.",
      image: "mentoria-blindagem-patrimonial.webp", imageAlt: "Mentoria Blindagem Patrimonial", imageClass: "tall-art",
      cta: "Quero conversar sobre a mentoria", ctaHref: "../contato.html?assunto=mentoria-blindagem-patrimonial", back: "../mentorias.html",
      proof: [["Governança", "Estrutura para decisões seguras"], ["Sucessão", "Continuidade familiar e societária"], ["Proteção estratégica", "Organização com visão de longo prazo"]],
      aboutTitle: "Patrimônio consolidado pede organização, governança e continuidade.",
      about: ["A proteção patrimonial começa por uma leitura responsável da estrutura existente: relações societárias, ativos, obrigações, sucessão, riscos operacionais e objetivos familiares. A mentoria organiza esses temas para que o participante possa formular perguntas melhores e tomar decisões com suporte técnico qualificado.", "O trabalho tem caráter educacional e estratégico. Quando necessário, a Global Invest Brasil orienta a identificação de temas que devem ser aprofundados com profissionais legalmente habilitados, como advogados, contadores e especialistas regulatórios."],
      benefits: [["Mapa patrimonial", "Leitura de estruturas, relações e pontos que merecem atenção."], ["Governança e sucessão", "Discussão de continuidade, responsabilidades e processos decisórios."], ["Plano de encaminhamento", "Prioridades e interface com especialistas habilitados quando aplicável."]],
      audience: [["Para quem é", "Empresários e famílias com patrimônio constituído que desejam organizar temas de continuidade e governança."], ["Como funciona", "Diagnóstico inicial, encontros de aprofundamento e plano de encaminhamentos, respeitando os limites da atividade educacional."]],
      steps: [["01", "Diagnóstico", "Estrutura, objetivos e riscos percebidos."], ["02", "Governança", "Papéis, decisões e continuidade."], ["03", "Encaminhamentos", "Temas técnicos e especialistas necessários."], ["04", "Plano", "Prioridades e próximos passos."]],
      notice: "A Global Invest Brasil não administra patrimônio de terceiros, não presta aconselhamento jurídico, contábil ou regulatório individualizado e não recomenda ativos. A mentoria é educacional; decisões e implementações exigem avaliação de profissionais habilitados."
    }
  };

  const page = pages[root.dataset.landingContent];
  if (!page) return;
  const proof = page.proof.map(([title, text]) => `<div><strong>${title}</strong><span>${text}</span></div>`).join("");
  const benefits = page.benefits.map(([title, text], index) => `<article><span>0${index + 1}</span><h3>${title}</h3><p>${text}</p></article>`).join("");
  const audience = page.audience.map(([label, title, text]) => `<article><span>${label}</span><h2>${title}</h2><p>${text}</p></article>`).join("");
  const steps = page.steps.map(([number, title, text]) => `<article><span>${number}</span><h3>${title}</h3><p>${text}</p></article>`).join("");
  const ctaAttrs = page.ctaExternal ? ' target="_blank" rel="noopener sponsored"' : "";
  root.innerHTML = `
    <section class="product-landing-hero"><div class="container product-landing-hero-grid"><div>
      <a class="publication-back" href="${page.back}">← Voltar</a><span class="eyebrow">${page.type} • Global Invest Brasil</span>
      <h1>${page.title}</h1><p>${page.lead}</p>
      <div class="product-landing-actions"><a class="btn btn-primary" href="${page.ctaHref}"${ctaAttrs}>${page.cta}</a><a class="btn btn-secondary" href="#detalhes">Conheça os detalhes</a></div>
    </div><div class="product-landing-visual"><img class="product-landing-image ${page.imageClass}" src="../assets/images/produtos/${page.image}" alt="${page.imageAlt}" width="1024" height="1536" decoding="async" fetchpriority="high"></div></div></section>
    <section class="product-proof-strip"><div class="container">${proof}</div></section>
    <section class="section product-landing-about" id="detalhes"><div class="container narrow"><span class="kicker">Conheça a proposta</span><h2>${page.aboutTitle}</h2><div class="product-rich-text"><p>${page.about[0]}</p><p>${page.about[1]}</p></div><a class="btn btn-primary" href="${page.ctaHref}"${ctaAttrs}>${page.cta}</a>${page.notice ? `<p class="product-landing-notice">${page.notice}</p>` : ""}</div></section>
    <section class="section product-landing-benefits"><div class="container"><div class="section-heading"><div><span class="kicker">O que você encontrará</span><h2>Conteúdo para compreender, aplicar e avançar.</h2></div><p>Uma experiência educacional estruturada para transformar temas complexos em decisões e próximos passos mais claros.</p></div><div class="product-benefit-grid">${benefits}</div></div></section>
    <section class="section product-audience"><div class="container product-audience-grid">${audience}</div></section>
    <section class="section"><div class="container"><div class="section-heading"><div><span class="kicker">Método de trabalho</span><h2>Da leitura do cenário à ação organizada.</h2></div><p>Uma sequência clara para criar direção, aplicar conhecimento e revisar prioridades.</p></div><div class="product-method-grid">${steps}</div></div></section>
    <section class="section product-final-cta"><div class="container"><span class="kicker">Dê o próximo passo</span><h2>${page.title}</h2><p>Converse com a Global Invest Brasil e entenda como esta solução pode fazer sentido para o seu momento.</p><a class="btn btn-primary" href="${page.ctaHref}"${ctaAttrs}>${page.cta}</a><small>Atendimento individual para esclarecer formato, disponibilidade e condições.</small></div></section>`;
})();
