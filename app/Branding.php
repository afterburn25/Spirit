<?php
declare(strict_types=1);
namespace Spirit;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Owner-managed logo drafts. Only the explicitly published image is public. */
final class Branding
{
    public const KEY = 'site_branding_v1';
    public const EMPTY_STATE = ['revision'=>0, 'draft_logo_id'=>0, 'published_logo_id'=>0];
    private static ?array $cached = null;
    private static bool $preview = false;

    public static function decode(?string $json): array
    {
        if ($json === null) { return self::EMPTY_STATE; }
        try { $state = json_decode($json, true, 8, JSON_THROW_ON_ERROR); }
        catch (\JsonException $e) { throw new RuntimeException('Saved branding is unreadable. Its original value has been preserved.'); }
        if (!is_array($state) || count($state) !== 3) { throw new RuntimeException('Saved branding has an unsupported format.'); }
        foreach (self::EMPTY_STATE as $key => $unused) {
            if (!isset($state[$key]) || !is_int($state[$key]) || $state[$key] < 0 || $state[$key] > 4294967294) {
                throw new RuntimeException('Saved branding has an unsupported format.');
            }
        }
        return $state;
    }

    /** Audit is append-only across supported restoration; old forms retain the old epoch. */
    public static function restoreEpoch(?\PDO $db = null): int
    {
        $db ??= Database::connect();
        $q = $db->query('SELECT id FROM '.Database::table('audit')." WHERE action IN ('backup.restored.content','backup.restored.site') ORDER BY id DESC LIMIT 1");
        $id = $q->fetchColumn(); return $id === false ? 0 : (int)$id;
    }

    public static function state(): array
    {
        if (self::$cached !== null) { return self::$cached; }
        $q = Database::connect()->prepare('SELECT setting_value FROM '.Database::table('settings').' WHERE setting_key=?');
        $q->execute([self::KEY]); $json = $q->fetchColumn();
        return self::$cached = self::decode($json === false ? null : $json);
    }

    /** Pure transition; stale submissions must not overwrite another session. */
    public static function transition(array $state, array $input, string $action, int $restoreEpoch = 0): array
    {
        $state = self::decode(json_encode($state, JSON_THROW_ON_ERROR));
        if (!in_array($action, ['save', 'publish', 'withdraw'], true)) { throw new InvalidArgumentException('Unknown branding action.'); }
        $submittedEpoch = Input::integer($input, 'restore_epoch', 0, PHP_INT_MAX, 0);
        if ($submittedEpoch !== $restoreEpoch) { throw new RuntimeException('Site data was restored after this form opened. Reload and review the recovered branding before saving.'); }
        $expected = Input::integer($input, 'revision', 0, 4294967293, 0);
        if ($expected !== $state['revision']) { throw new RuntimeException('Branding changed in another session. Reload and review the saved version before trying again.'); }
        if ($action !== 'withdraw') {
            if (($input['logo_id'] ?? null) === '') { $input['logo_id'] = '0'; }
            $state['draft_logo_id'] = Input::integer($input, 'logo_id', 0, 4294967294, 0);
        }
        if ($action === 'publish') {
            if (($input['approved'] ?? '') !== '1') { throw new InvalidArgumentException('Confirm that you approve this logo for public use.'); }
            $state['published_logo_id'] = $state['draft_logo_id'];
        } elseif ($action === 'withdraw') { $state['published_logo_id'] = 0; }
        $state['revision']++;
        return $state;
    }

