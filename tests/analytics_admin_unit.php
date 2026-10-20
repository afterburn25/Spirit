<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/bootstrap.php';
ob_start();
use Spirit\{AnalyticsAdmin,AnalyticsCharts,AnalyticsSearchConsole};
$checks = 0;
function analyticsCheck(bool $condition, string $label): void { global $checks; if (!$condition) { throw new RuntimeException($label); } $checks++; echo 'PASS '.$label."\n"; }
function rejected(callable $call): bool { try { $call(); return false; } catch (InvalidArgumentException|RuntimeException) { return true; } }
$range = AnalyticsAdmin::range(['range'=>'7']);
analyticsCheck($range[1] === gmdate('Y-m-d') && (new DateTimeImmutable($range[0]))->diff(new DateTimeImmutable($range[1]))->days===6,'Seven-day report includes both endpoints');
foreach ([['start'=>'2026-02-30','end'=>'2026-03-01'],['start'=>'2026-10-01','end'=>'2026-09-01'],['start'=>'2023-01-01','end'=>'2026-01-01'],['start'=>['bad'],'end'=>'2026-01-01'],['start'=>'2999-01-01','end'=>'2999-01-02']] as $input) { analyticsCheck(rejected(fn()=>AnalyticsAdmin::range($input)),'Invalid, reversed, future or unbounded dates are rejected'); }
analyticsCheck(AnalyticsAdmin::duration(null)==='—' && AnalyticsAdmin::duration(0)==='0s' && AnalyticsAdmin::duration(65)==='1m 5s' && AnalyticsAdmin::duration(3661)==='1h 1m','Unavailable time differs from measured zero and durations have clear units');
ob_start(); AnalyticsAdmin::bars([['label'=>'<script>alert(1)</script>','visits'=>10]],'label','visits','',100); $html=ob_get_clean();
analyticsCheck(!str_contains($html,'<script>') && str_contains($html,'&lt;script&gt;') && str_contains($html,'10%'),'Chart labels are escaped and percentages use the full supplied cohort');
ob_start(); AnalyticsCharts::donut([['label'=>'No observations','visits'=>0]]); $html=ob_get_clean();
analyticsCheck(str_contains($html,'No data yet') && !str_contains($html,'class="chart-segment'),'An empty donut has an honest zero state with no fabricated segment');
ob_start(); AnalyticsCharts::donut([['label'=>'<script>unsafe</script>','visits'=>10],['label'=>'Referral','visits'=>20]],'label','visits','Channel mix',100); $html=ob_get_clean();
analyticsCheck(!str_contains($html,'<script>') && str_contains($html,'&lt;script&gt;') && str_contains($html,'Other / unlisted: 70 (70%)'),'Donut labels are escaped and unlisted cohort members remain visible');
ob_start(); AnalyticsCharts::trend([['date'=>'2026-10-01','visits'=>0,'pageviews'=>0]]); AnalyticsCharts::trend([['date'=>'2026-10-01','visits'=>3,'pageviews'=>8]],true); $html=ob_get_clean();
preg_match_all('/\bid="([^"]+)"/', $html, $matches);
analyticsCheck(count($matches[1])===6 && count(array_unique($matches[1]))===6,'Overview and full traffic charts have unique accessible IDs and gradient references');
analyticsCheck(str_contains($html,'2026-10-01: 0 visits · 0 pageviews') && str_contains($html,'2026-10-01: 3 visits · 8 pageviews') && !preg_match('/(?:NaN|INF)/',$html),'Single-day and empty-traffic charts retain exact zeros and finite geometry');
ob_start(); AnalyticsCharts::trend([]); AnalyticsCharts::hourly([]); AnalyticsCharts::heatmap([]); $html=ob_get_clean();
analyticsCheck(substr_count($html,'<svg ')===3 && !preg_match('/(?:NaN|INF)/',$html),'Empty optional report arrays render safe chart shells without invented observations');
$hours=[]; $heat=[];
for ($hour=0;$hour<24;$hour++) { $hours[]=['hour'=>$hour,'pageviews'=>$hour===12?9:0,'visits'=>$hour===12?3:0]; }
for ($day=0;$day<7;$day++) { for ($hour=0;$hour<24;$hour++) { $heat[]=['weekday'=>$day,'day'=>['Mon','Tue','Wed','Thu','Fri','Sat','Sun'][$day],'hour'=>$hour,'pageviews'=>$day===2&&$hour===12?9:0]; } }
ob_start(); AnalyticsCharts::hourly($hours); $html=ob_get_clean();
analyticsCheck(substr_count($html,'class="chart-hour-bar"')===24 && str_contains($html,'12:00 UTC: 9 pageviews · 3 visits') && str_contains($html,'height="0.00"'),'Hourly bars preserve all 24 hours, including measured zero counts');
ob_start(); AnalyticsCharts::heatmap($heat); $html=ob_get_clean();
analyticsCheck(substr_count($html,'class="chart-heat ')===168 && substr_count($html,'class="chart-heat heat-0"')===167 && str_contains($html,'Wed 12:00 UTC: 9 pageviews'),'Heatmap includes 168 honest cells with exact keyboard-accessible values');
analyticsCheck(str_contains($html,'View weekday / hour counts') && substr_count($html,'scope="row"')===7 && substr_count($html,'<td>9</td>')===1,'Heatmap includes the same exact counts in a native accessible data table');
ob_start(); AnalyticsAdmin::liveRows([['id'=>'abc123','path'=>'/','country'=>'United States','region'=>'Unknown','city'=>'Unknown','device'=>'Desktop','browser'=>'Chrome','active_seconds'=>30,'last_seen'=>'2026-10-19 12:00:00']]); $html=ob_get_clean();
analyticsCheck(str_contains($html,'United States') && !str_contains($html,'Unknown, Unknown'),'Live location omits duplicate unknown components');
ob_start(); AnalyticsAdmin::liveRows([['record_id'=>42,'detail_url'=>'javascript:alert(1)','id'=>'<b>abc123</b>','path'=>'/music/','referrer'=>'google.com','country'=>'United States','region'=>'Unknown','city'=>'Unknown','device'=>'Desktop','browser'=>'Chrome','os'=>'Windows','page_count'=>2,'active_seconds'=>30,'last_seen'=>'2026-10-19 12:00:00']]); $html=ob_get_clean();
analyticsCheck(str_contains($html,'visitors.php?visit=42') && !str_contains($html,'javascript:') && !str_contains($html,'<b>') && substr_count($html,'<td')===7 && str_contains($html,'Chrome · Windows') && str_contains($html,'google.com'),'Live drilldowns use numeric internal IDs, escape labels, and retain source, software and page count');
ob_start(); AnalyticsAdmin::export(['available'=>true,'start'=>'2026-01-01','end'=>'2026-01-02','referrers'=>[['domain'=>'=HYPERLINK("https://example.invalid")','visits'=>1]]]); $csv=ob_get_clean();
analyticsCheck(str_contains($csv,"'=HYPERLINK") && str_contains($csv,'IP Geolocation by DB-IP'),'CSV export neutralizes formula-leading text and retains location attribution');
$temporary = tempnam(sys_get_temp_dir(),'spirit-query-csv-');
if (!$temporary) { throw new RuntimeException('Temporary test file unavailable.'); }
try {
 $header="Top queries,Clicks,Impressions,CTR,Position\n";
 file_put_contents($temporary,"\xEF\xBB\xBF".$header."spiritual gorillaz,12,120,10%,2.3\n\"band, live\",0,25,0%,10\n");
 $rows=AnalyticsSearchConsole::parse($temporary);
 analyticsCheck(count($rows)===2 && $rows[0]['clicks']===12 && $rows[1]['query']==='band, live','Official English query CSV supports BOM and quoted commas');
 file_put_contents($temporary,$header."grouped,\"1,234\",\"12,340\",10%,2\n");
 analyticsCheck(AnalyticsSearchConsole::parse($temporary)[0]['clicks']===1234,'Valid grouped thousands in official exports remain usable');
 foreach (['1,2','12,34','1,,234',',123','123,'] as $number) {
  file_put_contents($temporary,$header.'bad,"'.$number.'",999999,10%,2'."\n");
  analyticsCheck(rejected(fn()=>AnalyticsSearchConsole::parse($temporary)),'Malformed grouping cannot silently change an imported count');
 }
 foreach (["Path,Clicks,Impressions,CTR,Position\n/,1,1,100%,1\n",$header."x,2,1,200%,1\n",$header."x,1,10,10%,0\n",$header."x,-1,10,0%,1\n",$header."x,1,10,10%,1\nx,2,20,10%,1\n",$header."x,1,10,10%,1,extra\n",$header."x,1,10,10%,NaN\n",$header] as $invalid) {
  file_put_contents($temporary,$invalid); analyticsCheck(rejected(fn()=>AnalyticsSearchConsole::parse($temporary)),'Malformed or inconsistent query snapshot is rejected before replacement');
 }
 file_put_contents($temporary,$header.str_repeat("a",AnalyticsSearchConsole::MAX_BYTES));
 analyticsCheck(rejected(fn()=>AnalyticsSearchConsole::parse($temporary)),'Oversized CSV cannot be parsed into memory');
} finally { unlink($temporary); }
echo $checks." analytics admin and import checks passed.\n";
ob_end_flush();
