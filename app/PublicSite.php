<?php
declare(strict_types=1);
namespace Spirit;
use DateTimeImmutable;
use DateTimeZone;

final class PublicSite
{
    public static function url(array $item): string
    {
        $slug=rawurlencode($item['slug']);
        $path=match($item['kind']) {'release'=>'music/release.php?slug='.$slug,'event'=>'shows/event.php?slug='.$slug,'member'=>'band/member.php?slug='.$slug,'page'=>(SectionPages::definition($item['slug'])[1]??'pages/?slug='.$slug),default=>'media/'};
        return app_url($path);
    }
    public static function imageSizes(string $class): string
    {
        $classes=preg_split('/\s+/',trim($class))?:[];
        if(in_array('product-thumb-image',$classes,true)){return '72px';}
        if(in_array('product-main-image',$classes,true)){return '(max-width: 600px) calc(100vw - 42px), (max-width: 760px) calc(100vw - 82px), (max-width: 1000px) 680px, (max-width: 1440px) 52vw, 680px';}
        if(in_array('section-feature',$classes,true)){return '(max-width: 600px) calc(100vw - 42px), (max-width: 820px) calc(100vw - 80px), 640px';}
        if(in_array('hero-art',$classes,true)){return '(max-width: 820px) 100vw, 65vw';}
        if(in_array('page-image',$classes,true)||in_array('event-image',$classes,true)){return '(max-width: 600px) calc(100vw - 40px), (max-width: 900px) calc(100vw - 80px), 820px';}
        if(in_array('band-image',$classes,true)){return '(max-width: 600px) calc(100vw - 40px), 640px';}
        return '(max-width: 600px) calc(100vw - 40px), (max-width: 1100px) 50vw, 640px';
    }
    public static function image(int $id,string $alt,string $class='',bool $eager=false,?string $altOverride=null): string
    {
        if($id<1){return '<span class="art-placeholder '.e($class).'" aria-hidden="true"><span>♛</span><b>SG</b></span>';}
        $media=Media::get($id);if(!$media||$media['kind']!=='image'){return '<span class="art-placeholder '.e($class).'" aria-hidden="true">SG</span>';}
        $dimensions=Media::dimensions($media);$size=isset($dimensions['width'],$dimensions['height'])?' width="'.$dimensions['width'].'" height="'.$dimensions['height'].'"':'';
        $source=Media::url($id);$responsive='';
        if(isset($dimensions['width'])&&$dimensions['width']>ImageVariants::WIDTHS[0]){
            $candidates=[];
            foreach(ImageVariants::WIDTHS as $width){if($width<$dimensions['width']){$candidates[]=Media::url($id).'&width='.$width.' '.$width.'w';}}
            $candidates[]=Media::url($id).' '.$dimensions['width'].'w';
            $sizes=self::imageSizes($class);
            $responsive=' srcset="'.e(implode(', ',$candidates)).'" sizes="'.e($sizes).'"';
            $source=$dimensions['width']>640?Media::url($id).'&width=640':Media::url($id);
        }
        $resolvedAlt=$altOverride!==null && trim($altOverride)!=='' ? $altOverride : ($media['alt_text']?:$alt);
        return '<img'.$size.' src="'.e($source).'"'.$responsive.' alt="'.e($resolvedAlt).'" class="'.e($class).'" loading="'.($eager?'eager':'lazy').'"'.($eager?' fetchpriority="high"':'').' decoding="async">';
    }
    public static function text(string $text): string {return nl2br(e($text));}
    public static function releaseCard(array $release): void
    {
        echo '<article class="release-card"><a href="'.e(self::url($release)).'" class="release-art" aria-label="Explore release: '.e($release['title']).'">'.self::image((int)$release['data']['cover_id'],$release['title']).'<span class="art-action" aria-hidden="true">▶</span></a><div class="release-card-body"><p class="eyebrow">'.e(strtoupper($release['data']['release_type'])).($release['data']['release_date']?' / '.e(substr($release['data']['release_date'],0,4)):'').'</p><h3><a href="'.e(self::url($release)).'">'.e($release['title']).'</a></h3><a class="text-link" href="'.e(self::url($release)).'">Explore release <span aria-hidden="true">→</span></a></div></article>';
    }
    public static function ticket(array $event): void
    {
        $d=$event['data'];$state=$d['event_state'];
        if($state==='scheduled' && $d['ticket_url']!=='') {echo '<a class="button ticket-link" target="_blank" rel="noopener noreferrer" href="'.e($d['ticket_url']).'" aria-label="Get tickets for '.e($event['title']).' at the venue ticket seller (opens a new tab)">Get tickets <span aria-hidden="true">↗</span></a>';return;}
        $label=match($state){'free'=>'Free admission','sold_out'=>'Sold out','postponed'=>'Postponed','cancelled'=>'Cancelled',default=>'Ticket details soon'};echo '<span class="event-status status-'.e($state).'">'.e($label).'</span>';
    }
    public static function eventRow(array $event): void
    {
        $d=$event['data'];$date=new DateTimeImmutable($d['starts_at'],new DateTimeZone($d['timezone']));
        echo '<article class="event-row"><time class="date-tile" datetime="'.e($date->format(DATE_ATOM)).'"><span>'.e(strtoupper($date->format('M'))).'</span><strong>'.e($date->format('d')).'</strong><small>'.e($date->format('Y')).'</small></time><div class="event-place"><h3><a href="'.e(self::url($event)).'">'.e($event['title']).'</a></h3><p>'.e($d['venue']).' · '.e($d['city']).'</p></div><div class="event-time"><span>'.e($date->format('D, M j, Y')).'</span><small>'.e($date->format('g:i A T')).'</small></div><div class="event-action">';self::ticket($event);echo '</div></article>';
    }
    public static function tracks(array $release): void
    {
        $tracks=Content::tracks($release['id']);
        if(!$tracks){echo '<div class="empty-state compact"><p>On-site tracks have not been published for this release. Verified streaming links appear alongside the release when available.</p></div>';return;}
        echo '<ol class="track-list enhanced-tracks">';foreach($tracks as $index=>$track){$d=$track['data'];echo '<li><button type="button" class="play-track" data-audio="'.e(Media::url((int)$d['audio_id'])).'" data-track-id="'.$track['id'].'" data-title="'.e($track['title']).'" data-release="'.e($release['title']).'" aria-label="Play '.e($track['title']).'"><span class="play-circle" aria-hidden="true">▶</span><span class="track-number">'.($index+1).'.</span><span class="track-title">'.e($track['title']).'</span><span class="track-duration">'.e($d['duration']).'</span></button></li>';}echo '</ol><p class="small muted enhanced-tracks">Select a track to listen. Playback continues while you browse supported public pages.</p>';PublicAccess::nativeTracks($tracks);
    }
    public static function render(string $view,?array $preview=null,array $context=[]): void
    {
        if(!Paths::installed()){go('install/');}
        if(!Migrations::ready()) {http_response_code(503);View::start('Website update in progress');echo '<section class="panel"><h1>Website update in progress.</h1><p>The owner can finish the additive database update in administration. Existing website data has been preserved.</p><a class="button" href="'.e(app_url('admin/updates.php')).'">Owner administration</a></section>';View::end();return;}
        $settings=Settings::all();$record=$preview;$pageContent=null;$isPreview=$preview!==null||Branding::inPreview();
        $slug=is_string($_GET['slug']??null)?$_GET['slug']:'';
        $details=['release'=>'release','event'=>'event','member'=>'member','page'=>'page'];
        if(isset($details[$view])&&!$record){$record=Content::bySlug($details[$view],$slug);if(!$record){$previous=Discovery::former($details[$view],$slug);if($previous&&in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)){header('Cache-Control: no-cache');header('Location: '.self::url($previous),true,301);return;}http_response_code(404);$view='not-found';}}
        // A saved section Page previews in its real layout, with only that
        // Page draft substituted. Related releases/products/events stay public-only.
        if($view==='page'&&$record&&$record['kind']==='page'&&($section=SectionPages::definition($record['slug']))){
            if($section[2]==='catalog'){Storefront::render('catalog',$isPreview?['page_preview'=>$record]:[]);return;}
            $view=$section[2];$pageContent=$record;$record=null;
        }
        if(($sectionSlug=SectionPages::slugForView($view))!==null){$pageContent??=$preview??Content::bySlug('page',$sectionSlug);}
        $titles=['home'=>'Music, People, Purpose','music'=>'Music | Recordings & Performances','shows'=>'Shows | Dates & Venue Information','band'=>'Meet Spiritual Gorillaz | The Band','media'=>'Media | Performances & Photographs','booking'=>'Book Spiritual Gorillaz | Event Inquiries','booking-thanks'=>'Inquiry received','newsletter'=>'Band updates','press'=>'Press Kit | Band & Booking Information','shop'=>'Merchandise | Official Collection','search'=>'Search the website','not-found'=>'Page not found'];
        $descriptions=['home'=>'Discover Spiritual Gorillaz through music, performances, band stories and event inquiries.','music'=>'Explore Spiritual Gorillaz recordings, performances and approved listening links.','shows'=>"Find confirmed Spiritual Gorillaz performances, venue information and links to the venue's ticket seller.",'band'=>'Meet Chad Lindsey, Ron Green and Charlie Savell, the musicians behind Spiritual Gorillaz.','media'=>"Watch selected Spiritual Gorillaz performances and explore the band's approved photographs.",'booking'=>'Send a Spiritual Gorillaz booking inquiry with your preferred date, venue and event details. An inquiry is not a confirmed booking.','press'=>'Band information, confirmed lineup, approved media and booking resources for Spiritual Gorillaz.','shop'=>'Explore approved Spiritual Gorillaz merchandise, product photographs, available options and care information.'];
        // Historic names belong in the default Band description only while the legacy lineup remains visible.
        if($view==='band'&&!MemberOnboarding::showLegacyFallback()){$descriptions['band']='Meet the musicians behind Spiritual Gorillaz and discover their stories and contributions.';}
        $title=$record?$record['title']:($titles[$view]??'Spiritual Gorillaz');$active=match($view){'release'=>'music','event'=>'shows','member','page'=>'band','booking-thanks'=>'booking',default=>$view};
        $description=$record?Metadata::text((string)($record['data']['description']??$record['data']['body']??$record['data']['bio']??$record['title']),180):Metadata::text(SectionPages::text($pageContent,'body',$descriptions[$view]??$settings['welcome']),180);
        $private=$isPreview||in_array($view,['booking','booking-thanks','newsletter','search'],true);
        header('Cache-Control: '.($private?'private, no-store':'no-cache'));if($isPreview){header('X-Robots-Tag: noindex, nofollow');}
        $canonical=null;if(!$private&&$view!=='not-found'){$path=$record?self::url($record):app_url($view==='home'?'':($view==='music'?'music/':$view.'/'));$base=Paths::basePath();$canonical=rtrim(Paths::config()['site_url'],'/').substr($path,strlen($base));}
        $metaRecord=$record??$pageContent;
        $analyticsPage = !$isPreview && in_array($view, ['home','music','shows','band','media','press','booking','release','event','member','page'], true)
            ? Analytics::beginPage() : null;
        require Paths::root().'/app/views/public/header.php';
        $templates=['booking'=>'booking.php','booking-thanks'=>'booking-thanks.php','newsletter'=>'newsletter.php','search'=>'discovery.php'];
        require Paths::root().'/app/views/public/'.($templates[$view]??'sections.php');
        require Paths::root().'/app/views/public/footer.php';
    }
}
