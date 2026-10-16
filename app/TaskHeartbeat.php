<?php
declare(strict_types=1);
namespace Spirit;

use InvalidArgumentException;
use RuntimeException;

/** Private, timestamp-only evidence that a scheduled CLI worker completed. */
final class TaskHeartbeat
{
    private const NAMES=['mail','payments'];

    private static function file(string $name): string
    {
        if(!in_array($name,self::NAMES,true)){throw new InvalidArgumentException('Unknown task heartbeat.');}
        $dir=Paths::runtime().'/cache';
        if(is_link($dir)||!is_dir($dir)||realpath($dir)!==$dir||(fileperms($dir)&0077)!==0){throw new RuntimeException('Private heartbeat storage unavailable.');}
        return $dir.'/task-'.$name.'.json';
    }

    public static function record(string $name): void
    {
        $file=self::file($name);
        if(is_link($file)){throw new RuntimeException('Unsafe task heartbeat target.');}
        $tmp=dirname($file).'/.task-heartbeat-'.bin2hex(random_bytes(12));
        $body=json_encode(['ran_at'=>gmdate('c')],JSON_THROW_ON_ERROR)."
";
        $h=@fopen($tmp,'x+b');
        if(!$h){throw new RuntimeException('Cannot create task heartbeat.');}
        try{
            if(!@chmod($tmp,0600)||fwrite($h,$body)!==strlen($body)||!fflush($h)||(function_exists('fsync')&&!fsync($h))){throw new RuntimeException('Cannot persist task heartbeat.');}
            fclose($h);$h=null;
            if(!@rename($tmp,$file)){throw new RuntimeException('Cannot publish task heartbeat.');}
        }finally{
            if(is_resource($h)){fclose($h);}
            if(is_file($tmp)){@unlink($tmp);}
        }
    }

    public static function read(string $name): ?array
    {
        $file=self::file($name);
        if(!file_exists($file)&&!is_link($file)){return null;}
        clearstatcache(true,$file);
        if(is_link($file)||!is_file($file)||filesize($file)>2048||(fileperms($file)&0077)!==0){throw new RuntimeException('Task heartbeat is not protected.');}
        $data=json_decode((string)file_get_contents($file),true,8,JSON_THROW_ON_ERROR);
        $ran=$data['ran_at']??null;
        if(!is_string($ran)||strtotime($ran)===false){throw new RuntimeException('Task heartbeat is invalid.');}
        return ['ran_at'=>$ran,'age_seconds'=>max(0,time()-(int)strtotime($ran))];
    }
}
