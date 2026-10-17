<?php
declare(strict_types=1);
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function app_url(string $path = ''): string {
    $normalized = ltrim($path, '/');
    $url = Spirit\Paths::basePath() . '/' . $normalized;
    if ($normalized !== '' && preg_match('#^(?:assets|images)/#', $normalized)) {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . rawurlencode(Spirit\Paths::version());
    }
    return $url;
}
function go(string $path): never { header('Location: ' . app_url($path), true, 303); exit; }
function post(string $key): string { return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : ''; }
function password_input(string $key): string { return is_string($_POST[$key] ?? null) ? $_POST[$key] : ''; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(Spirit\Security::csrf()) . '">'; }
function flash(string $message): void { $_SESSION['notice'] = $message; }
