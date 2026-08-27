# Deploy: Vercel + Render + Supabase

Esse é o guia para a nova arquitetura, em substituição à Hostinger. O site estático
(HTML/CSS/JS) fica na Vercel, o backend PHP (painel, formulários, sitemap dinâmico)
fica no Render, e o banco de dados (agora PostgreSQL, não mais MySQL) fica no Supabase.

## Ordem de implantação (importante seguir nessa ordem)

### 1. Supabase (banco de dados)

1. Crie um projeto em supabase.com.
2. Em **Project Settings → Database**, anote: host, porta (geralmente `5432`), nome do
   banco (`postgres`), usuário (`postgres`) e a senha definida na criação do projeto.
3. Não precisa rodar o schema manualmente — o `/install.php` (passo 3) faz isso
   automaticamente. Se preferir rodar você mesmo, use o **SQL Editor** do Supabase e
   cole o conteúdo de `database/globalinvestbr-schema.sql`.

### 2. Render (backend PHP)

1. Suba este repositório no GitHub.
2. No Render, crie um **Web Service** apontando para o repositório — ele detecta o
   `Dockerfile` automaticamente.
3. Em **Environment**, configure as variáveis:
   - `DB_HOST` — host do Supabase
   - `DB_PORT` — `5432`
   - `DB_NAME` — `postgres`
   - `DB_USER` — `postgres`
   - `DB_PASSWORD` — a senha do Supabase
   - `DB_SSLMODE` — `require`
   - `SITE_URL` — a URL final do site (ex.: `https://globalinvestbr.com`)
4. Faça o deploy. Quando terminar, acesse `https://SEU-SERVICO.onrender.com/install.php`
   e crie o primeiro administrador (nome, e-mail, senha). Isso cria as tabelas no
   Supabase e o usuário admin.
5. Teste `https://SEU-SERVICO.onrender.com/api/health` — deve responder `"ok":true`.
6. **Remova ou bloqueie `install.php`** depois de testado (ele já se bloqueia sozinho
   assim que existe pelo menos um administrador, mas é mais seguro remover o arquivo).

### 3. Vercel (site estático)

1. Edite `vercel.json` e troque **todas** as ocorrências de
   `https://SEU-BACKEND.onrender.com` pela URL real do seu serviço Render (passo 2).
   Sem isso, o formulário de contato, a newsletter, o painel admin e o sitemap não
   vão funcionar — só as páginas estáticas.
2. Faça commit dessa alteração e importe o repositório na Vercel normalmente
   (framework: "Other"/estático, sem build command).
3. Depois do deploy, teste:
   - Uma página estática, tipo `/produtos.html` — deve carregar direto pela Vercel.
   - `/admin/login.php` — deve carregar o painel (a Vercel repassa pro Render).
   - O formulário de contato em `/contato.html` — deve gravar no Supabase.

### 4. Domínio

Aponte o domínio `globalinvestbr.com` para a Vercel (é ela quem serve o site pros
visitantes). O Render fica com uma URL própria (`onrender.com`), usada só
internamente pelas rotas que a Vercel repassa — o visitante nunca acessa o Render
diretamente.

## O que muda em relação à Hostinger

- O banco agora é **PostgreSQL**, não MySQL — `database/globalinvestbr-schema.sql`
  já foi convertido.
- A conexão com o banco agora vem de **variáveis de ambiente** (`DB_HOST`, etc.), não
  mais de um arquivo `app/config.php` com a senha em texto puro dentro do repositório
  — por isso `app/config.php` está no `.gitignore` e nunca deve ser commitado.
- O arquivo `app/config.php` continua funcionando como alternativa, caso algum dia
  vocês voltem a usar uma hospedagem tradicional sem variáveis de ambiente.

## Limitação conhecida

O `.htaccess` (regras de reescrita de URL) só é lido pelo Apache, que roda dentro do
container do Render — a Vercel não usa `.htaccess`. As rotas dinâmicas
(`/publicacao/slug`, `/blog/slug`, `/api/*`, `/admin/*`, `/sitemap.xml`) foram
replicadas manualmente no `vercel.json` como *rewrites* para o Render. Se novas rotas
dinâmicas forem adicionadas no futuro, `vercel.json` precisa ser atualizado junto.
