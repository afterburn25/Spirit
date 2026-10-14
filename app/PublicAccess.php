<?php
declare(strict_types=1);
namespace Spirit;

/** Progressive public access; never supplies legal text or publishes content. */
final class PublicAccess
{
    public const POLICIES = [
        'privacy' => 'Privacy',
        'terms' => 'Terms',
        'shipping' => 'Shipping',
        'returns' => 'Returns',
    ];

    public static function policyLinks(): void
    {
        $links = [];
        foreach (self::POLICIES as $slug => $label) {
            // bySlug reads the published snapshot, never the latest private draft.
            if ($page = Content::bySlug('page', $slug)) {
                $links[] = '<a href="'.e(PublicSite::url($page)).'">'.e($label).'</a>';
            }
        }
        if ($links) {
            echo '<nav class="policy-links" aria-label="Website policies">'.implode('', $links).'</nav>';
        }
    }

    public static function nativeTracks(array $tracks): void
    {
        if (!$tracks) { return; }
        echo '<noscript><div class="native-tracks"><p>JavaScript is off. Use the audio controls below; playback stops when you leave this page.</p>';
        foreach ($tracks as $track) {
            $source = Media::url((int)$track['data']['audio_id']);
            echo '<div class="native-track"><p>'.e($track['title']).'</p><audio controls preload="none" aria-label="Listen to '.e($track['title']).'" src="'.e($source).'"></audio><a class="text-link" href="'.e($source).'">Open recording: '.e($track['title']).'</a></div>';
        }
        echo '</div></noscript>';
    }
}
