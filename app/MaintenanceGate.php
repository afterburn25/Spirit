<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

/** All application requests share this lock; maintenance upgrades it exclusively.
 * A process exit releases it. No public flag or pathname can bypass the lock.
 */
final class MaintenanceGate
{
    private static mixed $handle=null;
    private static bool $exclusive=false;
    public static function enter(): void
    {
        if(is_resource(self::$handle)||!Paths::installed()){return;}
        $path=Paths::runtime().'/maintenance.lock';
        if(is_link($path)){throw new RuntimeException('Unsafe maintenance lock.');}
        $handle=@fopen($path,'c+b');
        if(!$handle||!@chmod($path,0600)){if(is_resource($handle)){fclose($handle);}throw new RuntimeException('Private maintenance lock unavailable.');}
        if(!flock($handle,LOCK_SH|LOCK_NB)){fclose($handle);self::busy();}
        self::$handle=$handle;
    }
    public static function exclusive(): void
    {
        self::enter();
        if(!is_resource(self::$handle)||!flock(self::$handle,LOCK_EX|LOCK_NB)){self::busy();}
        self::$exclusive=true;
    }
    public static function isExclusive(): bool {return self::$exclusive;}
    public static function release(): void
    {
        if(is_resource(self::$handle)){flock(self::$handle,LOCK_UN);fclose(self::$handle);}self::$handle=null;self::$exclusive=false;
    }
    private static function busy(): never
    {
        if(PHP_SAPI!=='cli'){header('Retry-After: 10');}
        throw new RuntimeException('Another website operation is active. Retry maintenance after current requests finish.');
    }
}
