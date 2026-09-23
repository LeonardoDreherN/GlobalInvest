<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Método não permitido'], 405);

const BRIEFING_ALLOWED_PRODUCTS = ['site', 'ecommerce', 'aplicativo'];
const BRIEFING_MAX_PER_IP_30MIN = 5;
const BRIEFING_ANSWERS_MAX_BYTES = 200000;

function briefing_reference_code(): string {
    return 'GIB-' . strtoupper(bin2hex(random_bytes(4)));
}

function briefing_clean_string($value, int $maxLength = 2000): string {
    $s = trim((string) $value);
    $s = str_replace("\0", '', $s);
    if (function_exists('mb_substr')) $s = mb_substr($s, 0, $maxLength);
    else $s = substr($s, 0, $maxLength);
    return $s;
}

function briefing_clean_answers($answers): array {
    $clean = [];
    if (!is_array($answers)) return $clean;
    foreach ($answers as $key => $value) {
        $key = preg_replace('/[^a-z0-9_]/i', '', (string) $key);
        if ($key === '') continue;
        if (is_array($value)) {
            $clean[$key] = array_values(array_map(fn($v) => briefing_clean_string($v, 300), array_slice($value, 0, 40)));
        } else {
            $clean[$key] = briefing_clean_string($value, 4000);
        }
    }
    return $clean;
}

try {
    $d = request_json();

    // Honeypot: bots preenchem este campo invisível. Responde como sucesso sem gravar nada.
    if (!empty($d['website'])) json_response(['ok' => true, 'reference' => briefing_reference_code()]);

    $product = strtolower(briefing_clean_string($d['product'] ?? '', 20));
    if (!in_array($product, BRIEFING_ALLOWED_PRODUCTS, true)) {
        json_response(['error' => 'Selecione o produto desejado (site, e-commerce ou aplicativo).'], 422);
    }

    $client = is_array($d['client'] ?? null) ? $d['client'] : [];
    $fullName = briefing_clean_string($client['full_name'] ?? '', 160);
    $email = briefing_clean_string($client['email'] ?? '', 190);
    $whatsapp = briefing_clean_string($client['whatsapp'] ?? '', 40);

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $whatsapp === '') {
        json_response(['error' => 'Preencha nome completo, e-mail e WhatsApp/telefone válidos.'], 422);
    }
    if (empty($d['consent'])) {
        json_response(['error' => 'É necessário aceitar o tratamento dos dados para enviar o formulário.'], 422);
    }

    $answers = briefing_clean_answers($d['answers'] ?? []);
    if (strlen(json_encode($answers, JSON_UNESCAPED_UNICODE)) > BRIEFING_ANSWERS_MAX_BYTES) {
        json_response(['error' => 'O conteúdo enviado é grande demais. Reduza o texto de alguma resposta e tente novamente.'], 422);
    }

    $pdo = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    $rateCheck = $pdo->prepare("SELECT COUNT(*) FROM briefings WHERE ip_address = ? AND created_at > (NOW() - INTERVAL '30 minutes')");
    $rateCheck->execute([$ip]);
    if ((int) $rateCheck->fetchColumn() >= BRIEFING_MAX_PER_IP_30MIN) {
        json_response(['error' => 'Muitas solicitações em pouco tempo. Aguarde alguns minutos e tente novamente.'], 429);
    }

    $company = briefing_clean_string($client['company'] ?? '', 160);
    $roleTitle = briefing_clean_string($client['role_title'] ?? '', 120);
    $documentNumber = briefing_clean_string($client['document_number'] ?? '', 30);
    $city = briefing_clean_string($client['city'] ?? '', 120);
    $state = briefing_clean_string($client['state'] ?? '', 80);
    $country = briefing_clean_string($client['country'] ?? '', 80);
    $currentSite = briefing_clean_string($client['current_site'] ?? '', 255);
    $instagram = briefing_clean_string($client['instagram'] ?? '', 160);
    $linkedin = briefing_clean_string($client['linkedin'] ?? '', 160);
    $otherSocial = briefing_clean_string($client['other_social'] ?? '', 255);
    $howFound = briefing_clean_string($client['how_found'] ?? '', 60);
    if ($howFound === 'Outro' && !empty($client['how_found_other'])) {
        $howFound = 'Outro: ' . briefing_clean_string($client['how_found_other'], 120);
    }

    $reference = briefing_reference_code();
    $now = now_utc();

    $insert = $pdo->prepare(
        'INSERT INTO briefings
            (reference_code, product, full_name, company, role_title, document_number, email, whatsapp,
             city, state, country, current_site, instagram, linkedin, other_social, how_found,
             answers, status, consent_at, source_url, ip_address, created_at, updated_at)
         VALUES
            (:reference_code, :product, :full_name, :company, :role_title, :document_number, :email, :whatsapp,
             :city, :state, :country, :current_site, :instagram, :linkedin, :other_social, :how_found,
             :answers, :status, :consent_at, :source_url, :ip_address, :created_at, :updated_at)
         RETURNING id'
    );

    $tries = 0;
    do {
        try {
            $insert->execute([
                ':reference_code' => $reference, ':product' => $product, ':full_name' => $fullName,
                ':company' => $company, ':role_title' => $roleTitle, ':document_number' => $documentNumber,
                ':email' => $email, ':whatsapp' => $whatsapp, ':city' => $city, ':state' => $state,
                ':country' => $country, ':current_site' => $currentSite, ':instagram' => $instagram,
                ':linkedin' => $linkedin, ':other_social' => $otherSocial, ':how_found' => $howFound,
                ':answers' => json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':status' => 'novo', ':consent_at' => $now,
                ':source_url' => briefing_clean_string($d['source_url'] ?? ('/formulario/?produto=' . $product), 255),
                ':ip_address' => briefing_clean_string($ip, 45), ':created_at' => $now, ':updated_at' => $now,
            ]);
            break;
        } catch (Throwable $e) {
            $tries++;
            if ($tries >= 3 || strpos($e->getMessage(), 'reference_code') === false) throw $e;
            $reference = briefing_reference_code();
        }
    } while ($tries < 3);

    $row = $insert->fetch();
    $id = $row['id'] ?? null;

    briefing_send_notifications($product, $fullName, $email, $whatsapp, $company, $reference, (int) ($id ?? 0));

    json_response(['ok' => true, 'reference' => $reference]);
} catch (Throwable $e) {
    json_response(['error' => 'Não foi possível registrar o levantamento. Tente novamente mais tarde.'], 500);
}

