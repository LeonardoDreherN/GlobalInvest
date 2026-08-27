<?php
declare(strict_types=1);
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: private, max-age=60');
header('Vary: Cookie');
require_once __DIR__ . '/app/bootstrap.php';
try { $publisher = setting('adsense_publisher_id'); $enabled = setting('adsense_enabled') === '1'; } catch (Throwable $e) { $publisher=''; $enabled=false; }
$consented = ($_COOKIE['globalinvest_cookie_consent'] ?? '') === 'accepted_all';
if ($enabled && $consented && preg_match('/^ca-pub-[0-9]+$/', $publisher)) echo "(function(){var s=document.createElement('script');s.async=true;s.crossOrigin='anonymous';s.src='https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=" . $publisher . "';document.head.appendChild(s);})();";
