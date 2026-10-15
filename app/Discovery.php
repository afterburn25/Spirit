<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;

/** Public projections read approved snapshots, never title/draft columns. */
final class Discovery
{
    private static function source(): string
    {
        $c=Database::table('content');$p=Database::table('products');$categories=Database::table('shop_categories');
        $content="SELECT id,kind,published_slug AS slug,published_json AS document,published_at AS modified FROM $c WHERE deleted_at IS NULL AND published_json IS NOT NULL AND kind IN ('page','release','event','member') AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(published_json,'$.data.seo_visibility')),'include')<>'exclude' AND NOT (kind='page' AND published_slug='booking')";
        if(!Catalog::options()['enabled']){return $content;}
        return $content." UNION ALL SELECT p.id,'product' AS kind,p.slug,p.published_json AS document,NULL AS modified FROM $p p LEFT JOIN $categories c ON c.id=CAST(JSON_UNQUOTE(JSON_EXTRACT(p.published_json,'$.category_id')) AS UNSIGNED) WHERE p.published_json IS NOT NULL AND (COALESCE(JSON_EXTRACT(p.published_json,'$.category_id'),0)=0 OR c.active=1)";
    }
    public static function decode(array $row): array
    {
        $doc=json_decode($row['document'],true,64,JSON_THROW_ON_ERROR);
        if($row['kind']==='product'){$title=$doc['name'];$data=$doc;$url=app_url('shop/product.php?slug='.rawurlencode($row['slug']));}
        else{$title=$doc['title'];$data=$doc['data'];$url=PublicSite::url(['kind'=>$row['kind'],'slug'=>$row['slug']]);}
        return ['id'=>(int)$row['id'],'kind'=>$row['kind'],'title'=>$title,'url'=>$url,'summary'=>Metadata::text($data['description']??$data['body']??$data['bio']??$data['subtitle']??'',230),'modified'=>$row['modified']];
    }
    public static function search(array $input): array
    {
        $q=Input::text($input,'q',100);$page=Input::integer($input,'page',1,1000000,1);$kind=Input::choice($input,'kind',['all','page','release','event','member','product'],'all');
        $result=['items'=>[],'total'=>0,'page'=>1,'pages'=>1,'q'=>$q,'kind'=>$kind];if($q===''){return $result;}
        $source=self::source();$where="(JSON_UNQUOTE(JSON_EXTRACT(document,'$.title')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.name')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.data.body')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.data.description')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.data.bio')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.description')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.data.venue')) LIKE ? ESCAPE '!' OR JSON_UNQUOTE(JSON_EXTRACT(document,'$.data.city')) LIKE ? ESCAPE '!')";
        $args=array_fill(0,8,ContentHistory::pattern($q));if($kind!=='all'){$where.=' AND kind=?';$args[]=$kind;}
        $db=Database::connect();$stmt=$db->prepare('SELECT COUNT(*) FROM ('.$source.') approved WHERE '.$where);$stmt->execute($args);$total=(int)$stmt->fetchColumn();$pages=max(1,(int)ceil($total/20));$page=min($page,$pages);
        $stmt=$db->prepare('SELECT * FROM ('.$source.') approved WHERE '.$where.' ORDER BY kind,id DESC LIMIT 20 OFFSET '.(($page-1)*20));$stmt->execute($args);
        return ['items'=>array_map(self::decode(...),$stmt->fetchAll()),'total'=>$total,'page'=>$page,'pages'=>$pages,'q'=>$q,'kind'=>$kind];
    }
    /** Only formerly published slugs can redirect. Current exact matches win. */
    public static function former(string $kind,string $slug): ?array
    {
        if(!in_array($kind,['page','release','event','member'],true)||!preg_match('/^[a-z0-9][a-z0-9-]{0,89}$/D',$slug)){return null;}
        $q=Database::connect()->prepare('SELECT DISTINCT c.id FROM '.Database::table('content').' c JOIN '.Database::table('content_revisions')." r ON r.content_id=c.id WHERE c.kind=? AND c.deleted_at IS NULL AND c.published_json IS NOT NULL AND r.action='publish' AND JSON_UNQUOTE(JSON_EXTRACT(r.draft_json,'$.slug'))=? AND c.published_slug<>? LIMIT 2");$q->execute([$kind,$slug,$slug]);$ids=$q->fetchAll(\PDO::FETCH_COLUMN);
        return count($ids)===1?Content::get((int)$ids[0],true):null;
    }
    public static function sitemapRows(int $page): array
    {
        $source=self::source();$filter="NOT (kind='page' AND slug IN ('home','music','shows','band','media','booking','press','merch'))";$db=Database::connect();
        $total=(int)$db->query('SELECT COUNT(*) FROM ('.$source.') approved WHERE '.$filter)->fetchColumn();$pages=max(1,(int)ceil($total/1000));
        if($page<1||$page>$pages){throw new InvalidArgumentException('Sitemap page not found.');}
        $rows=$db->query('SELECT * FROM ('.$source.') approved WHERE '.$filter.' ORDER BY kind,id LIMIT 1000 OFFSET '.(($page-1)*1000))->fetchAll();
        return ['items'=>array_map(self::decode(...),$rows),'pages'=>$pages];
    }
    private static function xml(string $value): string {return htmlspecialchars($value,ENT_XML1|ENT_QUOTES,'UTF-8');}
    public static function sitemap(): never
    {
        if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)){header('Allow: GET, HEAD');http_response_code(405);exit;}
        if(!Paths::installed()||!Migrations::ready()){http_response_code(503);header('Retry-After: 300');header('Cache-Control: no-store');exit('Sitemap is unavailable until setup or update is complete.');}
        try{$part=Input::choice($_GET,'part',['index','static','content'],'index');$page=Input::integer($_GET,'page',1,1000000,1);}catch(InvalidArgumentException $e){http_response_code(400);exit('Invalid sitemap selection.');}
        $prefix='<?xml version="1.0" encoding="UTF-8"?>';$ns=' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        if(!Metadata::options()['indexing']){$xml=$prefix.'<urlset'.$ns.'></urlset>';}
        elseif($part==='index'){
            $listing=self::sitemapRows(1);if($listing['pages']>49999){http_response_code(503);exit('Sitemap index capacity exceeded.');}
            $xml=$prefix.'<sitemapindex'.$ns.'><sitemap><loc>'.self::xml(Metadata::absolute(app_url('?sitemap=1&part=static'))).'</loc></sitemap>';
            for($i=1;$i<=$listing['pages'];$i++){$xml.='<sitemap><loc>'.self::xml(Metadata::absolute(app_url('?sitemap=1&part=content&page='.$i))).'</loc></sitemap>';}$xml.='</sitemapindex>';
        }else{
            $items=[];
            if($part==='static'){
                foreach([''=>'home','music/'=>'music','shows/'=>'shows','band/'=>'band','media/'=>'media','press/'=>'press'] as $path=>$slug){$item=$slug?Content::bySlug('page',$slug):null;if(($item['data']['seo_visibility']??'include')!=='exclude'){$items[]=['url'=>app_url($path),'modified'=>null];}}
                if(Catalog::options()['enabled']&&((Content::bySlug('page','merch')['data']['seo_visibility']??'include')!=='exclude')){$items[]=['url'=>app_url('shop/'),'modified'=>null];}
            }else{try{$items=self::sitemapRows($page)['items'];}catch(InvalidArgumentException $e){http_response_code(404);exit('Sitemap page unavailable.');}}
            $xml=$prefix.'<urlset'.$ns.'>';foreach($items as $item){$xml.='<url><loc>'.self::xml(Metadata::absolute($item['url'])).'</loc>';if($item['modified']){$xml.='<lastmod>'.self::xml(str_replace(' ','T',$item['modified']).'Z').'</lastmod>';}$xml.='</url>';}$xml.='</urlset>';
        }
        $etag='"'.hash('sha256',$xml).'"';header('Content-Type: application/xml; charset=utf-8');header('Cache-Control: public, max-age=0, must-revalidate');header('ETag: '.$etag);
        if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);exit;}header('Content-Length: '.strlen($xml));if(($_SERVER['REQUEST_METHOD']??'GET')!=='HEAD'){echo $xml;}exit;
    }
}
