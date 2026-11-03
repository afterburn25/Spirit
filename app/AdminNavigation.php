<?php
declare(strict_types=1);
namespace Spirit;

/** Display structure only. Every destination retains its own server permission check. */
final class AdminNavigation
{
    private const GROUPS = [
        'content' => ['Website content', 'content', [
            'page' => ['Pages & homepage', 'admin/content/?kind=page', 'content.manage'],
            'member' => ['Band members', 'admin/content/?kind=member', 'content.manage'],
            'library' => ['Media library', 'admin/media/', 'media.manage'],
            'gallery' => ['Photo gallery', 'admin/content/?kind=gallery', 'content.manage'],
            'video' => ['Videos', 'admin/content/?kind=video', 'content.manage'],
            'press' => ['Press downloads', 'admin/content/?kind=press', 'content.manage'],
        ]],
        'music' => ['Music & shows', 'music', [
            'release' => ['Music releases', 'admin/content/?kind=release', 'content.manage'],
            'track' => ['Audio tracks', 'admin/content/?kind=track', 'content.manage'],
            'event' => ['Shows & ticket links', 'admin/content/?kind=event', 'content.manage'],
        ]],
        'analytics' => ['Analytics', 'analytics', [
            'analytics' => ['Audience dashboard', 'admin/analytics/', 'owner'],
            'analytics-visitors' => ['Visitor explorer', 'admin/analytics/visitors.php', 'owner'],
            'analytics-geography' => ['Geography data', 'admin/analytics/geography.php', 'owner'],
        ]],
        'audience' => ['Bookings & communication', 'audience', [
            'bookings' => ['Booking inquiries', 'admin/bookings/', 'owner'],
            'subscribers' => ['Subscribers', 'admin/subscribers/', 'owner'],
            'mail' => ['Delivery queue', 'admin/mail/', 'owner'],
            'communication' => ['Email settings', 'admin/communication/', 'owner'],
        ]],
        'commerce' => ['Store & orders', 'commerce', [
            'store' => ['Products & inventory', 'admin/shop/', 'store.manage'],
            'store-categories' => ['Product categories', 'admin/shop/categories.php', 'store.manage'],
            'customers' => ['Customers', 'admin/shop/customers.php', 'owner'],
            'orders' => ['Orders & fulfillment', 'admin/orders/', 'orders.view'],
            'store-settings' => ['Store settings', 'admin/shop/settings.php', 'owner'],
            'delivery-policy' => ['Shipping, tax & terms', 'admin/orders/policy.php', 'owner'],
            'payment-settings' => ['Payment settings', 'admin/orders/payments.php', 'owner'],
            'payment-notifications' => ['Payment notifications', 'admin/orders/webhooks.php', 'owner'],
        ]],
        'settings' => ['Settings & access', 'settings', [
            'settings' => ['Website settings', 'admin/settings.php', 'owner'],
            'discovery' => ['Search & sharing', 'admin/discovery.php', 'owner'],
            'staff' => ['Staff access', 'admin/staff/', 'owner'],
            'account' => ['Account & security', 'admin/account.php', 'account'],
        ]],
        'help' => ['Help & guidance', 'help', [
            'help' => ['Help center', 'admin/help/', 'dashboard'],
        ]],
        'system' => ['System & maintenance', 'system', [
            'launch' => ['Launch checks', 'admin/launch/', 'owner'],
            'installation' => ['Installation health', 'admin/installation.php', 'owner'],
            'backups' => ['Backups & safe restore', 'admin/backups/', 'owner'],
            'upgrades' => ['Browser upgrades', 'admin/upgrades/', 'owner'],
            'updates' => ['Database updates', 'admin/updates.php', 'owner'],
            'maintenance' => ['Temporary data & retention', 'admin/maintenance/', 'owner'],
            'activity' => ['Activity history', 'admin/activity/', 'owner'],
            'roadmap' => ['Release roadmap', 'admin/roadmap.php', 'owner'],
        ]],
    ];

    /** Read-only navigation permissions shared by the Help Center and guide checks. */
    public static function documentedDestinations(): array
    {
        $destinations=['admin/'=>'dashboard'];
        foreach(self::GROUPS as $group){
            foreach($group[2] as $item){$destinations[$item[1]]=$item[2];}
        }
        return $destinations;
    }

    /** Exact destination for the active screen; never trust a requested URL. */
    public static function destinationForActive(string $active): ?string
    {
        if ($active === 'dashboard') { return 'admin/'; }
        foreach (self::GROUPS as $group) {
            if (isset($group[2][$active])) { return $group[2][$active][1]; }
        }
        return null;
    }

    public static function permissionForDestination(string $path): ?string
    {
        return self::documentedDestinations()[$path]??null;
    }

    public static function build(?array $user, string $active): array
    {
        $menu = ['overview' => Permissions::allows($user, 'dashboard'), 'groups' => [], 'section' => 'Administration', 'label' => 'Overview'];
        foreach (self::GROUPS as $id => [$label, $icon, $items]) {
            $links = [];
            foreach ($items as $key => [$text, $path, $permission]) {
                if (!Permissions::allows($user, $permission)) { continue; }
                $links[] = ['key' => $key, 'label' => $text, 'path' => $path, 'current' => $key === $active];
                if ($key === $active) { $menu['section'] = $label; $menu['label'] = $text; }
            }
            if (!$links) { continue; }
            $menu['groups'][] = ['id' => $id, 'label' => $label, 'icon' => $icon, 'links' => $links, 'current' => in_array(true, array_column($links, 'current'), true)];
        }
        return $menu;
    }

    /** Opaque preference scope, not an authentication credential. No URLs are saved. */
    public static function preferenceScope(array $user, string $basePath, string $installationSecret): string
    {
        return substr(hash_hmac('sha256', 'admin-navigation-v1|'.$basePath.'|'.(int)$user['id'].'|'.($user['role'] ?? ''), $installationSecret), 0, 32);
    }

    public static function icon(string $name): string
    {
        $path = match ($name) {
            'content' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
            'music' => '<path d="M9 17V5l11-2v12M9 9l11-2"/><ellipse cx="6" cy="17" rx="3" ry="3"/><ellipse cx="17" cy="15" rx="3" ry="3"/>',
            'analytics' => '<path d="M3 3v18h18M7 16v-5M12 16V6M17 16V9"/><path d="m6 8 5-5 6 3 4-4"/>',
            'audience' => '<path d="M4 4h16v12H9l-5 4V4Z"/><path d="M8 8h8M8 12h5"/>',
            'commerce' => '<path d="M4 7h16l-1 14H5L4 7ZM8 7V5a4 4 0 0 1 8 0v2"/>',
            'settings' => '<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="8" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="10" cy="18" r="2"/>',
            'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 2-2.5 2-2.5 4"/><path d="M12 17h.01"/>',
            'system' => '<rect x="3" y="3" width="18" height="12" rx="2"/><path d="M8 21h8M12 15v6M7 9l3 3 7-6"/>',
            default => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        };
        return '<svg class="admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.$path.'</svg>';
    }
}
