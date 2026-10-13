<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{Bookings,Input,Newsletter,PrivateValue,SmtpMailer};
$checks=0;
function check(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(Throwable $e){check(true,$label);return;}check(false,$label);}
$key=random_bytes(32);$plain="SMTP secret with unicode é and quotes '\"";$sealed=PrivateValue::seal($plain,$key);
check(PrivateValue::open($sealed,$key)===$plain,'Authenticated encryption round-trips secret bytes');
check(!str_contains($sealed,$plain)&&PrivateValue::seal($plain,$key)!==$sealed,'Secret values use different random nonces');
rejects(fn()=>PrivateValue::open($sealed,random_bytes(32)),'Wrong key cannot decrypt a service secret');
$raw=base64_decode(substr($sealed,3));$raw[30]=chr(ord($raw[30])^1);
rejects(fn()=>PrivateValue::open('v1:'.base64_encode($raw),$key),'Tampered ciphertext is refused');
foreach(['','v1:no*base64','v1:'.base64_encode('short'),'v2:abc'] as $bad){rejects(fn()=>PrivateValue::open($bad,$key),'Malformed encrypted value is refused');}
$data=['name'=>'Test organizer','email'=>'test@example.test','location'=>'Fixture location','contact_consent'=>'1'];
check(Bookings::validate($data)['audience']===0,'Minimal inquiry accepts unknown audience and date');
check(Bookings::validate($data)['contact_consent_version']==='booking-contact-2026-10-13-v1','Inquiry records the contact-consent version');
foreach([['email'=>"bad\r\nBcc: evil@example.test"],['name'=>['bad']],['name'=>str_repeat('x',121)],['preferred_date'=>'2027-02-30'],['event_type'=>'unknown'],['audience'=>'-1'],['audience'=>'1e3'],['contact_consent'=>'0'],['message'=>"bad\0text"]] as $change){rejects(fn()=>Bookings::validate(array_merge($data,$change)),'Invalid inquiry input is refused');}
check(Input::date('2028-02-29')==='2028-02-29','Valid leap date accepted');
check(Newsletter::validToken(str_repeat('a',64))&&!Newsletter::validToken(str_repeat('a',63))&&!Newsletter::validToken('../token'),'Only high-entropy-format subscription tokens accepted');
check(Newsletter::csvValue('=HYPERLINK("x")')[0]==="'"&&Newsletter::csvValue('  +formula')[0]==="'",'CSV export neutralizes formula prefixes');
$options=['from_email'=>'band@example.test','from_name'=>str_repeat('É',50)];
$message=['recipient'=>'fan@example.test','subject'=>str_repeat('É',90),'html'=>'<p>Hello &amp; welcome.</p><p>https://example.test/confirm</p>','id'=>1,'dedupe_key'=>'unit:1'];
$mime=SmtpMailer::mime($message,$options);
check(str_contains($mime,'multipart/alternative')&&str_contains($mime,'Content-Type: text/plain')&&str_contains($mime,'Content-Type: text/html'),'Mail has both plain-text and HTML alternatives');
preg_match_all('/=\?UTF-8\?B\?([^?]+)\?=/',$mime,$matches);
check(count($matches[0])>2&&max(array_map('strlen',$matches[0]))<=75,'Long Unicode headers fold into bounded encoded words');
check(array_reduce($matches[1],fn($ok,$word)=>$ok&&(bool)preg_match('//u',base64_decode($word)),true),'Header folding preserves UTF-8 code points');
rejects(fn()=>SmtpMailer::mime(array_merge($message,['subject'=>"Test\r\nBcc: other@example.test"]),$options),'MIME header injection rejected');
check(str_contains($mime,'@example.test>')&&!str_contains($mime,'@spiritualgorillaz.local>'),'Message ID uses the validated sender domain');
echo $checks." communication unit checks passed.\n";
