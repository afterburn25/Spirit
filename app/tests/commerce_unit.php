<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\{Addresses,ContentSchema,Money};
$checks=0;
function check(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(Throwable $e){check(true,$label);return;}check(false,$label);}
foreach(['0'=>0,'0.01'=>1,'19.9'=>1990,'25.00'=>2500,'99999.99'=>9999999] as $text=>$cents){check(Money::parse((string)$text)===$cents,'Exact decimal conversion '.$text);check(Money::parse(Money::decimal($cents))===$cents,'Minor-unit roundtrip '.$cents);}
foreach(['-1','+1','1e3','01.00','1.001','100000','$25','1,000','NaN','INF','',"2\n",'2.'] as $bad){rejects(fn()=>Money::parse($bad),'Invalid money syntax refused');}
check(Money::format(2501)==='$25.01','USD formatting has exactly two decimal places');
rejects(fn()=>Money::decimal(-1),'Negative price rejected');
check(ContentSchema::slug('','Test T-Shirt')==='test-t-shirt','Product URL uses existing validated slug rules');
$a=['label'=>'Home','recipient'=>'Fixture User','line_one'=>'123 Test Street','line_two'=>'','city'=>'Lafayette','region'=>'LA','postal_code'=>'70501','country_code'=>'US'];
check(Addresses::validate($a)['postal_code']==='70501','US address validation accepts complete fields');
check(Addresses::validate(array_merge($a,['postal_code'=>'70501-1234']))['postal_code']==='70501-1234','ZIP plus four accepted');
foreach([['recipient'=>''],['line_one'=>['not text']],['postal_code'=>'7050'],['country_code'=>'CA'],['city'=>"Bad\r\nCity"],['region'=>str_repeat('X',101)],['line_two'=>"bad\0line"]] as $change){rejects(fn()=>Addresses::validate(array_merge($a,$change)),'Malformed or unsupported address rejected');}
echo $checks." commerce unit checks passed.\n";
