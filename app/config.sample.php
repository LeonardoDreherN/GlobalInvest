<?php
// Usado apenas quando as variáveis de ambiente DB_HOST/DB_NAME/DB_USER/DB_PASSWORD
// não estão definidas (ex.: hospedagem tradicional sem suporte a variáveis de ambiente).
// No Render/Supabase, configure essas variáveis no painel do serviço em vez de editar este arquivo.
return [
    'db_host' => 'db.xxxxxxxxxxxx.supabase.co',
    'db_port' => '5432',
    'db_name' => 'postgres',
    'db_user' => 'postgres',
    'db_password' => 'ALTERE_NO_INSTALADOR',
    'db_sslmode' => 'require',
    'site_url' => 'https://globalinvestbr.com',
];
