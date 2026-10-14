<?php
declare(strict_types=1);
namespace Spirit;
/** Share the frozen early recovery lock with ordinary web and CLI operations. */
final class MaintenanceGate
{
    public static function enter(): void
    {
        if(Paths::installed()){\SpiritRecovery::enter(Paths::runtime());}
    }
    public static function exclusive(): void {self::enter();\SpiritRecovery::exclusive();}
    public static function isExclusive(): bool {return \SpiritRecovery::isExclusive();}
    public static function release(): void {\SpiritRecovery::release();}
}
