<?php
declare(strict_types=1);
/*
 * Uso ÚNICO. Cria a tabela "briefings" (Formulário de Pesquisa de Necessidade
 * de Produto Digital) em bancos que já foram instalados antes desta função
 * existir. Se o banco for instalado do zero via /install.php, esta tabela já
 * é criada automaticamente pelo schema — este script não é necessário.
 *
 * Exige login no /admin/. Depois de rodar (uma vez), APAGUE este arquivo.
 */
require_once __DIR__ . '/app/auth.php';
require_admin();
header('Content-Type: text/plain; charset=utf-8');

$pdo = db();
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS briefings (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  reference_code VARCHAR(20) NOT NULL UNIQUE,
  product VARCHAR(20) NOT NULL CHECK (product IN ('site','ecommerce','aplicativo')),
  full_name VARCHAR(160) NOT NULL,
  company VARCHAR(160),
  role_title VARCHAR(120),
  document_number VARCHAR(30),
  email VARCHAR(190) NOT NULL,
  whatsapp VARCHAR(40) NOT NULL,
  city VARCHAR(120),
  state VARCHAR(80),
  country VARCHAR(80),
  current_site VARCHAR(255),
  instagram VARCHAR(160),
  linkedin VARCHAR(160),
  other_social VARCHAR(255),
  how_found VARCHAR(120),
  answers JSONB NOT NULL DEFAULT '{}'::jsonb,
  status VARCHAR(30) NOT NULL DEFAULT 'novo' CHECK (status IN (
    'novo','em_analise','contato_realizado','orcamento_enviado','aguardando_cliente',
    'aprovado','nao_aprovado','projeto_iniciado','concluido'
  )),
  internal_notes TEXT,
  consent_at TIMESTAMP NOT NULL,
  source_url VARCHAR(255),
  ip_address VARCHAR(45),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_briefings_status_created ON briefings (status, created_at);
CREATE INDEX IF NOT EXISTS idx_briefings_product ON briefings (product);
CREATE INDEX IF NOT EXISTS idx_briefings_email ON briefings (email);
CREATE INDEX IF NOT EXISTS idx_briefings_ip_created ON briefings (ip_address, created_at);
SQL;

try {
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) as $statement) {
        if (trim($statement) !== '') $pdo->exec($statement);
    }
    echo "OK: tabela 'briefings' pronta.\n";
    echo "Acesse /admin/briefings.php para conferir. Depois APAGUE este arquivo (migrate-briefings.php).\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
