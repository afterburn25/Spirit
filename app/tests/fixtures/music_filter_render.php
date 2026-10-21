<?php
declare(strict_types=1);
/**
 * CLI-only presentation fixture. It renders the production templates with an
 * explicitly empty data layer. It never connects to a database or writes state.
 */
namespace Spirit {
    final class Content {
        public static function listing(string $kind, bool $public = true): array { return []; }
        public static function bySlug(string $kind, string $slug): ?array { return null; }
    }
    final class Catalog {
        public static function options(): array { return ['enabled' => false]; }
    }
    final class Communications {
        public static function newsletterReady(): bool { return false; }
    }
}
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(404); exit(1); }
    $root = realpath(getenv('SPIRIT_CODE') ?: dirname(__DIR__, 3));
    if (!$root || !is_file($root . '/includes/bootstrap.php')) {
        throw new \RuntimeException('An application source root is required.');
    }
    $base = $argv[1] ?? '';
    if (!in_array($base, ['', '/band'], true)) { throw new \RuntimeException('Unsupported fixture base.'); }
    $_SERVER['SCRIPT_NAME'] = $base . '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
    $_SERVER['DOCUMENT_ROOT'] = $root;
    $_SERVER['REQUEST_URI'] = $base . '/music/';
    $_GET['type'] = $argv[2] ?? 'all';
    require $root . '/includes/bootstrap.php';
    // Initialize only the in-memory source selector; no database or publication.
    $source = new \ReflectionProperty(\Spirit\Branding::class, 'cachedSource');
    $source->setValue(null, 'bundled');
    $settings = [
        'site_name' => 'Spiritual Gorillaz',
        'tagline' => 'Music. People. Purpose.',
        'footer_copyright' => '',
    ];
    $title = 'Music | Isolated template regression';
    $description = 'Isolated empty-data template fixture; not a live installation.';
    $canonical = null;
    $isPreview = false;
    $view = $active = 'music';
    $pageContent = $record = null;
    require $root . '/app/views/public/header.php';
    $sections = getenv('SPIRIT_SECTIONS_FIXTURE');
    require ($sections ?: $root . '/app/views/public/sections.php');
    require $root . '/app/views/public/footer.php';
}