    public static function save(array $input, int $actor, string $action): void
    {
        $db = Database::connect(); $db->beginTransaction();
        try {
            $q = $db->prepare('SELECT id,role,active,session_version FROM '.Database::table('users').' WHERE id=? FOR UPDATE');
            $q->execute([$actor]); $staff = $q->fetch();
            if (!Permissions::allows($staff ?: null, 'owner') ||
                (PHP_SAPI !== 'cli' && ((int)($_SESSION['user_id'] ?? 0) !== $actor ||
                (int)($_SESSION['session_version'] ?? 0) !== (int)$staff['session_version']))) {
                throw new RuntimeException('Owner access changed. Sign in again.');
            }
            // An absent key is initialized only inside a successful explicit POST.
            $q = $db->prepare('INSERT INTO '.Database::table('settings').' (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_key=setting_key');
            $q->execute([self::KEY, json_encode(self::EMPTY_STATE, JSON_THROW_ON_ERROR)]);
            $q = $db->prepare('SELECT setting_value FROM '.Database::table('settings').' WHERE setting_key=? FOR UPDATE');
            $q->execute([self::KEY]);
            $state = self::transition(self::decode($q->fetchColumn()), $input, $action, self::restoreEpoch($db));
            if ($action !== 'withdraw' && $state['draft_logo_id'] > 0) {
                $media = Media::get($state['draft_logo_id']);
                try {
                    if (!$media || $media['kind'] !== 'image') { throw new RuntimeException(); }
                    $file = Media::path($media); $actual = hash_file('sha256', $file);
                    if (!is_string($actual) || !hash_equals($media['checksum'], $actual) || !Media::dimensions($media)) { throw new RuntimeException(); }
                } catch (Throwable $e) { throw new InvalidArgumentException('Choose an existing, undamaged image from Media library. No branding was changed.'); }
            }
            $q = $db->prepare('UPDATE '.Database::table('settings').' SET setting_value=? WHERE setting_key=?');
            $q->execute([json_encode($state, JSON_THROW_ON_ERROR), self::KEY]);
            Database::audit($db, $actor, 'branding.'.$action); $db->commit(); self::$cached = $state;
        } catch (Throwable $e) { if ($db->inTransaction()) { $db->rollBack(); } throw $e; }
    }

    public static function publicImage(int $id): bool
    {
        if ($id < 1) { return false; }
        try { return self::state()['published_logo_id'] === $id; }
        catch (Throwable $e) { return false; }
    }

    public static function beginPreview(): void { Auth::requireOwner(); self::$preview = true; }
    public static function inPreview(): bool { return self::$preview; }

    public static function textMark(string $name): string
    {
        return '<span class="crown" aria-hidden="true">♛</span><span>'.e($name).'</span>';
    }

    /** Bundled animated fallback uses the approved transparent crown-and-wordmark mark. */
    public static function bundledMark(bool $footer = false): string
    {
        $animated = Paths::root().'/images/spiritual-gorillaz-logo.png';
        $static = Paths::root().'/images/spiritual-gorillaz-logo-static.png';
        $animatedReady = is_file($animated) && !is_link($animated);
        $staticReady = is_file($static) && !is_link($static);
        if (!$animatedReady && !$staticReady) { return ''; }

        $staticUrl = e(app_url('images/spiritual-gorillaz-logo-static.png'));
        if ($footer || !$animatedReady) {
            return '<span class="bundled-brand-wrap bundled-brand-static"><img class="bundled-site-logo" src="'.$staticUrl.'" width="600" height="600" alt="" loading="'.($footer ? 'lazy' : 'eager').'" decoding="async"></span>';
        }

        $source = $staticReady ? '<source media="(prefers-reduced-motion: reduce)" srcset="'.$staticUrl.'">' : '';
        return '<span class="bundled-brand-wrap bundled-brand-animated"><picture class="bundled-brand-picture">'.$source.'<img class="bundled-site-logo bundled-site-logo-animated" src="'.e(app_url('images/spiritual-gorillaz-logo.png')).'" width="600" height="600" alt="" loading="eager" fetchpriority="high" decoding="async"></picture></span>';
    }

    /** Original ratio and transparency are retained; no arbitrary image URL. */
    public static function image(int $id, bool $footer = false): string
    {
        if ($id < 1) { return ''; }
        try {
            $media = Media::get($id);
            if (!$media || $media['kind'] !== 'image') { return ''; }
            $size = Media::dimensions($media);
            if (!$size) { return ''; }
            $width = $size['width']; $height = $size['height']; $url = Media::url($id);
            $responsive = $width > 320 ? ' srcset="'.e($url.'&width=320').' 320w, '.e($width > 640 ? $url.'&width=640' : $url).' '.min(640, $width).'w" sizes="(max-width: 820px) 142px, 174px"' : '';
            return '<span class="brand-logo-wrap"><img class="site-logo" src="'.e($width > 320 ? $url.'&width=320' : $url).'"'.$responsive.' width="'.$width.'" height="'.$height.'" alt="" loading="'.($footer ? 'lazy' : 'eager').'" decoding="async"></span>';
        } catch (Throwable $e) { return ''; }
    }

    public static function mark(array $settings, bool $footer = false): string
    {
        try {
            $state = self::state(); $image = self::image($state[self::$preview ? 'draft_logo_id' : 'published_logo_id'], $footer);
            if ($image !== '') { return $image; }
        } catch (Throwable $e) { /* Keep the site's name available without exposing a storage error. */ }
        $bundled=self::bundledMark($footer);
        return $bundled!==''?$bundled:self::textMark((string)$settings['site_name']);
    }
}
