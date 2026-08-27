<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
$publisher = setting('adsense_publisher_id'); $enabled = setting('adsense_enabled') === '1';
json_response(['enabled' => $enabled && preg_match('/^ca-pub-[0-9]+$/', $publisher) === 1, 'publisher' => $publisher]);
