# Formulário de Pesquisa de Necessidade de Produto Digital

Sistema de briefing comercial/técnico da Global Invest Brasil. Coleta, em etapas, os dados
do cliente e um questionário específico (Site, E-commerce ou Aplicativo), grava tudo no
banco, avisa a equipe por e-mail e disponibiliza a solicitação no painel administrativo
com status, filtros, impressão/PDF e link para envio ao cliente.

Este módulo é independente: não altera nenhuma página, tabela ou funcionalidade
existente do site. Ele só adiciona arquivos novos.

## 1. Estrutura de arquivos

```
/formulario/index.html                    página pública do formulário (estática)
/assets/data/formulario-perguntas.json     TODAS as perguntas (fonte única de dados)
/assets/js/formulario.js                   motor do formulário (etapas, lógica condicional, envio)
/assets/css/formulario.css                 estilos do formulário
/api/briefing.php                          recebe o envio (POST), valida, grava e notifica
/admin/briefings.php                       lista de solicitações + filtros + links para o cliente
/admin/briefing-view.php                   detalhe de uma solicitação (todas as respostas)
/database/globalinvestbr-schema.sql        já inclui a tabela "briefings" (instalação nova)
/migrate-briefings.php                     cria a tabela "briefings" em um banco já existente
```

## 2. Requisitos

Os mesmos do restante do site: PHP com PDO/pgsql e um banco PostgreSQL (Supabase), já
configurados em `app/bootstrap.php`. Nenhuma dependência nova foi adicionada (sem
Composer, sem bibliotecas pesadas).

## 3. Instalação

**Site novo (ainda não instalado):** nada a fazer — `/install.php` já cria a tabela
`briefings` junto com as demais, porque ela foi adicionada a
`database/globalinvestbr-schema.sql`.

**Site que já estava instalado antes desta atualização:** depois de publicar estes
arquivos, faça login em `/admin/` e acesse uma única vez:

```
https://SEUDOMINIO/migrate-briefings.php
```

Isso cria a tabela `briefings` (é seguro rodar mais de uma vez — usa
`CREATE TABLE IF NOT EXISTS`). Depois de confirmar que `/admin/briefings.php` está
funcionando, **apague o arquivo `migrate-briefings.php`** (e remova a linha dele do
`vercel.json`, se estiver usando Vercel+Render).

## 4. E-mail de notificação

Ao receber uma solicitação, o sistema tenta enviar um e-mail para a equipe (função
`mail()` do PHP — sem SMTP externo, igual ao restante do servidor) e, opcionalmente, uma
confirmação ao cliente. O destinatário é o mesmo e-mail já configurado em
**Administração → SEO e Google AdSense → "E-mail público de contato"**. Se o servidor
não tiver `mail()` configurado, o formulário continua funcionando normalmente — o envio
de e-mail é best-effort e nunca bloqueia o registro da solicitação. Nesse caso, use a
lista em `/admin/briefings.php` para acompanhar as novas solicitações.

## 5. URLs disponíveis

| URL | O que faz |
|---|---|
| `/formulario/` | Deixa o cliente escolher entre Site, E-commerce ou Aplicativo |
| `/formulario/?produto=site` | Abre direto o questionário de Site |
| `/formulario/?produto=ecommerce` | Abre direto o questionário de E-commerce |
| `/formulario/?produto=aplicativo` | Abre direto o questionário de Aplicativo |
| `/admin/briefings.php` | Lista de solicitações, filtros e links para copiar/enviar |
| `/admin/briefing-view.php?id=N` | Detalhe completo de uma solicitação |

A página pública tem `<meta name="robots" content="noindex,follow">` — não aparece no
Google, só é acessada por quem recebe o link. O restante do site não foi alterado.

## 6. Como enviar o link ao cliente

Em `/admin/briefings.php` há um cartão "Links para enviar ao cliente" com os 4 links
prontos (geral + um por produto). O botão **Copiar link** copia a URL completa; o botão
**Enviar por WhatsApp** abre o WhatsApp Web/App com uma mensagem pronta — basta escolher
o contato.

## 7. Como alterar as perguntas

Todas as perguntas (dos 3 produtos e dos dados do cliente) ficam em **um único arquivo**:

```
assets/data/formulario-perguntas.json
```

