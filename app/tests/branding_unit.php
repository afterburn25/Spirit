<?php
declare(strict_types=1);
$_SERVER['SCRIPT_NAME']='/tests/branding_unit.php'; $_SERVER['SCRIPT_FILENAME']=__FILE__;
require dirname(__DIR__, 2).'/includes/bootstrap.php';
use Spirit\Branding as B;
$count=0;
function check(bool $ok,string $label):void{global $count;if(!$ok){throw new RuntimeException($label);}echo 'PASS '.$label."\n";$count++;}
function rejected(callable $fn,string $label):void{try{$fn();}catch(InvalidArgumentException|RuntimeException $e){check(true,$label);return;}throw new RuntimeException('Accepted '.$label);}
check(B::decode(null)===B::EMPTY_STATE,'Absent branding remains a read-only bundled-artwork default');
check(B::decode(json_encode(B::EMPTY_STATE))===B::EMPTY_STATE,'Canonical branding state round-trips');
check(B::decodeSource(null)==='bundled','An absent source selects the approved artwork without rewriting a legacy branding record');
check(B::decodeSource('bundled')==='bundled'&&B::decodeSource('custom')==='custom','The explicit source accepts only supported identities');
foreach(['','auto','1','"custom"','https://example.test/logo.png'] as $raw){rejected(fn()=>B::decodeSource($raw),'Malformed stored source cannot expose retained custom media');}
foreach(['', '{}','[]','null','"logo"','{"revision":0,"draft_logo_id":1,"published_logo_id":"1"}','{"revision":-1,"draft_logo_id":0,"published_logo_id":0}','{"revision":0,"draft_logo_id":0,"published_logo_id":0,"extra":1}'] as $raw){rejected(fn()=>B::decode($raw),'Malformed persisted state is rejected');}
$draft=B::transition(B::EMPTY_STATE,['revision'=>'0','logo_id'=>'7'],'save');
check($draft===['revision'=>1,'draft_logo_id'=>7,'published_logo_id'=>0],'Save creates a private draft, never a public reference');
rejected(fn()=>B::transition($draft,['revision'=>'0','logo_id'=>'8'],'save'),'Stale revision rejected');
rejected(fn()=>B::transition($draft,['revision'=>'1','logo_id'=>'7'],'publish'),'Publication requires explicit approval');
$live=B::transition($draft,['revision'=>'1','logo_id'=>'7','approved'=>'1'],'publish');
check($live===['revision'=>2,'draft_logo_id'=>7,'published_logo_id'=>7],'Explicit approval publishes the selected image');
$new=B::transition($live,['revision'=>'2','logo_id'=>'8'],'save');
check($new['draft_logo_id']===8&&$new['published_logo_id']===7,'Replacement draft leaves current publication alone');
$builtin=B::transition($new,['revision'=>'3','logo_id'=>'999'],'builtin');
check($builtin===['revision'=>4,'draft_logo_id'=>8,'published_logo_id'=>7],'Using the approved 3D logo preserves both saved media references and advances the revision');
rejected(fn()=>B::transition($builtin,['revision'=>'3'],'builtin'),'A stale source-selection form cannot overwrite a newer change');
$withdrawn=B::transition($new,['revision'=>'3','logo_id'=>'999'],'withdraw');
check($withdrawn['draft_logo_id']===8&&$withdrawn['published_logo_id']===0,'Withdrawal preserves the saved draft and ignores an unsaved selection');
$clear=B::transition($withdrawn,['revision'=>'4','logo_id'=>'','approved'=>'1'],'publish');
check($clear['draft_logo_id']===0&&$clear['published_logo_id']===0,'Native empty selection explicitly restores the bundled artwork');
foreach([['logo_id'=>[]],['logo_id'=>'../file'],['logo_id'=>'-1'],['logo_id'=>'https://example.test/logo.png'],['revision'=>[]]] as $bad){rejected(fn()=>B::transition(B::EMPTY_STATE,$bad,'save'),'Malformed or remote selection cannot choose image bytes');}
foreach(['delete','enable','create_database','restore',''] as $action){rejected(fn()=>B::transition(B::EMPTY_STATE,[],$action),'Unknown action cannot mutate branding');}
check(str_contains(B::textMark('<script>Logo</script>'),'&lt;script&gt;')&&!str_contains(B::textMark('<script>Logo</script>'),'<script>'),'Text fallback escapes the configured website name');
check(B::image(0)===''&&!B::publicImage(0),'Empty media ID does not query or expose a file');
$bundled=B::bundledMark();
check(str_contains($bundled,'images/branding/spiritual-gorillaz-sculpted-gold.png"')&&!str_contains($bundled,'.webp')&&!str_contains($bundled,'?v='),'Bundled mark renders the immutable full-resolution PNG without a separate low-resolution animation request');
check(str_contains($bundled,'bundled-brand-animated')&&str_contains($bundled,'<span class="bundled-brand-art">')&&!str_contains($bundled,'<picture'),'Approved artwork exposes the CSS motion wrapper while remaining a usable native image');
check(str_contains($bundled,'width="2172" height="724"')&&!str_contains($bundled,'spiritual-gorillaz-logo.png'),'Bundled mark keeps the approved horizontal proportions and retires the old active logo path');
$footer=B::bundledMark(true);
check(str_contains($footer,'sculpted-gold.png"')&&str_contains($footer,'loading="lazy"')&&str_contains($footer,'bundled-brand-animated')&&str_contains($footer,'bundled-brand-art'),'Footer shares the same full-resolution artwork and motion treatment with lazy image loading');
check(!B::inPreview(),'Private-preview mode is not enabled by default');
rejected(fn()=>B::transition($draft,['revision'=>1,'logo_id'=>8,'restore_epoch'=>0],'save',12),'Old form cannot match a restored revision after the audit epoch changes');
rejected(fn()=>B::transition($builtin,['revision'=>4,'restore_epoch'=>0],'builtin',12),'Restoration epoch also guards switching back to the approved logo');
$fresh=B::transition($draft,['revision'=>1,'logo_id'=>8,'restore_epoch'=>12],'save',12);
check($fresh['draft_logo_id']===8&&$fresh['published_logo_id']===0,'A freshly loaded post-restore form can save a private draft');
rejected(fn()=>B::transition(B::EMPTY_STATE,['revision'=>0,'logo_id'=>8],'save',12),'Restoring a pre-branding snapshot cannot revalidate an original empty form');
rejected(fn()=>B::transition(B::EMPTY_STATE,['restore_epoch'=>[]],'save'),'Malformed restoration epoch is rejected');
echo $count." branding unit checks passed.\n";
