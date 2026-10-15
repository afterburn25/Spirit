<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
Spirit\ContentAdmin::boot('owner');
if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) { header('Allow: GET, HEAD'); http_response_code(405); exit('Branding previews are read-only.'); }
header('Referrer-Policy: no-referrer');
Spirit\Branding::beginPreview();
Spirit\PublicSite::render('home');
