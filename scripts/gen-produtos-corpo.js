/* Gera database/produtos-corpo.json com os corpos (Markdown) limpos dos 5
   produtos que tinham HTML proprio. Rode: node scripts/gen-produtos-corpo.js */
const fs = require("fs");
const path = require("path");

const bodies = {
  "produto-site": `## Não é apenas um site. É a estrutura pública da sua decisão de marca.

Quando uma empresa comunica serviços complexos sem hierarquia, o visitante não entende a proposta e a oportunidade se perde antes do contato. O site precisa organizar a narrativa, reduzir dúvidas e dar segurança.

Transformamos conhecimento, diferenciais e ofertas em páginas que facilitam a leitura e tornam o próximo passo natural.

## O que construímos para a sua operação digital

O projeto é desenhado de acordo com o estágio, o público e a rotina comercial da empresa.

- **Estratégia e arquitetura**: Mapeamento de objetivos, públicos, páginas, prioridades e caminhos de navegação.
- **Conteúdo que orienta**: Textos, argumentos, provas e chamadas para ação com linguagem adequada à sua marca.
- **Design e experiência**: Interface responsiva, legível e consistente, construída para computador e celular.
- **Conversão e contato**: Formulários, rotas de atendimento e pontos de captura organizados para gerar oportunidades.
- **Desempenho e SEO**: Estrutura técnica para indexação, velocidade e leitura correta pelos mecanismos de busca.
- **Evolução contínua**: Uma base que pode receber novas páginas, produtos, conteúdo e integrações ao longo do tempo.

## Como funciona

Começamos pela estratégia, passamos pela arquitetura e pelo conteúdo, evoluímos para design e implementação e encerramos com a publicação validada.

1. **Diagnóstico**: Negócio, público e objetivo
2. **Estrutura**: Mapa de páginas e conteúdos
3. **Construção**: Design, tecnologia e integrações
4. **Publicação**: Validação e evolução contínua`,

  "produto-ecommerce": `## Uma vitrine sem processo não sustenta crescimento.

Um e-commerce eficiente precisa conectar experiência do cliente e operação interna. Produto sem informação, checkout confuso, meios de pagamento mal definidos e atendimento desconectado criam perdas que não aparecem apenas no relatório.

Desenhamos o fluxo completo para que a venda seja compreensível, mensurável e replicável.

## Uma operação de comércio digital preparada para evoluir

O escopo é definido conforme catálogo, modelo de entrega e metas comerciais.

- **Catálogo estruturado**: Organização de categorias, páginas de produto, variações e argumentos de compra.
- **Checkout objetivo**: Jornada de pagamento com orientação clara, segurança e menor atrito possível.
- **Integrações essenciais**: Conexões planejadas com pagamentos, logística, estoque e canais de atendimento.
- **Operação e pedidos**: Processos para receber, acompanhar e atender cada pedido com mais consistência.
- **Indicadores comerciais**: Leitura de conversão, ticket, recorrência e sinais que ajudam a priorizar ações.
- **Base de crescimento**: Estrutura preparada para campanhas, novos produtos e evolução dos canais de venda.

## Do modelo comercial à rotina operacional

Organizamos a solução de acordo com o modelo de negócio e validamos os pontos que impactam conversão, entrega e gestão.

1. **Estratégia**: Oferta, público e operação
2. **Jornada**: Catálogo, compra e atendimento
3. **Integrações**: Pagamentos e fluxos internos
4. **Otimização**: Dados, conversão e evolução`,

  "produto-app-dedicado": `## Não comece pela tela. Comece pelo problema que vale resolver.

Planilhas dispersas, processos repetitivos e sistemas que não conversam entre si consomem tempo e reduzem a qualidade da operação. Um aplicativo dedicado deve simplificar uma decisão, uma entrega ou uma experiência concreta.

Por isso, cada projeto começa pela compreensão do processo, dos usuários e dos resultados esperados.

## Uma solução digital construída a partir da sua realidade

Definimos o produto em etapas para controlar investimento, prioridade e evolução.

- **Descoberta e requisitos**: Entendimento de processos, usuários, regras de negócio e oportunidades de melhoria.
- **Jornadas e protótipos**: Mapeamento de fluxos e telas antes da construção, para validar decisões com clareza.
- **Experiência do usuário**: Interface objetiva, acessível e coerente com a rotina de quem irá usar a solução.
- **Desenvolvimento**: Construção de funcionalidades prioritárias com base técnica organizada e testável.
- **Integrações e dados**: Conexões com sistemas existentes, cadastros, painéis e automações necessárias.
- **Evolução do produto**: Um caminho estruturado para incluir novos recursos conforme o uso gera aprendizados.

## Clareza antes do código. Validação antes da expansão.

O trabalho avança por decisões verificáveis, reduzindo desperdícios e concentrando esforço no que produz resultado operacional.

1. **Diagnóstico**: Processos e oportunidade
2. **Protótipo**: Fluxos e experiência
3. **Produto inicial**: Funcionalidades prioritárias
4. **Evolução**: Dados e novas entregas`,

  "produto-livro": `## Quanto da sua vida está sendo consumido por uma rotina que não leva você adiante?

Trabalhar muito virou sinônimo de mérito. Mas horas acumuladas não garantem clareza, prosperidade ou liberdade. Sem direção, o esforço pode apenas manter você ocupado, e distante da vida que deseja construir.

Este livro propõe uma mudança de perspectiva: substituir o automatismo por escolhas conscientes, usar melhor os recursos disponíveis e transformar conhecimento, tecnologia e disciplina em instrumentos de autonomia.

## Uma nova forma de enxergar trabalho, dinheiro, tempo e propósito

Mais do que apresentar respostas prontas, o livro convida você a rever decisões e reconhecer possibilidades que uma rotina acelerada costuma esconder.

- **Clareza sobre prioridades**: Diferencie movimento de progresso e concentre energia naquilo que realmente produz valor.
- **Trabalho mais inteligente**: Reflita sobre estratégia, sistemas e decisões capazes de reduzir dependência do esforço contínuo.
- **Tecnologia como aliada**: Entenda como novas ferramentas podem ampliar capacidade sem consumir toda a sua agenda.
- **Disciplina com direção**: Transforme constância em avanço, com objetivos e critérios mais claros para agir.
- **Liberdade possível**: Explore caminhos para construir maior autonomia financeira, profissional e geográfica.
- **Resultados sustentáveis**: Busque crescimento conectado a propósito, responsabilidade e qualidade de vida.

## Para quem é este livro

- **Empreendedores sobrecarregados**: Para quem criou um negócio, mas percebeu que se tornou prisioneiro da própria operação.
- **Profissionais em busca de autonomia**: Para quem quer repensar a carreira e construir escolhas com mais liberdade e intenção.
- **Pessoas em momento de mudança**: Para quem sente que trabalha muito, mas ainda procura direção, equilíbrio e significado.

## Um caminho de leitura

1. **Observe**: Reconheça padrões que consomem tempo sem gerar avanço
2. **Questione**: Reavalie crenças sobre produtividade, dinheiro e sucesso
3. **Escolha**: Defina prioridades coerentes com a vida que deseja
4. **Construa**: Use estratégia, disciplina e tecnologia para evoluir

## Sobre o que é o livro?

A obra apresenta reflexões e aprendizados sobre trabalho inteligente, liberdade financeira e geográfica, estratégia, disciplina, tecnologia e propósito.

## Este livro promete enriquecimento rápido?

Não. O livro tem caráter educacional e reflexivo. Não oferece fórmula de enriquecimento nem garante resultados financeiros.

## Para quem a leitura é indicada?

Para empreendedores, profissionais e pessoas que desejam rever sua relação com trabalho, tempo, resultados e qualidade de vida.

## Onde a compra é realizada?

Ao clicar no botão, você será direcionado ao checkout oficial da loja, onde encontrará as informações da oferta e da entrega.`,

  "produto-ideia-ao-lucro": `## Faturar não é o mesmo que construir lucro

Muitas empresas trabalham mais, vendem mais e ainda assim não conseguem identificar por que o caixa não evolui. O problema quase sempre está na distância entre a informação disponível e o critério usado para interpretá-la.

Este e-book organiza uma leitura simples, mas tecnicamente consistente, dos elementos que tornam um negócio viável: receita, estrutura de custos, margem, capacidade operacional, risco e retorno do esforço empregado.

## Um método para enxergar onde o negócio ganha, e onde perde, valor

O conteúdo foi estruturado para quem precisa avaliar um cenário com clareza antes de ampliar investimentos, equipe, estoque ou canais de venda.

- **Leitura da operação**: Mapeie a origem da receita, os custos relevantes e os pontos que consomem tempo, dinheiro e atenção.
- **Margem e rentabilidade**: Entenda quais produtos, serviços ou clientes realmente contribuem para o resultado.
- **Decisões prioritárias**: Organize ações de melhoria de acordo com impacto, urgência e capacidade de execução.
- **Indicadores de controle**: Escolha indicadores que ajudam a acompanhar evolução sem criar uma rotina burocrática.
- **Crescimento responsável**: Reconheça quando é hora de expandir e quando o melhor movimento é consolidar a estrutura.
- **Plano aplicável**: Transforme o diagnóstico em um plano de trabalho realista para os próximos ciclos.

## Critério antes de velocidade

O propósito não é vender uma promessa de rentabilidade. É entregar uma estrutura de análise para que o empreendedor compreenda seu contexto, reconheça riscos e conduza o negócio com responsabilidade.

1. **Diagnosticar**: Entenda o cenário atual
2. **Medir**: Organize os números relevantes
3. **Decidir**: Defina prioridades e responsáveis
4. **Acompanhar**: Revise o plano e a evolução`,
};

const out = path.join(__dirname, "..", "database", "produtos-corpo.json");
fs.writeFileSync(out, JSON.stringify(bodies, null, 2) + "\n");
console.log("ok ->", path.relative(path.join(__dirname, ".."), out), "|", Object.keys(bodies).length, "produtos");
