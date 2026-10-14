<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{HostingLimits,LaunchChecks};
$count=0;
function verify(bool $ok,string $label):void {global $count;if(!$ok){throw new RuntimeException($label);}$count++;echo 'PASS '.$label."\n";}
foreach(['128M'=>134217728,'2g'=>2147483648,'4096'=>4096,'0'=>0,'-1'=>-1,'0x10K'=>16384,'010'=>8,'0b10M'=>2097152] as $value=>$expected){verify(HostingLimits::bytes((string)$value)===$expected,'PHP quantity interpreted: '.$value);}
foreach([false,'','1.5M','2MB','unknown','/private/path','-2','99999999999999999999999G','09'] as $value){verify(HostingLimits::bytes($value)===null,'Unknown or invalid PHP quantity is not invented');}
verify(HostingLimits::label(null)==='Unknown'&&HostingLimits::label(-1)==='No PHP limit','Unknown and explicitly unlimited limits are distinguished');
verify(str_contains(HostingLimits::label(1048576),'1.00 MiB'),'Quantity labels use binary units');
foreach([['101',100,true],['100',100,false],['000101',100,true],['0',100,false],['99999999999999999999999999999999',100,true],['-1',100,false],['1e3',100,false],[[101],100,false],['999',0,false],['999',-1,false],['999',null,false]] as [$length,$limit,$expected]){verify(HostingLimits::exceedsPost($length,$limit)===$expected,'Whole-body comparison is bounded and overflow-safe');}
$secret='PRIVATE-DIAGNOSTIC-SENTINEL';
$snapshot=['version'=>$secret,'checked_at'=>$secret,'runtime'=>['hostname'=>$secret,'path'=>$secret,'db'=>$secret,'https'=>true,'extensions'=>['curl'=>true,'zip'=>false,$secret=>true],'limits'=>['file_uploads'=>true,'upload_max_bytes'=>1048576,'post_max_bytes'=>2097152,'memory_limit_bytes'=>-1,'token'=>$secret]],'checks'=>[['id'=>'smtp','state'=>'review','detail'=>$secret,'path'=>$secret],['id'=>$secret,'state'=>'attention'],['id'=>'payments','state'=>[$secret]],'malformed'],'credentials'=>$secret,'counts'=>[$secret=>12]];
$report=LaunchChecks::supportReport($snapshot);$json=json_encode($report,JSON_THROW_ON_ERROR);
verify(!str_contains($json,$secret),'Support allowlist excludes injected identity, paths, credentials, content and unknown check IDs');
verify($report['check_states']===['smtp'=>'review'],'Support report contains only fixed check IDs and valid states');
verify(array_keys($report['extensions'])===['pdo_mysql','openssl','fileinfo','gd','curl','zip'],'Report extension names are explicitly bounded');
verify($report['extensions']['curl']&&!$report['extensions']['zip'],'Report preserves observed optional-extension differences');
verify(array_keys($report['limits'])===['file_uploads','upload_max_bytes','post_max_bytes','memory_limit_bytes'],'Report limits cannot become a raw phpinfo dump');
verify(str_contains($report['scope'],'No provider')&&!array_key_exists('ready',$report),'A support report cannot certify launch or provider acceptance');
verify(!str_contains(HostingLimits::oversizedMessage(),'form expired')&&str_contains(HostingLimits::oversizedMessage(),'no requested action'),'Oversized request is distinguished from CSRF failure');
echo $count." launch/hosting pure assertions passed.\n";
