<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;

/** Published metadata only. No HTML templates or caller-selected external URLs. */
final class Metadata
{
    public static function options(): array
    {
        $site=ConfigStore::read('site');$settings=$site['discovery']??[];
        return ['indexing'=>($settings['indexing']??true)===true];
    }
    public static function save(array $input,int $actor): void
    {
        $user=Auth::current();
        if(!Permissions::allows($user,'owner')||(int)$user['id']!==$actor){throw new InvalidArgumentException('Owner access is required.');}
        $value=Input::choice($input,'indexing',['enabled','disabled'],'enabled');
        $site=ConfigStore::read('site');$site['discovery']=['indexing'=>$value==='enabled'];ConfigStore::write('site',$site,$actor);
    }
    public static function text(string $value,int $limit=180): string
    {
        if($limit<1||$limit>1200){throw new InvalidArgumentException('Invalid metadata length.');}
        if(!preg_match('//u',$value)){return '';}
        $value=trim(preg_replace('/\s+/u',' ',strip_tags($value))??'');
        preg_match('/^.{0,'.$limit.'}/us',$value,$match);return $match[0]??'';
    }
    public static function absolute(string $path): string
    {
        $base=Paths::basePath();
        if(!str_starts_with($path,$base.'/')||str_starts_with($path,'//')||preg_match('/[\\\\\x00-\x20\x7f]/',$path)){throw new InvalidArgumentException('Invalid canonical application path.');}
        return rtrim(Paths::config()['site_url'],'/').substr($path,strlen($base));
    }
    public static function json(array $value): string
    {
        return json_encode($value,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    }
    public static function event(array $item,string $canonical): ?array
    {
        $d=$item['data'];
        if(($item['kind']??'')!=='event'||($d['event_access']??'unconfirmed')!=='public'||empty($d['address'])||empty($d['city'])||empty($d['country'])||empty($d['starts_at'])){return null;}
        $date=new \DateTimeImmutable($d['starts_at'],new \DateTimeZone($d['timezone']));
        $address=['@type'=>'PostalAddress','streetAddress'=>$d['address'],'addressLocality'=>$d['city'],'addressCountry'=>$d['country']];
        foreach(['region'=>'addressRegion','postal_code'=>'postalCode'] as $key=>$property){if(!empty($d[$key])){$address[$property]=$d[$key];}}
        return ['@context'=>'https://schema.org','@type'=>'MusicEvent','name'=>$item['title'],'url'=>$canonical,'startDate'=>$date->format(DATE_ATOM),'eventAttendanceMode'=>'https://schema.org/OfflineEventAttendanceMode','eventStatus'=>'https://schema.org/'.match($d['event_state']){'cancelled'=>'EventCancelled','postponed'=>'EventPostponed',default=>'EventScheduled'},'location'=>['@type'=>'Place','name'=>$d['venue'],'address'=>$address],'description'=>self::text($d['description']??$item['title'],1000)];
    }
    public static function build(string $title,string $description,?string $canonical,?array $record,bool $private): array
    {
        $data=$record['data']??[];$noindex=$private||$canonical===null||!self::options()['indexing']||($data['seo_visibility']??'include')==='exclude'||http_response_code()>=400;
        $out=['title'=>self::text($data['seo_title']??'')?:$title,'description'=>self::text($data['seo_description']??'')?:self::text($description),'canonical'=>$canonical,'robots'=>$noindex?'noindex, nofollow':'index, follow','image'=>null,'schema'=>null];
        if(!$private&&$canonical!==null&&$record){
            $id=(int)($data['share_image_id']??0);$id=$id?:((int)($data['cover_id']??$data['image_id']??$data['image_ids'][0]??0));
            if($id>0&&(Content::publicMedia($id)||Catalog::publicImage($id))){$media=Media::get($id);if($media&&$media['kind']==='image'){$out['image']=['url'=>self::absolute(Media::url($id)),'alt'=>$media['alt_text']?:$title,'dimensions'=>Media::dimensions($media)];}}
            if(!$noindex){$out['schema']=self::event($record,$canonical);if($out['schema']&&$out['image']){$out['schema']['image']=[$out['image']['url']];}}
        }
        if($noindex){header('X-Robots-Tag: noindex, nofollow');}return $out;
    }
    public static function tags(array $meta,string $siteName): void
    {
        $title=$meta['title'].' · '.$siteName;
        echo '<title>'.e($title).'</title><meta name="description" content="'.e($meta['description']).'"><meta name="robots" content="'.e($meta['robots']).'">';
        foreach(['og:title'=>$title,'og:description'=>$meta['description'],'og:type'=>'website','og:site_name'=>$siteName] as $name=>$value){echo '<meta property="'.e($name).'" content="'.e($value).'">';}
        if($meta['canonical']){echo '<link rel="canonical" href="'.e($meta['canonical']).'"><meta property="og:url" content="'.e($meta['canonical']).'">';}
        echo '<meta name="twitter:card" content="'.($meta['image']?'summary_large_image':'summary').'"><meta name="twitter:title" content="'.e($title).'"><meta name="twitter:description" content="'.e($meta['description']).'">';
        if($meta['image']){$image=$meta['image'];echo '<meta property="og:image" content="'.e($image['url']).'"><meta property="og:image:alt" content="'.e($image['alt']).'"><meta name="twitter:image" content="'.e($image['url']).'">';foreach(['width','height'] as $key){if(isset($image['dimensions'][$key])){echo '<meta property="og:image:'.$key.'" content="'.(int)$image['dimensions'][$key].'">';}}}
        if($meta['schema']){echo '<script id="page-structured-data" type="application/ld+json">'.self::json($meta['schema']).'</script>';}
    }
}
