<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{Metadata as M,ContentSchema as S};
$n=0;
function check(bool $ok,string $label):void{global $n;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$n++;}
function rejects(callable $fn,string $label):void{try{$fn();}catch(InvalidArgumentException $e){check(true,$label);return;}check(false,$label);}
check(M::text("  Intro\n  <b>approved</b> story ")==='Intro approved story','Metadata collapses whitespace and strips HTML tags');
check(M::text(str_repeat('é',200),180)===str_repeat('é',180),'Unicode description truncation never splits a code point');
check(M::text("bad\xff")==='','Malformed legacy UTF-8 metadata is not reflected');
rejects(fn()=>M::text('x',0),'Metadata length is bounded');
$json=M::json(['name'=>'</script><script>alert("x")</script>','description'=>'A & B']);
check(!str_contains($json,'</script>')&&str_contains($json,'\\u003C'),'Structured JSON cannot close its non-executable script element');
check(json_decode($json,true,64,JSON_THROW_ON_ERROR)['name']==='</script><script>alert("x")</script>','Safe JSON still preserves the approved literal content');
$d=S::validate('event',['starts_at'=>'2099-11-08T20:00','timezone'=>'America/Chicago','venue'=>'Fixture Hall','city'=>'Fixture City','address'=>'123 Synthetic Avenue','country'=>'United States','region'=>'Louisiana','postal_code'=>'70500','event_access'=>'public','description'=>'Fixture only'],true);
$record=['kind'=>'event','title'=>'Synthetic event','data'=>$d];$schema=M::event($record,'https://example.test/shows/event.php?slug=fixture');
check($schema['@type']==='MusicEvent'&&$schema['location']['address']['addressCountry']==='United States','Confirmed public physical event uses supplied structured venue information');
check(str_ends_with($schema['startDate'],'-06:00'),'Structured start date includes the actual named-zone offset');
check(!isset($schema['offers'],$schema['aggregateRating'],$schema['performer']),'No ticket prices, ratings or performer assertions are fabricated');
foreach(['cancelled'=>'EventCancelled','postponed'=>'EventPostponed','sold_out'=>'EventScheduled'] as $state=>$expected){$copy=$record;$copy['data']['event_state']=$state;$value=M::event($copy,'https://example.test/event');check(str_ends_with($value['eventStatus'],$expected)&&$value['startDate']===$schema['startDate'],'Status change retains original dates: '.$state);}
foreach(['unconfirmed','invitation'] as $state){$copy=$record;$copy['data']['event_access']=$state;check(M::event($copy,'https://example.test/event')===null,'Unconfirmed/private audience is not advertised as public structured event');}
$copy=$record;$copy['kind']='page';check(M::event($copy,'https://example.test/page')===null,'Non-event pages cannot generate event markup');
foreach(['address','country'] as $missing){$input=$d;$input[$missing]='';rejects(fn()=>S::validate('event',$input,true),'Confirmed public publication requires '.$missing);}
foreach(['page','release','member'] as $kind){$fields=S::fields($kind);check(isset($fields['seo_title'],$fields['seo_description'],$fields['share_image_id'],$fields['seo_visibility']),'Structured optional metadata is editable for '.$kind);}
foreach([['seo_title'=>['bad']],['seo_description'=>str_repeat('a',321)],['share_image_id'=>['bad']],['seo_visibility'=>'private-secret']] as $input){rejects(fn()=>S::validate('page',$input,false),'Malformed metadata fields are rejected');}
check(S::validate('page',[],false)['seo_visibility']==='include','Old published records retain their previous visibility default');
echo $n." discovery unit checks passed.\n";
