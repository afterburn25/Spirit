<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;
use ZipArchive;
use Throwable;

/** Bounded streaming validation; never extracts an archive with extractTo().
 * Hashes prove byte consistency, NOT publisher authenticity. Only an explicitly
 * trusted owner-supplied distribution is eligible to become executable code.
 */
final class UpdatePackage
{
    public const PINNED=['.htaccess','includes/maintenance-entry.php','admin/upgrades/RecoveryEngine.php','admin/upgrades/recover.php','admin/upgrades/.htaccess'];
    public static function validName(string $name): bool
    {
        return strlen($name)<=220 && (bool)preg_match('#^(?:[A-Za-z0-9_-]+/)*(?:[A-Za-z0-9_.-]+|\.htaccess)$#D',$name) && !str_contains($name,'..') && !str_contains($name,'//');
    }
    private static function allowed(string $name): bool
    {
        if(!self::validName($name)){return false;}
        if(in_array($name,self::PINNED,true)||\SpiritRecovery::mutable($name)){return true;}
        if(preg_match('#^(config|storage|uploads)/(\.htaccess|README\.md)$#D',$name)){return true;}
        if(preg_match('#^config/database/migrations/[0-9]{3}\.sql$#D',$name)||$name==='config/database/.htaccess'){return true;}
        return in_array($name,['install/index.php','install/.htaccess','install/README.md','install/access.php'],true);
    }
    public static function stage(string $archive,string $directory): array
    {
        if(!class_exists(ZipArchive::class)||!method_exists(ZipArchive::class,'getStreamIndex')){throw new RuntimeException('Enable the PHP ZIP extension with indexed stream support in your hosting panel before using browser upgrades.');}
        if(is_link($archive)||!is_file($archive)||filesize($archive)>33554432){throw new RuntimeException('Choose a full installation ZIP no larger than 32 MiB.');}
        $zip=new ZipArchive();if($zip->open($archive,ZipArchive::RDONLY|ZipArchive::CHECKCONS)!==true){throw new RuntimeException('This is not a readable ZIP distribution.');}
        $deadline=microtime(true)+12;$entries=[];$lower=[];$expanded=0;$staged=[];
        try{
            if($zip->numFiles<1||$zip->numFiles>3000){throw new RuntimeException('Unsupported archive file count.');}
            for($i=0;$i<$zip->numFiles;$i++){
                $stat=$zip->statIndex($i);$name=$stat['name']??'';
                if(!is_string($name)||!self::allowed($name)||isset($lower[strtolower($name)])){throw new RuntimeException('Archive has an unexpected, duplicate or unsafe path.');}
                $opsys=0;$attributes=0;
                if(!$zip->getExternalAttributesIndex($i,$opsys,$attributes)){throw new RuntimeException('Archive file attributes are unavailable.');}
                $kind=($attributes>>16)&0170000;
                if($kind!==0&&$kind!==0100000){throw new RuntimeException('Archive links, directories and special files are not accepted.');}
                $size=$stat['size']??-1;
                if(!is_int($size)||$size<0||$size>8388608||($stat['encryption_method']??0)!==0){throw new RuntimeException('Encrypted or oversized archive entry.');}
                $expanded+=$size;if($expanded>67108864){throw new RuntimeException('Archive expands beyond the safe update budget.');}
                $entries[$name]=['index'=>$i,'size'=>$size];$lower[strtolower($name)]=true;
            }
            if(!isset($entries['docs/PACKAGE-MANIFEST.json'],$entries['VERSION'])){throw new RuntimeException('Use a complete generated installation ZIP with its package manifest.');}
            if($entries['docs/PACKAGE-MANIFEST.json']['size']>524288){throw new RuntimeException('Package manifest is too large.');}
            $text=$zip->getFromIndex($entries['docs/PACKAGE-MANIFEST.json']['index']);
            $manifest=json_decode($text?:'',true,32,JSON_THROW_ON_ERROR);
            if(!is_array($manifest)||!is_array($manifest['files']??null)||count($manifest['files'])!==count($entries)-1){throw new RuntimeException('Package manifest does not cover the complete payload.');}
            $version=$manifest['version']??'';
            if(!is_string($version)||!preg_match('/^\d+\.\d+\.\d+(?:-[A-Za-z0-9]+(?:[.-][A-Za-z0-9]+)*)?$/D',$version)||!version_compare($version,Paths::version(),'>')){throw new RuntimeException('Select a newer version; same-version replacements and downgrades are refused.');}
            foreach($entries as $name=>$entry){
                if(microtime(true)>$deadline){throw new RuntimeException('Archive validation exceeded the browser time budget.');}
                $expected=$manifest['files'][$name]??null;
                if($name!=='docs/PACKAGE-MANIFEST.json'&&(!is_string($expected)||!preg_match('/^[a-f0-9]{64}$/D',$expected))){throw new RuntimeException('An archive entry is missing its checksum.');}
                $h=$zip->getStreamIndex($entry['index']);if(!$h){throw new RuntimeException('Cannot stream an archive entry.');}
                $body='';
                try{
                    while(!feof($h)){$chunk=fread($h,65536);if($chunk===false){throw new RuntimeException('Archive read failed.');}$body.=$chunk;if(strlen($body)>$entry['size']||microtime(true)>$deadline){throw new RuntimeException('Archive size or time limit exceeded.');}}
                }finally{fclose($h);}
                if(strlen($body)!==$entry['size']||($expected!==null&&!hash_equals($expected,hash('sha256',$body)))){throw new RuntimeException('An archive checksum or length does not match.');}
                if($name==='VERSION'&&trim($body)!==$version){throw new RuntimeException('Package version disagrees with its manifest.');}
                if(str_ends_with($name,'.php')){
                    if(strlen($body)>2097152){throw new RuntimeException('PHP entry is too large to validate safely.');}
                    try{token_get_all($body,TOKEN_PARSE);}catch(\ParseError $e){throw new RuntimeException('The package contains PHP syntax incompatible with this server.');}
                }
                if(in_array($name,self::PINNED,true)||str_starts_with($name,'config/database/')||(basename($name)==='.htaccess'&&!str_starts_with($name,'install/'))){
                    $original=Paths::root().'/'.$name;
                    if(is_link($original)||!is_file($original)||!hash_equals(hash_file('sha256',$original),hash('sha256',$body))){throw new RuntimeException('This package changes the pinned recovery layer or database schema. Browser code-only upgrade is refused before any application changes.');}
                }
                if($name==='includes/bootstrap.php'&&!str_starts_with($body,"<?php\ndeclare(strict_types=1);\nrequire_once __DIR__ . '/maintenance-entry.php';\n")){throw new RuntimeException('The early recovery guard must remain the first bootstrap action.');}
                if(\SpiritRecovery::mutable($name)){
                    $path=$directory.'/new/'.hash('sha256',$name);\SpiritRecovery::atomic($path,$body);$staged[$name]=hash('sha256',$body);
                }
            }
            foreach(array_merge(self::PINNED,['includes/bootstrap.php','index.php','VERSION','admin/upgrades/index.php','app/UpdateManager.php']) as $required){if(!isset($entries[$required])){throw new RuntimeException('The cumulative package is missing a required application component.');}}
            foreach(Migrations::files() as $migration){if(!isset($entries['config/database/migrations/'.basename($migration)])){throw new RuntimeException('Existing immutable migrations must be present in every update.');}}
            // A code-only upgrade never guesses which omitted scripts are safe
            // to delete. Require a cumulative package; preserve unrelated files.
            $oldManifest=Paths::root().'/docs/PACKAGE-MANIFEST.json';
            if(is_file($oldManifest)&&!is_link($oldManifest)){
                $previous=json_decode((string)file_get_contents($oldManifest),true,32,JSON_THROW_ON_ERROR);
                foreach(array_keys($previous['files']??[]) as $old){if(\SpiritRecovery::mutable($old)&&!isset($entries[$old])){throw new RuntimeException('A cumulative update cannot omit previously installed application files.');}}
            }
            return ['version'=>$version,'files'=>$staged,'expanded_bytes'=>$expanded];
        }finally{$zip->close();}
    }
}
