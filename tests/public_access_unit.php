<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
use Spirit\{PublicAccess,PublicSite,Storefront};
$count=0;
set_error_handler(static function(int $level,string $message):never{throw new RuntimeException($message);});
function verify(bool $ok,string $message):void{global $count;if(!$ok){throw new RuntimeException($message);}echo 'PASS '.$message."\n";$count++;}
function rendered(callable $draw):string{ob_start();try{$draw();return (string)ob_get_contents();}finally{ob_end_clean();}}
verify(PublicAccess::POLICIES===['privacy'=>'Privacy','terms'=>'Terms','shipping'=>'Shipping','returns'=>'Returns'],'Policy navigation is limited to four owner-published page names');
verify(rendered(fn()=>PublicAccess::nativeTracks([]))==='','No native audio is invented for an empty release');
$title='Track <script>alert(1)</script> & "live"';
$tracks=[['id'=>7,'title'=>$title,'data'=>['audio_id'=>9]],['id'=>8,'title'=>'Second recording','data'=>['audio_id'=>10]]];
$html=rendered(fn()=>PublicAccess::nativeTracks($tracks));
verify(str_starts_with($html,'<noscript>')&&str_ends_with($html,'</noscript>'),'Native audio is rendered only as a no-JavaScript alternative');
verify(substr_count($html,'<audio controls preload="none"')===2,'Each authorized track receives its own non-preloading native controls');
verify(!str_contains($html,'autoplay'),'Native recordings never autoplay');
verify(str_contains($html,'src="/media/file.php?id=9"')&&str_contains($html,'src="/media/file.php?id=10"'),'Native controls use the same permission-checked media endpoint');
verify(str_contains($html,'href="/media/file.php?id=9"'),'A recording link remains available beyond native-player support');
verify(str_contains($html,'&lt;script&gt;')&&!str_contains($html,'<script>'),'Track text cannot inject executable markup');
verify(str_contains($html,'&quot;live&quot;'),'Audio accessible names escape attribute delimiters');
verify(str_contains($html,'playback stops when you leave'),'Native fallback does not falsely promise persistent playback');
$release=['kind'=>'release','title'=>'Release <img onerror="bad">','slug'=>'fixture','data'=>['cover_id'=>0,'release_type'=>'album','release_date'=>'']];
$html=rendered(fn()=>PublicSite::releaseCard($release));
verify(str_contains($html,'aria-label="Explore release: Release &lt;img'),'A release cover link has a name even with no artwork');
verify(!str_contains($html,'<img onerror'),'Release link names cannot inject markup');
$product=['slug'=>'fixture','data'=>['name'=>'Product <b>"test"</b>','image_ids'=>[]],'variants'=>[]];
$html=rendered(fn()=>Storefront::card($product));
verify(str_contains($html,'aria-label="View product: Product &lt;b&gt;&quot;test&quot;'),'An image-free product link retains an escaped accessible name');
verify(str_contains($html,'Currently unavailable'),'An image-free product does not invent stock or a price');
verify(!str_contains($html,'<b>"test"'),'Product names remain escaped');
verify(version_compare('1.0.0-rc.1','0.9.0-dev.12','>')&&version_compare('1.0.0','1.0.0-rc.1','>'),'Candidate and final versions retain forward-only upgrade ordering');
echo $count." public access unit checks passed.\n";
