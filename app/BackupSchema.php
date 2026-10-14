<?php
declare(strict_types=1);
namespace Spirit;
use PDO;
use RuntimeException;

final class BackupSchema
{
    public static function names(): array
    {
        $names=['installation','migrations'];
        foreach(Migrations::files() as $file){preg_match_all('/CREATE TABLE IF NOT EXISTS `\{\{prefix\}\}([a-z_]+)`/',(string)file_get_contents($file),$m);$names=array_merge($names,$m[1]);}
        $names=array_values(array_unique($names));sort($names,SORT_STRING);return $names;
    }
    public static function shape(PDO $db): array
    {
        $shape=[];
        foreach(self::names() as $name){
            $columns=$db->query('SHOW FULL COLUMNS FROM '.Database::table($name))->fetchAll();$fields=[];
            foreach($columns as $c){if(!preg_match('/^[a-z_]+$/D',$c['Field'])){throw new RuntimeException('Unsupported database column.');}$fields[]=['name'=>$c['Field'],'type'=>$c['Type'],'null'=>$c['Null'],'key'=>$c['Key']];}
            $shape[$name]=$fields;
        }
        return $shape;
    }
    public static function encode(array $row): array
    {
        return array_map(static fn($value)=>$value===null?null:base64_encode((string)$value),$row);
    }
    public static function decode(array $row,array $fields): array
    {
        if(array_keys($row)!==array_column($fields,'name')){throw new RuntimeException('Backup row does not match the installed schema.');}
        $out=[];foreach($row as $key=>$value){if($value===null){$out[$key]=null;continue;}if(!is_string($value)||($plain=base64_decode($value,true))===false){throw new RuntimeException('Invalid encoded database value.');}$out[$key]=$plain;}return $out;
    }
    public static function protectedNames(): array
    {
        return array_values(array_diff(self::names(),['installation','migrations','audit','login_limits','request_limits','content','content_media','content_revisions','media','settings','products','shop_categories']));
    }
    public static function digest(PDO $db,string $name): string
    {
        if(!in_array($name,self::names(),true)){throw new RuntimeException('Unknown backup table.');}
        $columns=$db->query('SHOW COLUMNS FROM '.Database::table($name))->fetchAll();$primary=array_column(array_filter($columns,static fn($c)=>$c['Key']==='PRI'),'Field');if(!$primary){throw new RuntimeException('Application table has no primary key.');}$order=implode(',',array_map(static fn($field)=>'`'.$field.'`',$primary));
        $q=$db->query('SELECT * FROM '.Database::table($name).' ORDER BY '.$order);$hash=hash_init('sha256');
        while($row=$q->fetch()){$encoded=json_encode(self::encode($row),JSON_THROW_ON_ERROR);hash_update($hash,strlen($encoded).':'.$encoded);}return hash_final($hash);
    }
    public static function fields(PDO $db,string $name): array
    {
        if(!in_array($name,self::names(),true)){throw new RuntimeException('Unknown backup table.');}
        return array_column($db->query('SHOW COLUMNS FROM '.Database::table($name))->fetchAll(),'Field');
    }
}
