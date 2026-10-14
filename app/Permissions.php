<?php
declare(strict_types=1);
namespace Spirit;

/** Deny-by-default staff capabilities. Customers never consult this map. */
final class Permissions
{
    public const ROLES=['owner'=>'Owner','content_editor'=>'Content editor','store_manager'=>'Store manager'];
    private const GRANTS=[
        'owner'=>['owner','dashboard','account','content.manage','media.manage','store.manage','orders.view','orders.fulfill'],
        'content_editor'=>['dashboard','account','content.manage','media.manage'],
        'store_manager'=>['dashboard','account','media.manage','store.manage','orders.view','orders.fulfill'],
    ];
    public static function allows(?array $user,string $permission): bool
    {
        return $user!==null && (int)($user['active']??0)===1 && in_array($permission,self::GRANTS[$user['role']??'']??[],true);
    }
}
