<?php
declare(strict_types=1);
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\{AuthorizeGateway,DeliveryPolicy,Money,PaymentSettings,WebhookInbox};
$checks=0;
function check(bool $ok,string $label):void{global $checks;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$checks++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(Throwable $e){check(true,$label);return;}check(false,$label);}
foreach(['0'=>0,'8.125'=>81250,'9.975'=>99750,'99.9999'=>999999] as $text=>$ppm){check(DeliveryPolicy::rate((string)$text)===$ppm,'Exact configured percentage '.$text);}
foreach(['-1','100','1e2','01.5','8.12345','8%',''] as $bad){rejects(fn()=>DeliveryPolicy::rate($bad),'Invalid percentage refused');}
$o=['configured'=>true,'shipping_cents'=>500,'rates'=>['LA'=>81250],'zip_rates'=>['LA:70501'=>100000],'shipping_taxable'=>false,'policy'=>'Synthetic terms'];
$a=['region'=>'LA','postal_code'=>'70501-1234'];$q=DeliveryPolicy::quote(5002,$a,$o);
check($q['shipping_cents']===500&&$q['tax_cents']===500&&$q['total_cents']===6002,'ZIP override and rounded integer tax included in review total');
$o['shipping_taxable']=true;check(DeliveryPolicy::quote(5002,$a,$o)['total_cents']===6052,'Shipping taxation follows explicit configuration');
check(DeliveryPolicy::quote(5002,['region'=>'LA','postal_code'=>'70000'],$o)['tax_cents']===447,'State fallback calculates exact fractional percentage');
rejects(fn()=>DeliveryPolicy::quote(1,['region'=>'TX','postal_code'=>'75001'],$o),'Unconfigured shipping state fails closed');
rejects(fn()=>DeliveryPolicy::quote(9999999,$a,$o),'Total above provider range is refused');
$valid=['recipient'=>'Fixture User','line_one'=>'123 Test Street','line_two'=>'','city'=>'Lafayette','region'=>'LA','postal_code'=>'70501','billing_same'=>'1'];
check(DeliveryPolicy::validate($valid)['billing_zip']==='70501','Matching billing address is computed server-side');
foreach([['recipient'=>''],['region'=>'XX'],['postal_code'=>'7050'],['city'=>"Bad\nCity"],['line_one'=>str_repeat('x',60),'line_two'=>'x'],['billing_same'=>'0']] as $bad){rejects(fn()=>DeliveryPolicy::validate(array_merge($valid,$bad)),'Malformed delivery or billing address rejected');}
$key=str_repeat('A1',64);$body='{ "notificationId": "fixture-123", "payload": { "id": "1234" } }';
// Independent standard HMAC operation; exact raw bytes, ASCII key, no JSON re-encoding.
$header='sha512='.hash_hmac('sha512',$body,$key);
check(WebhookInbox::validSignature($body,$header,$key),'Exact raw webhook and copied Signature Key authenticate');
check(WebhookInbox::validSignature($body,strtoupper($header),$key),'Signature hexadecimal letter case is accepted');
check(!WebhookInbox::validSignature($body.' ',$header,$key),'Whitespace mutation cannot reuse a webhook signature');
check(!WebhookInbox::validSignature($body,$header,str_repeat('B2',64)),'Another merchant Signature Key is rejected');
check(!WebhookInbox::validSignature($body,'sha512='.hash_hmac('sha512',$body,hex2bin($key)),$key),'Transaction-hash binary key convention is not accepted as webhook authentication');
foreach(['','sha256='.str_repeat('a',64),'sha512=bad',str_repeat('a',128)] as $bad){check(!WebhookInbox::validSignature($body,$bad,$key),'Malformed signature header rejected');}
$ok=['messages'=>['resultCode'=>'Ok'],'transactionResponse'=>['responseCode'=>'1','transId'=>'123456','accountNumber'=>'XXXX1111']];
check(AuthorizeGateway::result($ok)['state']==='succeeded','Only a positive provider transaction can succeed');
check(AuthorizeGateway::result($ok)['last_four']==='1111','Only masked last-four metadata is retained');
foreach([['messages'=>['resultCode'=>'Ok'],'transactionResponse'=>['responseCode'=>'1','transId'=>'0']],[],['messages'=>['resultCode'=>'Error']],['messages'=>['resultCode'=>'Ok'],'transactionResponse'=>['responseCode'=>'9','transId'=>'123']]] as $bad){check(AuthorizeGateway::result($bad)['state']==='uncertain','Unknown/malformed/test-mode reply never claims payment');}
check(AuthorizeGateway::result(['transactionResponse'=>['responseCode'=>'2','transId'=>'0']])['state']==='declined','Explicit decline is not a payment');
check(AuthorizeGateway::result(['transactionResponse'=>['responseCode'=>'4','transId'=>'123']])['state']==='held','Fraud-held transaction remains under review');
check(AuthorizeGateway::result(['transactionResponse'=>['responseCode'=>'3','transId'=>'0','errors'=>[['errorCode'=>'11']]]])['state']==='uncertain','Duplicate provider reply must be reconciled, not charged again');
foreach([0,-1,'0','123x',[],null] as $bad){check(AuthorizeGateway::transactionId($bad)===null,'Invalid provider transaction ID rejected');}
foreach(['25.01'=>2501,'0.00'=>0] as $value=>$cents){check(AuthorizeGateway::reportedCents($value)===$cents,'String API amount converted exactly');}
check(AuthorizeGateway::reportedCents(25.01)===2501,'Reporting JSON number normalized only at the trusted API boundary');
foreach([25.011,-1,INF,NAN,[],null] as $bad){rejects(fn()=>AuthorizeGateway::reportedCents($bad),'Invalid API amount refused without rounding it into a valid order');}
rejects(fn()=>PaymentSettings::mode('live'),'Unknown payment environment refused');
check(str_contains(PaymentSettings::script('sandbox'),'jstest.authorize.net')&&!str_contains(PaymentSettings::script('production'),'jstest'),'Payment environments use distinct official SDK hosts');
echo $checks." payment unit checks passed.\n";
