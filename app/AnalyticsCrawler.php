<?php
declare(strict_types=1);
namespace Spirit;

/** Dated offline crawler range matching. A user agent alone is always only reported. */
final class AnalyticsCrawler
{
    private static ?array $providers = null;
    private const MAX_SNAPSHOT_AGE = 90 * 86400;

    public static function classify(string $ua, string $ip): ?array
    {
        $ua = substr($ua, 0, 2048);
        $family = ''; $provider = '';
        if (preg_match('/(?:Googlebot|GoogleOther|Google-InspectionTool)/i', $ua)) { $family = 'Google'; $provider = 'google'; }
        elseif (preg_match('/bingbot/i', $ua)) { $family = 'Bing'; $provider = 'bing'; }
        elseif (preg_match('/(?:Yahoo!?.*Slurp|\bSlurp\b)/i', $ua)) { $family = 'Yahoo Slurp'; }
        else {
            $known = [
                'BingPreview' => 'Bing preview', 'DuckDuckBot' => 'DuckDuckGo', 'Baiduspider' => 'Baidu',
                'Yandex' => 'Yandex', 'Sogou' => 'Sogou', 'Applebot' => 'Apple', 'PetalBot' => 'Petal',
                'facebookexternalhit' => 'Facebook', 'Facebot' => 'Facebook', 'Twitterbot' => 'X / Twitter',
                'LinkedInBot' => 'LinkedIn', 'Discordbot' => 'Discord', 'Slackbot' => 'Slack',
                'TelegramBot' => 'Telegram', 'WhatsApp' => 'WhatsApp', 'AhrefsBot' => 'Ahrefs',
                'SemrushBot' => 'Semrush', 'DotBot' => 'DotBot', 'MJ12bot' => 'Majestic',
                'GPTBot' => 'OpenAI', 'OAI-SearchBot' => 'OpenAI', 'ChatGPT-User' => 'OpenAI',
                'ClaudeBot' => 'Anthropic', 'Claude-User' => 'Anthropic', 'Bytespider' => 'ByteDance',
                'Amazonbot' => 'Amazon', 'PerplexityBot' => 'Perplexity', 'CCBot' => 'Common Crawl',
            ];
            foreach ($known as $needle => $label) { if (stripos($ua, $needle) !== false) { $family = $label; break; } }
            if ($family === '' && preg_match('/(?:bot\b|spider|crawler|scanner|HeadlessChrome|PhantomJS|python-requests|python-urllib|curl\/|Wget\/)/i', $ua)) { $family = 'Other automation'; }
        }
        if ($family === '') { return null; }
        $result = [
            'family' => $family, 'verification' => 'reported_ua', 'verified' => false,
            'source' => 'User agent only', 'data_version' => '', 'snapshot_at' => '',
        ];
        if ($provider === '') { return $result; }
        $snapshot = self::providers()[$provider] ?? null;
        if (!is_array($snapshot)) { return $result; }
        $result['source'] = $snapshot['source_url'];
        $result['data_version'] = $snapshot['published_at'];
        $result['snapshot_at'] = $snapshot['retrieved_at'];
        $retrieved = strtotime($snapshot['retrieved_at']);
        if ($retrieved === false || $retrieved > time() + 86400 || $retrieved < time() - self::MAX_SNAPSHOT_AGE
            || !AnalyticsGeo::isPublicIp($ip)) { return $result; }
        foreach ($snapshot['prefixes'] as $cidr) {
            if (self::ipInCidr($ip, $cidr)) {
                $result['verification'] = 'verified_range'; $result['verified'] = true; break;
            }
        }
        return $result;
    }

    public static function status(): array
    {
        $output = [];
        foreach (['google', 'bing'] as $provider) {
            $snapshot = self::providers()[$provider] ?? null;
            $retrieved = is_array($snapshot) ? strtotime($snapshot['retrieved_at']) : false;
            $fresh = $retrieved !== false && $retrieved <= time() + 86400 && $retrieved >= time() - self::MAX_SNAPSHOT_AGE;
            $output[$provider] = [
                'status' => $snapshot === null ? 'missing' : ($fresh ? 'ready' : 'stale'),
                'source' => $snapshot['source_url'] ?? '',
                'published_at' => $snapshot['published_at'] ?? '',
                'retrieved_at' => $snapshot['retrieved_at'] ?? '',
                'prefix_count' => count($snapshot['prefixes'] ?? []),
            ];
        }
        $output['yahoo'] = ['status' => 'user_agent_only', 'source' => 'User agent only'];
        return $output;
    }

    /** Binary comparisons avoid signed-integer overflow and support IPv4 plus IPv6. */
    public static function ipInCidr(string $ip, string $cidr): bool
    {
        if (strlen($cidr) > 49 || !str_contains($cidr, '/')) { return false; }
        [$network, $length] = explode('/', $cidr, 2);
        if (!ctype_digit($length)) { return false; }
        $normalized = AnalyticsGeo::normalizeIp($ip); $network = AnalyticsGeo::normalizeIp($network);
        if ($normalized === null || $network === null) { return false; }
        $addressBytes = inet_pton($normalized); $networkBytes = inet_pton($network);
        if ($addressBytes === false || $networkBytes === false || strlen($addressBytes) !== strlen($networkBytes)) { return false; }
        $bits = (int)$length; $width = strlen($addressBytes);
        if ($bits < 1 || $bits > $width * 8) { return false; }
        $whole = intdiv($bits, 8); $remaining = $bits % 8;
        if ($whole > 0 && substr($addressBytes, 0, $whole) !== substr($networkBytes, 0, $whole)) { return false; }
        if ($remaining === 0) { return true; }
        $mask = (0xff << (8 - $remaining)) & 0xff;
        return (ord($addressBytes[$whole]) & $mask) === (ord($networkBytes[$whole]) & $mask);
    }

    private static function providers(): array
    {
        if (self::$providers !== null) { return self::$providers; }
        self::$providers = [];
        $path = __DIR__ . '/GeoData/crawlers.json';
        if (!is_file($path) || is_link($path) || realpath($path) !== $path || filesize($path) > 262144) { return []; }
        try {
            $data = json_decode((string)file_get_contents($path), true, 16, JSON_THROW_ON_ERROR);
            if (($data['format'] ?? null) !== 1) { return []; }
            $sources = [
                'google' => 'https://developers.google.com/static/crawling/ipranges/common-crawlers.json',
                'bing' => 'https://www.bing.com/toolbox/bingbot.json',
            ];
            foreach ($sources as $provider => $source) {
                $entry = $data['providers'][$provider] ?? null;
                if (!is_array($entry) || ($entry['source_url'] ?? '') !== $source
                    || !is_string($entry['retrieved_at'] ?? null) || strlen($entry['retrieved_at']) > 40
                    || !is_string($entry['published_at'] ?? null) || strlen($entry['published_at']) > 40
                    || strtotime($entry['retrieved_at']) === false || strtotime($entry['published_at']) === false
                    || !is_array($entry['prefixes'] ?? null) || count($entry['prefixes']) < 1 || count($entry['prefixes']) > 4096) { continue; }
                $valid = true;
                foreach ($entry['prefixes'] as $cidr) {
                    if (!is_string($cidr) || !self::ipInCidr(explode('/', $cidr, 2)[0], $cidr)) { $valid = false; break; }
                }
                if ($valid) { self::$providers[$provider] = $entry; }
            }
        } catch (\Throwable) { }
        return self::$providers;
    }
}