function briefing_send_notifications(string $product, string $name, string $email, string $whatsapp, string $company, string $reference, int $id): void {
    try {
        $productLabels = ['site' => 'Site', 'ecommerce' => 'E-commerce', 'aplicativo' => 'Aplicativo'];
        $productLabel = $productLabels[$product] ?? $product;
        $to = setting('contact_email', 'contato@globalinvestbrasil.com');
        if ($to && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $subject = '=?UTF-8?B?' . base64_encode('Novo levantamento de projeto - ' . $productLabel . ' - ' . $name) . '?=';
            $adminUrl = base_url() . '/admin/briefing-view.php?id=' . $id;
            $body = "Novo levantamento recebido pelo formulário do site.\n\n"
                . "Referência: {$reference}\n"
                . "Produto: {$productLabel}\n"
                . "Nome: {$name}\n"
                . ($company !== '' ? "Empresa: {$company}\n" : '')
                . "E-mail: {$email}\n"
                . "WhatsApp: {$whatsapp}\n\n"
                . "Ver detalhes no painel: {$adminUrl}\n";
            $headers = "From: Global Invest Brasil <" . $to . ">\r\nContent-Type: text/plain; charset=UTF-8";
            @mail($to, $subject, $body, $headers);
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $subjectClient = '=?UTF-8?B?' . base64_encode('Recebemos o seu levantamento - Global Invest Brasil') . '?=';
            $bodyClient = "Olá, {$name}.\n\n"
                . "Obrigado. Seu levantamento foi recebido pela Global Invest Brasil. Nossa equipe analisará as informações para elaboração do escopo do seu projeto.\n\n"
                . "Código da solicitação: {$reference}\n\n"
                . "Atenciosamente,\nGlobal Invest Brasil\n";
            $headersClient = "From: Global Invest Brasil <" . setting('contact_email', 'contato@globalinvestbrasil.com') . ">\r\nContent-Type: text/plain; charset=UTF-8";
            @mail($email, $subjectClient, $bodyClient, $headersClient);
        }
    } catch (Throwable $e) {
        // Notificação é best-effort: falha de e-mail nunca deve impedir o registro do briefing.
    }
}
