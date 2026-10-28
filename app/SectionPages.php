<?php
declare(strict_types=1);
namespace Spirit;

/** Existing Page records supply approved artwork/copy to fixed public layouts. */
final class SectionPages
{
    public const SECTIONS = [
        'home' => ['Home', '', 'home', 1600, 'Subtitle introduces the hero; page text appears in the purpose section.'],
        'music' => ['Music', 'music/', 'music', 1200, 'Introduction and feature artwork above the published releases.'],
        'shows' => ['Shows', 'shows/', 'shows', 1400, 'Introduction and feature artwork above upcoming and past shows.'],
        'band' => ['The band', 'band/', 'band', 1400, 'The band story and photograph above the published member profiles.'],
        'media' => ['Media', 'media/', 'media', 1400, 'Introduction and feature artwork above the published videos and photographs.'],
        'booking' => ['Booking', 'booking/', 'booking', 1000, 'Introduction and feature artwork beside the existing inquiry form.'],
        'press' => ['Press kit', 'press/', 'press', 1400, 'Feature artwork above the press biography and approved downloads.'],
        'merch' => ['Merchandise', 'shop/', 'catalog', 1400, 'Introduction and feature artwork on the catalog, not private checkout pages.'],
    ];

    public static function definition(string $slug): ?array
    {
        return self::SECTIONS[$slug] ?? null;
    }

    public static function slugForView(string $view): ?string
    {
        foreach (self::SECTIONS as $slug => $section) {
            if ($section[2] === $view) { return $slug; }
        }
        return null;
    }

    public static function text(?array $page, string $key, string $fallback = ''): string
    {
        $value = $page['data'][$key] ?? '';
        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    /** Optional externally linked call-to-action; never render an incomplete override. */
    public static function customButton(?array $page): string
    {
        $label=self::text($page,'button_label');
        $url=self::text($page,'button_url');
        if($label===''||$url===''){return '';}
        // The editor checks HTTPS URLs; this is a defensive guard for legacy records.
        if(!filter_var($url,FILTER_VALIDATE_URL)||parse_url($url,PHP_URL_SCHEME)!=='https'){
            return '';
        }
        return '<a class="button outline" href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($label).'</a>';
    }

    public static function focal(?array $page): string
    {
        $focal = $page['data']['focal'] ?? 'center';
        return in_array($focal, ['center', 'top', 'left', 'right'], true) ? $focal : 'center';
    }

    public static function artwork(?array $page): string
    {
        $id = (int)($page['data']['image_id'] ?? 0);
        if ($id < 1) { return ''; }
        return '<figure class="section-artwork">'.PublicSite::image($id, $page['title'] ?? 'Page artwork', 'section-feature focal-'.self::focal($page), true).'</figure>';
    }

    /** Exact matching, without the general library's pagination/1000-row ceiling. */
    public static function records(string $slug): array
    {
        if (!isset(self::SECTIONS[$slug])) { return []; }
        $q = Database::connect()->prepare('SELECT id,title,draft_slug,published_slug,published_json IS NOT NULL AS is_published,draft_json<>COALESCE(published_json,\'\') AS has_draft FROM '.Database::table('content')." WHERE kind='page' AND deleted_at IS NULL AND (draft_slug=? OR published_slug=?) ORDER BY id");
        $q->execute([$slug,$slug]);
        return $q->fetchAll();
    }

    /** Pixel guidance is not approval of rights, composition, detail or sharpness. */
    public static function resolution(array $dimensions, int $recommended): string
    {
        $width = (int)($dimensions['width'] ?? 0); $height = (int)($dimensions['height'] ?? 0);
        if ($width < 1 || $height < 1) { return 'Image dimensions could not be read. Check the selected file before publishing.'; }
        return $width.' × '.$height.' pixels. '.($width < $recommended
            ? 'Below the suggested '.$recommended.'-pixel width. Use an approved larger original; enlarging a small image does not restore detail.'
            : 'Meets the suggested pixel width. Still review its sharpness, composition and usage approval.');
    }
}
