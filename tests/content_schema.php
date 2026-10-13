<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\ContentSchema as S;
$checks=0;$failures=0;
function verify(string $label,callable $test):void{global $checks,$failures;$checks++;try{if($test()!==true){throw new RuntimeException('Assertion failed');}echo 'PASS '.$label."\n";}catch(Throwable $e){$failures++;echo 'FAIL '.$label.': '.$e->getMessage()."\n";}}
function rejectsSchema(callable $call):bool{try{$call();return false;}catch(InvalidArgumentException $e){return true;}}
verify('Schema rejects unknown content kinds',fn()=>rejectsSchema(fn()=>S::fields('php')));
verify('Slug generated safely from a title',fn()=>S::slug('','Music & Purpose!')==='music-purpose');
verify('Slug rejects traversal',fn()=>rejectsSchema(fn()=>S::slug('../private','title')));
verify('Slug rejects overlong names',fn()=>rejectsSchema(fn()=>S::slug(str_repeat('a',91),'title')));
verify('HTTPS ticket link retains query parameters',fn()=>S::url('https://tickets.example.test/show?id=1')==='https://tickets.example.test/show?id=1');
verify('JavaScript URL refused',fn()=>rejectsSchema(fn()=>S::url('javascript:alert(1)')));
verify('Plain HTTP external link refused',fn()=>rejectsSchema(fn()=>S::url('http://example.test')));
verify('URL credentials refused',fn()=>rejectsSchema(fn()=>S::url('https://user:pass@example.test')));
verify('Missing optional URL accepted',fn()=>S::url('')==='');
verify('Draft track can remain incomplete',fn()=>S::validate('track',[],false)['audio_id']===0);
verify('Published track needs recording and release',fn()=>rejectsSchema(fn()=>S::validate('track',[],true)));
verify('Gallery publication requires selected image',fn()=>rejectsSchema(fn()=>S::validate('gallery',[],true)));
verify('Valid track duration accepted',fn()=>S::validate('track',['duration'=>'3:42'],false)['duration']==='3:42');
verify('Invalid seconds refused',fn()=>rejectsSchema(fn()=>S::validate('track',['duration'=>'3:99'],false)));
verify('Invalid release calendar date refused',fn()=>rejectsSchema(fn()=>S::validate('release',['release_date'=>'2026-02-30'],false)));
verify('Unknown event timezone refused',fn()=>rejectsSchema(fn()=>S::validate('event',['timezone'=>'Mars/Crater'],false)));
verify('Nonexistent daylight-saving local time refused',fn()=>rejectsSchema(fn()=>S::validate('event',['starts_at'=>'2026-03-08T02:30','timezone'=>'America/Chicago'],false)));
verify('Event local time converted to UTC',fn()=>S::validate('event',['starts_at'=>'2026-10-13T20:00','timezone'=>'America/Chicago'],false)['starts_utc']==='2026-10-14 01:00:00');
verify('Publication needs venue and city',fn()=>rejectsSchema(fn()=>S::validate('event',['starts_at'=>'2099-11-08T20:00'],true)));
verify('Empty ticket URL allowed without a fake buy button',fn()=>S::validate('event',['starts_at'=>'2099-11-08T20:00','venue'=>'Test venue','city'=>'Test city'],true)['ticket_url']==='');
verify('Unknown status refused',fn()=>rejectsSchema(fn()=>S::validate('event',['event_state'=>'approved_payment'],false)));
verify('Arbitrary video host refused',fn()=>rejectsSchema(fn()=>S::validate('video',['url'=>'https://other.example.test/video'],true)));
verify('YouTube video link accepted',fn()=>S::validate('video',['url'=>'https://www.youtube.com/watch?v=example'],true)['url']!=='');
verify('NUL bytes refused in content',fn()=>rejectsSchema(fn()=>S::validate('page',['body'=>"safe\0unsafe"],false)));
verify('Media references extract only declared fields',fn()=>S::mediaIds('page',['image_id'=>7,'audio_id'=>8])===[7=>'image']);
verify('Content output escapes active markup',fn()=>e('<img src=x onerror=alert(1)>')==='&lt;img src=x onerror=alert(1)&gt;');
echo $checks.' schema checks; '.$failures." failures.\n";exit($failures?1:0);
