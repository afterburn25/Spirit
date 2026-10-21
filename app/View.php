<?php
declare(strict_types=1);
namespace Spirit;

final class View
{
    public static function start(string $title, string $area = 'public', string $active = '', ?array $user = null): void
    {
        header('Cache-Control: no-store');
        if ($area !== 'public') { header('X-Robots-Tag: noindex, nofollow'); }
        require Paths::root() . '/app/views/header.php';
    }
    public static function end(): void { require Paths::root() . '/app/views/footer.php'; }
    public static function notice(?string $error = null): void
    {
        if ($error) { echo '<div class="notice error" role="alert">' . e($error) . '</div>'; }
        if (isset($_SESSION['notice'])) { echo '<div class="notice success" role="status">' . e($_SESSION['notice']) . '</div>'; unset($_SESSION['notice']); }
    }
}
