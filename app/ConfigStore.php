<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use Throwable;

final class ConfigStore
{
    private static array $cache=[];
    private static function guard(string $name): void
    {
        if(!in_array($name,['communication','store','payments','site','maintenance'],true)){throw new InvalidArgumentException('Unknown service configuration.');}
    }
    public static function read(string $name): array
    {
        self::guard($name);if(array_key_exists($name,self::$cache)){return self::$cache[$name];}
        $q=Database::connect()->prepare('SELECT settings_json FROM '.Database::table('service_settings').' WHERE namespace=?');$q->execute([$name]);$value=$q->fetchColumn();
        return self::$cache[$name]=$value===false?[]:json_decode($value,true,64,JSON_THROW_ON_ERROR);
    }
    public static function write(string $name,array $values,int $actor): void
    {
        self::guard($name);$json=json_encode($values,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);$db=Database::connect();$db->beginTransaction();
        try{$q=$db->prepare('INSERT INTO '.Database::table('service_settings').' (namespace,settings_json,updated_at) VALUES (?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json),updated_at=UTC_TIMESTAMP()');$q->execute([$name,$json]);Database::audit($db,$actor,'settings.'.$name.'.updated');$db->commit();self::$cache[$name]=$values;}catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
}
