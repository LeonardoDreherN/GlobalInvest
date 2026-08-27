<?php
declare(strict_types=1);

/*
 * Conversor Markdown -> HTML enxuto, sem dependência externa.
 * Cobre o que o painel precisa: títulos, parágrafos, negrito, itálico,
 * listas, citações, links, imagens, regra horizontal e blocos de código.
 * Também deixa passar HTML de bloco (o painel é de uso interno e confiável),
 * removendo <script>/<style>, handlers on* e URLs javascript:.
 * A saída usa só tags já estilizadas por .publication-body em styles.css.
 */

function md_safe_url(string $url): string {
    $u = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
    if ($u === '') return '';
    if (preg_match('#^(https?://|mailto:|tel:|/|\#|\.\./|\./)#i', $u)) {
        return htmlspecialchars($u, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    return '';
}

function md_sanitize_html(string $html): string {
    $html = preg_replace('#<\s*(script|style)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html) ?? $html;
    $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;
    $html = preg_replace('#(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2#i', '$1=$2#$2', $html) ?? $html;
    return $html;
}

function md_inline(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $text = preg_replace_callback('/`([^`]+)`/', fn($m) => '<code>' . $m[1] . '</code>', $text) ?? $text;
    $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)\)/', function ($m) {
        $url = md_safe_url($m[2]);
        return $url ? '<img src="' . $url . '" alt="' . $m[1] . '" loading="lazy">' : $m[0];
    }, $text) ?? $text;
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
        $url = md_safe_url($m[2]);
        $ext = preg_match('#^https?://#i', html_entity_decode($url)) ? ' target="_blank" rel="noopener"' : '';
        return $url ? '<a href="' . $url . '"' . $ext . '>' . $m[1] . '</a>' : $m[0];
    }, $text) ?? $text;
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text) ?? $text;
    $text = preg_replace('/(?<![\*\w])\*(?!\s)([^\*\n]+?)(?<!\s)\*(?![\*\w])/', '<em>$1</em>', $text) ?? $text;
    return $text;
}

function md_to_html(?string $src): string {
    $src = str_replace(["\r\n", "\r"], "\n", (string) $src);
    $lines = explode("\n", $src);
    $n = count($lines);
    $i = 0;
    $out = [];
    $blockStart = '/^(#{1,6}\s|>\s?|\s*[-*+]\s+|\s*\d+[.)]\s+|```|(-{3,}|\*{3,}|_{3,})\s*$)/';

    while ($i < $n) {
        $line = $lines[$i];
        if (trim($line) === '') { $i++; continue; }

        if (preg_match('/^```/', $line)) {
            $i++;
            $buf = [];
            while ($i < $n && !preg_match('/^```/', $lines[$i])) { $buf[] = $lines[$i]; $i++; }
            $i++;
            $out[] = '<pre><code>' . htmlspecialchars(implode("\n", $buf), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
            continue;
        }

        if (preg_match('/^\s*<(figure|div|table|thead|tbody|tr|h[1-6]|blockquote|ul|ol|p|img|iframe|hr|section|aside|picture|video|source)\b/i', $line)) {
            $buf = [];
            while ($i < $n && trim($lines[$i]) !== '') { $buf[] = $lines[$i]; $i++; }
            $out[] = md_sanitize_html(implode("\n", $buf));
            continue;
        }

        if (preg_match('/^(#{1,6})\s+(.*)$/', $line, $m)) {
            $level = strlen($m[1]);
            $tag = $level <= 2 ? 'h2' : ($level === 3 ? 'h3' : 'h4');
            $out[] = "<$tag>" . md_inline(trim($m[2])) . "</$tag>";
            $i++;
            continue;
        }

        if (preg_match('/^(-{3,}|\*{3,}|_{3,})\s*$/', $line)) { $out[] = '<hr>'; $i++; continue; }

        if (preg_match('/^>\s?/', $line)) {
            $buf = [];
            while ($i < $n && preg_match('/^>\s?(.*)$/', $lines[$i], $mm)) { $buf[] = $mm[1]; $i++; }
            $out[] = '<blockquote>' . md_to_html(implode("\n", $buf)) . '</blockquote>';
            continue;
        }

        if (preg_match('/^\s*[-*+]\s+/', $line)) {
            $items = [];
            while ($i < $n && preg_match('/^\s*[-*+]\s+(.*)$/', $lines[$i], $mm)) { $items[] = '<li>' . md_inline(trim($mm[1])) . '</li>'; $i++; }
            $out[] = '<ul>' . implode('', $items) . '</ul>';
            continue;
        }

        if (preg_match('/^\s*\d+[.)]\s+/', $line)) {
            $items = [];
            while ($i < $n && preg_match('/^\s*\d+[.)]\s+(.*)$/', $lines[$i], $mm)) { $items[] = '<li>' . md_inline(trim($mm[1])) . '</li>'; $i++; }
            $out[] = '<ol>' . implode('', $items) . '</ol>';
            continue;
        }

        $buf = [];
        while ($i < $n && trim($lines[$i]) !== '' && !preg_match($blockStart, $lines[$i])) { $buf[] = $lines[$i]; $i++; }
        $out[] = '<p>' . nl2br(md_inline(trim(implode("\n", $buf)))) . '</p>';
    }

    return implode("\n", $out);
}
