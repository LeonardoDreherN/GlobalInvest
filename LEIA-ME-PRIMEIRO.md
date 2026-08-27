# Global Invest Brasil — pacote de implantação Hostinger

> **Este projeto migrou para Vercel + Render + Supabase.** Siga `DEPLOY-RENDER-VERCEL-SUPABASE.md`. O arquivo `database/globalinvestbr-schema.sql` agora é PostgreSQL — as instruções de MySQL/phpMyAdmin abaixo estão desatualizadas e não funcionam mais com esse arquivo. Mantido aqui só como referência histórica.

Este é um pacote limpo e único para o site, painel administrativo, banco MySQL, SEO e preparação para Google AdSense.

## Implantação segura

1. No Gerenciador de Arquivos da Hostinger, entre em `public_html`.
2. Crie uma pasta de segurança, por exemplo `backup-antes-2026-08-21`, e mova para ela os arquivos atuais. Não apague o backup no primeiro momento.
3. Envie **este arquivo ZIP** para dentro de `public_html` e use **Extrair**. Os arquivos devem aparecer diretamente em `public_html`, nunca dentro de uma pasta adicional.
4. Acesse `https://globalinvestbr.com/install.php`.
5. Preencha servidor `localhost`, nome completo do banco, usuário completo, senha do MySQL e os dados do primeiro administrador. Clique em **Concluir instalação**.
6. Acesse `https://globalinvestbr.com/admin/` e entre com o e-mail e a senha definidos no passo anterior.
7. Teste `https://globalinvestbr.com/api/health`. O resultado deve mostrar `"ok":true`.
8. Depois do teste, remova `install.php` do `public_html`.

## Banco de dados

O instalador executa automaticamente `database/globalinvestbr-schema.sql`. Não é necessário criar tabelas manualmente no phpMyAdmin.

Se preferir importar manualmente: abra phpMyAdmin, selecione o banco e importe o arquivo `database/globalinvestbr-schema.sql`; depois acesse `/install.php` somente para criar o administrador e o arquivo de conexão.

**Se o site já estava instalado antes desta atualização**, é preciso reimportar `database/globalinvestbr-schema.sql` pelo phpMyAdmin (Importar → selecionar o arquivo → Executar) para criar as novas tabelas `login_attempts`, `newsletter_subscribers` e `cookie_consents`. O arquivo usa `CREATE TABLE IF NOT EXISTS`, então reimportar é seguro e não apaga dados existentes.

## Google AdSense

O pacote já contém:

- carregador seguro de AdSense, que só carrega o script do Google depois que o visitante aceita cookies no banner de consentimento (LGPD);
- `ads.txt` modelo;
- sitemap dinâmico e `robots.txt`;
- telas no painel para ID de publicador e verificação do Search Console.

Após ter a conta aprovada, acesse **Administração → SEO e Google AdSense**, informe seu ID `ca-pub-...`, marque a ativação e salve. Em seguida substitua o conteúdo de `ads.txt` pela linha oficial fornecida pelo Google.

Nunca invente um ID de AdSense: ele precisa ser o ID real da conta aprovada.

## Segurança do login administrativo

O login em `/admin/login.php` agora bloqueia por 15 minutos qualquer IP ou e-mail que errar a senha 5 vezes seguidas (registrado na tabela `login_attempts`). Não é necessário configurar nada.

## Newsletter (isca digital)

Um formulário de e-mail no rodapé do site (presente em todas as páginas) grava inscrições na tabela `newsletter_subscribers`, independente do formulário de contato. Consulte os e-mails direto pelo phpMyAdmin por enquanto — não há tela própria no painel administrativo ainda.