Ele é lido tanto pelo formulário público quanto pelo painel administrativo — editar ali
é suficiente, não precisa mexer em código. Cada pergunta é um objeto assim:

```json
{ "key": "possui_site", "label": "A empresa já possui um site?", "type": "yesno" }
```

- `key`: identificador único da pergunta dentro do produto (sem espaços/acentos).
- `label`: o texto que aparece para o cliente e no painel.
- `type`: `text`, `textarea`, `email`, `tel`, `url`, `date`, `select`, `checkbox` (múltipla
  escolha) ou `yesno` (Sim/Não).
- `options`: lista de opções, usado em `select` e `checkbox`.
- `required`: `true` torna a pergunta obrigatória.
- `showIf`: exibe a pergunta apenas se outra pergunta tiver determinada resposta. Exemplo:
  `"showIf": { "key": "possui_site", "equals": "Sim" }` ou, para checkbox,
  `"showIf": { "key": "paginas_desejadas", "includes": "Outras" }`.

Para **adicionar uma pergunta**, copie um bloco parecido dentro do `"fields"` da etapa
desejada. Para **remover**, apague o bloco. Para **criar uma nova etapa**, adicione um
novo objeto `{ "title": "...", "fields": [...] }` dentro de `"steps"`. Para **adicionar
um novo tipo de produto**, copie o bloco de um produto existente dentro de `"products"`
e ajuste `label`, `description` e `steps` — o formulário e o painel passam a exibi-lo
automaticamente.

Depois de editar o JSON, valide o arquivo (por exemplo em jsonlint.com) antes de publicar
— um JSON inválido impede o carregamento do formulário.

## 8. Como alterar textos fixos

- Título e texto de apresentação: em `formulario/index.html`, dentro de `<section class="form-hero">`.
- Mensagem de confirmação final: em `assets/js/formulario.js`, função `renderSuccess`.
- Texto do checkbox de LGPD: em `assets/js/formulario.js`, função `renderReviewStep`.
- Mensagem padrão do botão "Enviar por WhatsApp": em `admin/briefings.php`.

## 9. Como alterar a logo

A logo usada no topo do formulário é `assets/images/logo-globalinvestbr-horizontal.png`
(a mesma logomarca oficial usada no restante do site). Para trocar, substitua esse
arquivo ou aponte para outro em `formulario/index.html` (tag `<img>` dentro de
`<header class="form-header">`). Não é adicionado nenhum texto ao lado da logo — o nome
"Global Invest Brasil" e o texto "Educação e Negócios" já fazem parte da própria imagem.

## 10. Backup

Antes de qualquer alteração no servidor de produção, faça backup do banco (Supabase:
Database → Backups, ou `pg_dump`) e dos arquivos (`app/config.php`, se existir, nunca
deve ser commitado nem perdido).

## 11. Segurança

- Validação de obrigatoriedade e formato de e-mail no navegador **e** no servidor
  (`api/briefing.php`).
- Todas as consultas usam **prepared statements** (PDO) — sem concatenação de SQL.
- Saída no painel sempre passa por `h()` (escape HTML) — sem risco de XSS.
- Campo honeypot invisível (`website`) descarta envios de robôs simples.
- Limite de 5 envios por IP a cada 30 minutos (`api/briefing.php`).
- Limite de tamanho do payload de respostas (200 KB) contra abuso.
- Sessão do painel administrativo protegida por CSRF token (`csrf()`/`verify_csrf()`,
  já existente no site).
- Use sempre HTTPS em produção (já garantido pela Vercel/Render).

## 12. Solução de problemas

- **"Não foi possível registrar o levantamento"**: confira se a tabela `briefings` existe
  (rode `/migrate-briefings.php` se o site já estava instalado antes desta atualização).
- **Formulário fica em "Carregando formulário..."**: o navegador não conseguiu buscar
  `/assets/data/formulario-perguntas.json` — confira se o arquivo foi publicado e se o
  JSON é válido.
- **E-mail de notificação não chega**: verifique se `mail()` está habilitado no
  servidor/hospedagem; na dúvida, acompanhe as solicitações direto em
  `/admin/briefings.php`, que sempre funciona independente do e-mail.
- **Perguntas de um produto não aparecem**: confira a sintaxe do JSON em
  `assets/data/formulario-perguntas.json` (vírgulas, chaves e colchetes).
