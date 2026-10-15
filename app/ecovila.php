<?php
declare(strict_types=1);
/*
 * Ecovila Mundo Verde (https://www.ecovilamundoverde.com.br): terceiro site
 * administrado por este painel. Os lotes ficam na tabela eco_lots, editados em
 * /admin/ecovila.php e servidos ao site por /api/eco-lots. Os contatos do site
 * entram na tabela contacts com site = 'ecovila' (via /api/contacts).
 */
require_once __DIR__ . '/bootstrap.php';

/** Endereços de onde o site da Ecovila chama a API (CORS). */
const ECO_ORIGINS = ['https://www.ecovilamundoverde.com.br', 'https://ecovilamundoverde.com.br', 'https://ecovila-mundo-verde.vercel.app'];
// Endereço público do site, usado nos links e nas prévias de imagem do admin.
// Troque para 'https://www.ecovilamundoverde.com.br' quando o domínio estiver apontado para a Vercel.
const ECO_SITE_URL = 'https://ecovila-mundo-verde.vercel.app';

const ECO_STATUSES = ['disponivel' => 'Disponível', 'reservado' => 'Reservado', 'vendido' => 'Vendido'];

/** Campos da ficha do lote: coluna => rótulo. Cada um tem também a coluna "<campo>_pending". */
const ECO_SPEC_FIELDS = [
    'location' => 'Localização',
    'area' => 'Área',
    'dimensions' => 'Dimensões',
    'topography' => 'Topografia',
    'model' => 'Sugestão de construção',
];

/**
 * Cria a tabela e cadastra os 18 lotes com o conteúdo publicado no site, apenas
 * se a tabela ainda não existir. Custa uma consulta rápida nas demais vezes.
 */
function eco_ensure_schema(): void {
    $pdo = db();
    if ($pdo->query("SELECT to_regclass('public.eco_lots') IS NOT NULL")->fetchColumn()) return;
    $pdo->exec("CREATE TABLE IF NOT EXISTS eco_lots (
        id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        number SMALLINT NOT NULL UNIQUE CHECK (number > 0),
        title VARCHAR(80) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'disponivel' CHECK (status IN ('disponivel','reservado','vendido')),
        description TEXT NOT NULL DEFAULT '',
        image_url VARCHAR(500) NOT NULL DEFAULT '',
        location VARCHAR(200) NOT NULL DEFAULT '', location_pending SMALLINT NOT NULL DEFAULT 0,
        area VARCHAR(200) NOT NULL DEFAULT '', area_pending SMALLINT NOT NULL DEFAULT 0,
        dimensions VARCHAR(200) NOT NULL DEFAULT '', dimensions_pending SMALLINT NOT NULL DEFAULT 0,
        topography VARCHAR(200) NOT NULL DEFAULT '', topography_pending SMALLINT NOT NULL DEFAULT 0,
        model VARCHAR(200) NOT NULL DEFAULT '', model_pending SMALLINT NOT NULL DEFAULT 0,
        is_visible SMALLINT NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    // Conteúdo que estava fixo no script.js do site no momento da integração.
    $insert = $pdo->prepare("INSERT INTO eco_lots (number, title, description, image_url, location, location_pending, area, area_pending,
        dimensions, dimensions_pending, topography, topography_pending, model, model_pending)
        VALUES (?,?,?,?,'Em definição no mapa técnico',1,'Em levantamento',1,'A confirmar',1,'Levantamento em andamento',1,?,0)
        ON CONFLICT (number) DO NOTHING");
    for ($n = 1; $n <= 18; $n++) {
        $even = $n % 2 === 0;
        $insert->execute([
            $n, 'Lote ' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'Um lote integrado à implantação de baixíssima densidade da Ecovila, planejado para valorizar privacidade, natureza e a relação com a paisagem.',
            $even ? 'assets/casa-frente.jpg' : 'assets/casa-verde.jpg',
            $even ? 'Casa Horizonte' : 'Casa Jardim',
        ]);
    }
}

/** URL utilizável no admin para pré-visualizar a imagem (caminhos relativos apontam para o site). */
function eco_image_preview(string $url): string {
    if ($url === '') return '';
    if (preg_match('#^https?://#i', $url)) return $url;
    return ECO_SITE_URL . '/' . ltrim($url, '/');
}
