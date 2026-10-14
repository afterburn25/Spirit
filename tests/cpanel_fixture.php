<?php
declare(strict_types=1);
// Isolated cPanel boundary simulation using a disposable local SQL service.
// The root credential is used ONLY by this test-owned account simulator.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'||getenv('SPIRIT_DB_HOST')!=='127.0.0.1'||getenv('SPIRIT_DB_NAME')!=='spirit_ci'||!preg_match('/^sgci_[a-f0-9]{8}_$/D',(string)getenv('SPIRIT_DB_PREFIX'))){exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
$_SERVER['SCRIPT_FILENAME']=$root.'/install/index.php';$_SERVER['SCRIPT_NAME']=(string)getenv('SPIRIT_FIXTURE_SCRIPT_NAME');$_SERVER['HTTPS']='on';$_SERVER['REMOTE_ADDR']='127.0.0.1';$_SESSION=[];
require $root.'/includes/bootstrap.php';
use Spirit\{Installer,CpanelClient,Paths};
function check(bool $ok,string $label):void{if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";}
$mode=$argv[1]??'success';
if(!in_array($mode,['success','rejected','unknown','migration'],true)){exit(1);}
$adminPassword=getenv('SPIRIT_CPANEL_TEST_ADMIN_PASSWORD');
if($adminPassword!=='ephemeral-ci-root-not-production'){throw new RuntimeException('Missing explicit disposable SQL administrator.');}
$admin=new PDO('mysql:host=127.0.0.1;port='.(int)getenv('SPIRIT_DB_PORT').';charset=utf8mb4','root',$adminPassword,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$ownedDbs=[];$ownedUsers=[];$calls=[];$failed=false;$passwords=[];
$transport=static function(string $function,array $parameters)use($admin,&$ownedDbs,&$ownedUsers,&$calls,&$failed,&$passwords,$mode):string{
    $calls[]=$function;$data=null;$status=1;
    $safe=static function(string $name):string{if(!preg_match('/^spiritci_s[a-f0-9]{6,15}$/D',$name)){throw new RuntimeException('Refusing non-fixture resource.');}return '`'.$name.'`';};
    if($function==='get_restrictions'){$data=['prefix'=>'spiritci_','max_database_name_length'=>64,'max_username_length'=>32];}
    elseif($function==='list_databases'){$data=array_merge([['database'=>'unrelated_db','users'=>['unrelated_user']]],array_values($ownedDbs));}
    elseif($function==='list_users'){$data=array_merge([['user'=>'unrelated_user','databases'=>['unrelated_db']]],array_values($ownedUsers));}
    elseif($function==='create_database'){
        $name=$parameters['name'];$admin->exec('CREATE DATABASE '.$safe($name).' CHARACTER SET utf8mb4');$ownedDbs[$name]=['database'=>$name,'users'=>[]];
        if($mode==='unknown'&&!$failed){$failed=true;throw new RuntimeException('Synthetic loss after remote database creation with private response text.');}
    }elseif($function==='create_user'){
        if($mode==='rejected'&&!$failed){$failed=true;$status=0;}
        else{$name=$parameters['name'];$safe($name);$admin->exec('CREATE USER '.$admin->quote($name)."@'%' IDENTIFIED BY ".$admin->quote($parameters['password']));$ownedUsers[$name]=['user'=>$name,'databases'=>[]];$passwords[]=$parameters['password'];}
    }elseif($function==='set_privileges_on_database'){
        $db=$parameters['database'];$user=$parameters['user'];$safe($db);$safe($user);
        if(!isset($ownedDbs[$db],$ownedUsers[$user])||$parameters['privileges']!==Spirit\CpanelProvisioner::PRIVILEGES){throw new RuntimeException('Unsafe fixture grant request.');}
        $admin->exec('GRANT '.$parameters['privileges'].' ON '.$safe($db).'.* TO '.$admin->quote($user)."@'%'");$ownedDbs[$db]['users']=[$user];$ownedUsers[$user]['databases']=[$db];
    }else{throw new RuntimeException('Unexpected cPanel operation: '.$function);}
    return json_encode(['apiversion'=>3,'module'=>'Mysql','func'=>$function,'result'=>['status'=>$status,'data'=>$data,'errors'=>$status?null:['Synthetic quota error with a secret that must not be reflected.']]],JSON_THROW_ON_ERROR);
};
$credentials=['host'=>'cpanel.example.com','user'=>'spiritci','token'=>str_repeat('Z',32)];
$input=['db_mode'=>'cpanel','cpanel_consent'=>'1','cpanel'=>$credentials,'cpanel_db_host'=>'127.0.0.1','db'=>['prefix'=>getenv('SPIRIT_DB_PREFIX')],'site_url'=>'https://fixture.example.com'.dirname(dirname($_SERVER['SCRIPT_NAME'])),'owner_name'=>'Synthetic Owner','email'=>'owner@example.test','password'=>'Synthetic-owner-password-975!','password_confirm'=>'Synthetic-owner-password-975!'];
$input['site_url']=rtrim($input['site_url'],'/');
$client=static fn()=>new CpanelClient($credentials,$transport);
$journal=Paths::runtime().'/cpanel-provision.php';$schema=$root.'/database/migrations/001.sql';$original=file_get_contents($schema);
try{
    foreach([['cpanel_consent'=>'0'],['password_confirm'=>'Different-password-975!']] as $change){$denied=false;try{Installer::install(array_replace($input,$change),$client());}catch(Throwable $e){$denied=true;}check($denied&&!$calls&&!is_file($journal),'Owner validation/authorization must precede all cPanel resources');}
    if($mode==='migration'){file_put_contents($schema,$original."\nINVALID SQL FOR ISOLATED RECOVERY TEST;\n");}
    $error=null;try{$cleanup=Installer::install($input,$client());}catch(Throwable $e){$error=$e;}
    if($mode!=='success'){
        check($error!==null&&!is_file(Paths::runtime().'/config.php'),'Interrupted provisioning cannot claim an installed website');
        check(is_file($journal)&&(fileperms($journal)&0777)===0600,'Generated credentials/state are saved only in a private recovery file');
        check(!str_contains(file_get_contents($journal),$credentials['token'])&&!str_contains($error->getMessage(),'secret that must'),'Panel token and raw provider diagnostics are not retained or reflected');
        $before=$calls;$mismatch=false;try{Installer::install(array_replace($input,['email'=>'other@example.test']),$client());}catch(Throwable $e){$mismatch=true;}
        check($mismatch&&$calls===$before,'A retry cannot silently change owner binding or create resources in another account');
        if($mode==='unknown'){
            $paused=false;try{Installer::install($input,$client());}catch(Throwable $e){$paused=str_contains($e->getMessage(),'uncertain');}
            check($paused&&count(array_filter($calls,fn($c)=>$c==='create_database'))===1&&count($ownedUsers)===0,'Lost create acknowledgement pauses instead of recreating or adopting a database');
            check(!is_file(Paths::runtime().'/config.php'),'An uncertain cPanel operation cannot complete the installation');
        }else{
            if($mode==='migration'){file_put_contents($schema,$original);}
            $cleanup=Installer::install($input,$client());
            check(count(array_filter($calls,fn($c)=>$c==='create_database'))===1&&count($passwords)===1,'Retry resumes its confirmed resources without new databases or password changes');
        }
    }else{check($error===null,'Synthetic cPanel API plus real SQL completes installation'.($error!==null?' ['.get_class($error).': '.$error->getMessage().']':''));}
    if($mode!=='unknown'){
        $config=Paths::config();$sql=Spirit\Database::connect();
        check($config!==null&&$config['db']['password']===$passwords[0]&&$config['db']['user']===array_key_first($ownedUsers),'Generated dedicated credentials connect through real PDO');
        check((int)$sql->query('SELECT COUNT(*) FROM '.Spirit\Database::table('users'))->fetchColumn()===1,'Automatic provisioning installs exactly one owner');
        check($cleanup['removed']&&!is_dir($root.'/install')&&!is_file($journal),'Successful setup deletes the installer and transient provisioning record');
        check(!str_contains(file_get_contents(Paths::runtime().'/config.php'),$credentials['token']),'Permanent application configuration never contains the cPanel token');
        $before=$calls;$locked=false;try{Installer::install($input,$client());}catch(Throwable $e){$locked=true;}
        check($locked&&$before===$calls,'Completed setup is locked before any new provider calls');
    }
    check(!array_intersect($calls,['delete_database','delete_user','set_password']),'No destructive resource or password-reset API is available');
}finally{
    file_put_contents($schema,$original);
    // Delete only resources this isolated test invocation explicitly created.
    foreach(array_keys($ownedDbs) as $name){$admin->exec('DROP DATABASE `'.$name.'`');}
    foreach(array_keys($ownedUsers) as $name){$admin->exec('DROP USER '.$admin->quote($name)."@'%'");}
}
echo 'cPanel boundary scenario '.$mode." passed; no A2 account was contacted.\n";
