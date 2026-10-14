<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{CpanelClient,CpanelProvisioner,CpanelRejected};
$count=0;
function check(bool $ok,string $label):void{global $count;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$count++;}
function rejects(callable $call,string $label):void{try{$call();}catch(Throwable $e){check(true,$label);return;}check(false,$label);}
$c=['host'=>'server.example.com','user'=>'cpuser','token'=>str_repeat('A',32)];
check(CpanelClient::validate($c)===$c,'cPanel hostname/account/token validate separately from database and owner credentials');
foreach(['http://server.example.com','https://server.example.com:2083','server.example.com/path','127.0.0.1','169.254.169.254','2130706433','localhost','server.local','foo..com',"bad\r\nHost:x.com",'server.example.com@evil.com'] as $host){rejects(fn()=>CpanelClient::validate(array_replace($c,['host'=>$host])),'Malformed or unsafe cPanel destination refused: '.json_encode($host));}
foreach(['root@example.com','../user',"cpuser\r\nX:",'USER','a:b'] as $user){rejects(fn()=>CpanelClient::validate(array_replace($c,['user'=>$user])),'Invalid cPanel account rejected');}
foreach(['','short',str_repeat('A',513),str_repeat('A',32)."\n",str_repeat('A',32).':x',['bad']] as $token){rejects(fn()=>CpanelClient::validate(array_replace($c,['token'=>$token])),'Malformed panel token rejected without header injection');}
foreach(['127.0.0.1','10.0.0.1','172.16.0.1','192.168.0.1','169.254.169.254','100.64.0.1','198.18.0.1','192.0.2.1','203.0.113.1','224.0.0.1','0.0.0.0','::1','::ffff:127.0.0.1'] as $ip){check(!CpanelClient::publicAddress($ip),'Nonpublic or unsupported resolved address rejected: '.$ip);}
check(CpanelClient::publicAddress('1.1.1.1'),'Public IPv4 address remains supported');
$direct=['status'=>1,'data'=>['prefix'=>'cpuser_','max_database_name_length'=>64,'max_username_length'=>16],'errors'=>null];
check(CpanelClient::decode(json_encode($direct),'get_restrictions')===$direct['data'],'Direct /execute response is recognized');
check(CpanelClient::decode(json_encode(['apiversion'=>3,'module'=>'Mysql','func'=>'get_restrictions','result'=>$direct]),'get_restrictions')===$direct['data'],'Documented UAPI envelope is recognized');
foreach(['bad','null','[]',json_encode(['status'=>'1','data'=>[]]),json_encode(['status'=>1]),json_encode(['status'=>1,'errors'=>['secret'],'data'=>null]),json_encode(['module'=>'Other','result'=>$direct]),str_repeat('x',1048577)] as $body){rejects(fn()=>CpanelClient::decode($body,'get_restrictions'),'Malformed, mismatched or oversized API result cannot indicate success');}
try{CpanelClient::decode('{"status":0,"errors":["PASSWORD_SECRET"],"data":null}','create_user');check(false,'Rejection expected');}catch(CpanelRejected $e){check(!str_contains($e->getMessage(),'PASSWORD_SECRET'),'Provider rejection does not reflect raw credential-bearing diagnostics');}
$a=CpanelProvisioner::names($direct['data']);$b=CpanelProvisioner::names($direct['data']);
check($a['name']!==$b['name']&&$a['user']===$a['name']&&strlen($a['name'])<=16&&str_starts_with($a['name'],'cpuser_'),'Generated names respect returned prefix and tighter user length');
check(strlen($a['password'])>=40&&$a['password']!==$b['password'],'Dedicated database password uses fresh cryptographic randomness');
check(!str_contains(CpanelProvisioner::PRIVILEGES,'ALL')&&!str_contains(CpanelProvisioner::PRIVILEGES,'GRANT'),'Grant list contains only required database-scoped privileges');
foreach([[],['prefix'=>'bad/path','max_database_name_length'=>64,'max_username_length'=>16],['prefix'=>'cpuser_','max_database_name_length'=>64,'max_username_length'=>'16'],['prefix'=>'longprefix_','max_database_name_length'=>64,'max_username_length'=>16]] as $bad){rejects(fn()=>CpanelProvisioner::names($bad),'Unsupported provider restrictions fail before resource creation');}
check(str_starts_with(CpanelProvisioner::names(['prefix'=>null,'max_database_name_length'=>64,'max_username_length'=>32])['name'],'s'),'Disabled database prefixing is supported explicitly');
echo $count." cPanel input and contract checks passed.\n";
