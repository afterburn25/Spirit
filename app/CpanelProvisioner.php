<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** Called under the install-operation lock; no reset, delete or root APIs. */
final class CpanelProvisioner
{
    public const PRIVILEGES='SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,DROP,INDEX,REFERENCES';
    private const FILE='cpanel-provision.php';
    public static function exists(): bool {return file_exists(Paths::runtime().'/'.self::FILE)||is_link(Paths::runtime().'/'.self::FILE);}
    public static function names(array $restrictions): array
    {
        if(!array_key_exists('prefix',$restrictions)){throw new RuntimeException('cPanel did not provide the database naming restrictions.');}
        $prefix=$restrictions['prefix']??'';$databaseMax=$restrictions['max_database_name_length']??null;$userMax=$restrictions['max_username_length']??null;
        if(!is_string($prefix)||($prefix!==''&&!preg_match('/^[a-z][a-z0-9]{0,15}_$/D',$prefix))||!is_int($databaseMax)||!is_int($userMax)||$databaseMax<8||$databaseMax>64||$userMax<8||$userMax>128){throw new RuntimeException('The cPanel naming rules are not supported safely. Use an existing database instead.');}
        $available=min(16,$databaseMax-strlen($prefix),$userMax-strlen($prefix));
        if($available<7){throw new RuntimeException('The cPanel naming limits leave too little space for a safe unique database/user pair.');}
        $name=$prefix.'s'.substr(bin2hex(random_bytes(16)),0,$available-1);
        return ['name'=>$name,'user'=>$name,'password'=>'Aa9!'.rtrim(strtr(base64_encode(random_bytes(32)),'+/','-_'),'=')];
    }
    private static function inventory(CpanelClient $client): array
    {
        $databases=$client->call('list_databases');$users=$client->call('list_users');$dbMap=[];$userMap=[];
        foreach([[$databases,'database'],[$users,'user']] as [$rows,$key]){
            if(!is_array($rows)||!array_is_list($rows)||count($rows)>5000){throw new RuntimeException('cPanel returned an unsupported resource inventory.');}
            foreach($rows as $row){if(!is_array($row)||!is_string($row[$key]??null)){throw new RuntimeException('cPanel returned an incomplete resource inventory.');}}
        }
        foreach($databases as $row){$dbMap[$row['database']]=$row;}
        foreach($users as $row){$userMap[$row['user']]=$row;}
        return [$dbMap,$userMap];
    }
    private static function load(): ?array
    {
        $path=Paths::runtime().'/'.self::FILE;
        if(!self::exists()){return null;}
        if(is_link($path)||!is_file($path)||(fileperms($path)&0777)!==0600){throw new RuntimeException('The private cPanel recovery record needs owner inspection.');}
        $record=require $path;
        if(!is_array($record)||($record['version']??null)!==1||!is_array($record['identity']??null)||!is_array($record['db']??null)||!is_string($record['binding']??null)||!is_string($record['phase']??null)||!is_array($record['done']??null)){throw new RuntimeException('The private cPanel recovery record is invalid. Do not delete it to force a retry.');}
        return $record;
    }
    private static function save(array $state): void {Filesystem::atomicPhp(Paths::runtime().'/'.self::FILE,$state);}
    public static function provision(CpanelClient $client,string $prefix,string $email,string $url,string $databaseHost='localhost'): array
    {
        if(!in_array($databaseHost,['localhost','127.0.0.1'],true)){throw new InvalidArgumentException('Automatic setup supports only local MySQL. Use an existing database for a remote server.');}
        $identity=$client->identity();$binding=hash('sha256',$email."\0".$url."\0".$prefix."\0".$databaseHost);$state=self::load();
        if($state!==null&&($state['identity']!==$identity||!hash_equals($state['binding'],$binding))){throw new RuntimeException('Resume automatic setup with the original cPanel account, hostname, website URL, owner email and table prefix. Existing resources were left untouched.');}
        // Read-only authenticated operations precede every fresh or resumed mutation.
        $restrictions=$client->call('get_restrictions');
        if(!is_array($restrictions)){throw new RuntimeException('cPanel database features are unavailable for this account.');}
        [$databases,$users]=self::inventory($client);
        if($state===null){
            if(is_file(Paths::runtime().'/pending.php')||is_link(Paths::runtime().'/pending.php')){throw new RuntimeException('An existing-database installation is already pending. Resume its original database settings.');}
            for($i=0;$i<8;$i++){$generated=self::names($restrictions);if(!isset($databases[$generated['name']])&&!isset($users[$generated['user']])){break;}}
            if(isset($databases[$generated['name']])||isset($users[$generated['user']])){throw new RuntimeException('Unable to select unused cPanel database names. No resources were changed.');}
            $state=['version'=>1,'identity'=>$identity,'binding'=>$binding,'db'=>Database::validate($generated+['host'=>$databaseHost,'port'=>3306,'prefix'=>$prefix]),'phase'=>'prepared','done'=>['database'=>false,'user'=>false,'privileges'=>false],'created_at'=>gmdate('c')];self::save($state);
        }
        if(!in_array($state['phase'],['prepared','confirmed','rejected','ready'],true)){throw new RuntimeException('A previous cPanel request has an uncertain result. Automatic creation is paused to avoid duplicates or changing unrelated resources. Ask A2 support to inspect the private cpanel-provision.php record and the cPanel database list; no passwords are reset or resources deleted by a retry.');}
        $db=Database::validate($state['db']);
        foreach(['database'=>isset($databases[$db['name']]),'user'=>isset($users[$db['user']])] as $kind=>$present){
            if(!is_bool($state['done'][$kind]??null)||$state['done'][$kind]!==$present){throw new RuntimeException('The cPanel resource list does not match the saved installation state. Existing resources were left untouched; inspect the private recovery record in the hosting account.');}
        }
        if(isset($users[$db['user']])){
            $assigned=$users[$db['user']]['databases']??null;
            if(!is_array($assigned)||array_diff($assigned,[$db['name']])){throw new RuntimeException('The generated database user is associated with another database. No privileges were changed.');}
        }
        foreach(['database'=>['create_database',['name'=>$db['name']]],'user'=>['create_user',['name'=>$db['user'],'password'=>$db['password']]],'privileges'=>['set_privileges_on_database',['database'=>$db['name'],'user'=>$db['user'],'privileges'=>self::PRIVILEGES]]] as $step=>[$function,$parameters]){
            if(($state['done'][$step]??false)===true){continue;}
            $state['phase']=$step.'_in_flight';self::save($state);
            try{$client->call($function,$parameters);}
            catch(CpanelRejected $e){$state['phase']='rejected';self::save($state);throw $e;}
            catch(Throwable $e){
                // In-flight is durable before a potentially irreversible request.
                // No blind repeat after timeout, lost reply or process termination.
                throw new RuntimeException('The cPanel '.$step.' result could not be confirmed. The saved attempt is paused for inspection; retrying cannot create a second pair or reset a password.');
            }
            $state['done'][$step]=true;$state['phase']='confirmed';self::save($state);
        }
        $state['phase']='ready';self::save($state);
        return $db;
    }
    public static function completed(): void
    {
        $path=Paths::runtime().'/'.self::FILE;
        if(!is_link($path)&&is_file($path)&&!@unlink($path)){error_log('Spirit: completed private database-provisioning receipt retained; no panel token is stored.');}
    }
}
