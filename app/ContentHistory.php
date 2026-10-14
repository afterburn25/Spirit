<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;

/** Private revision selection and field comparison; never returns executable markup. */
final class ContentHistory
{
    public static function query(array $input): array
    {
        $kind=Input::choice($input,'kind',array_keys(ContentSchema::kinds()),'page');
        return ['kind'=>$kind,'q'=>Input::text($input,'q',100),'state'=>Input::choice($input,'state',['all','private','published','changed'],'all'),'sort'=>Input::choice($input,'sort',['position','updated','title'],'position'),'page'=>Input::integer($input,'page',1,1000000,1)];
    }
    public static function pattern(string $value): string
    {
        return '%'.strtr($value,['!'=>'!!','%'=>'!%','_'=>'!_']).'%';
    }
    public static function document(string $json): array
    {
        try{$value=json_decode($json,true,64,JSON_THROW_ON_ERROR);}catch(\JsonException $e){throw new InvalidArgumentException('This saved revision is unreadable. No content was changed.');}
        if(!is_array($value)||!is_string($value['title']??null)||!is_string($value['slug']??null)||!is_array($value['data']??null)){throw new InvalidArgumentException('This saved revision has an invalid format. No content was changed.');}
        return $value;
    }
    public static function difference(string $kind,array $saved,array $current): array
    {
        $labels=['title'=>'Title','slug'=>'URL name','sort_order'=>'Sort order'];
        $a=['title'=>$saved['title'],'slug'=>$saved['slug'],'sort_order'=>$saved['sort_order']??null];
        $b=['title'=>$current['title'],'slug'=>$current['slug'],'sort_order'=>$current['sort_order']??null];
        foreach(ContentSchema::fields($kind) as $key=>[$label]){$labels[$key]=$label;$a[$key]=$saved['data'][$key]??'';$b[$key]=$current['data'][$key]??'';}
        $out=[];
        foreach($labels as $key=>$label){$before=$a[$key];$after=$b[$key];if(!is_scalar($before)&&$before!==null||!is_scalar($after)&&$after!==null){throw new InvalidArgumentException('Invalid value in revision comparison.');}$out[]=['label'=>$label,'saved'=>$before===null?'Not recorded in this older revision':(string)$before,'current'=>$after===null?'Not recorded':(string)$after,'changed'=>$before!==$after];}
        return $out;
    }
    public static function listing(int $id,int $page=1): array
    {
        $db=Database::connect();$q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('content_revisions').' WHERE content_id=?');$q->execute([$id]);$total=(int)$q->fetchColumn();$pages=max(1,(int)ceil($total/25));$page=max(1,min($page,$pages));
        $q=$db->prepare('SELECT r.revision,r.action,r.actor_id,r.created_at,u.name AS actor_name FROM '.Database::table('content_revisions').' r LEFT JOIN '.Database::table('users').' u ON u.id=r.actor_id WHERE r.content_id=? ORDER BY r.revision DESC,r.id DESC LIMIT 25 OFFSET '.(($page-1)*25));$q->execute([$id]);
        return ['items'=>$q->fetchAll(),'page'=>$page,'pages'=>$pages,'total'=>$total];
    }
    public static function get(int $id,int $revision): ?array
    {
        $q=Database::connect()->prepare('SELECT revision,draft_json,action,created_at FROM '.Database::table('content_revisions').' WHERE content_id=? AND revision=? ORDER BY id DESC LIMIT 1');$q->execute([$id,$revision]);$row=$q->fetch();
        if(!$row){return null;}$row['document']=self::document($row['draft_json']);unset($row['draft_json']);return $row;
    }
}
