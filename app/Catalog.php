<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class Catalog
{
    public static function options(): array
    {
        return array_merge(['enabled'=>false, 'intro'=>'Wear the movement. Explore official merchandise.', 'accounts_enabled'=>false], ConfigStore::read('store'));
    }
    public static function categories(bool $public = false): array
    {
        return Database::connect()->query('SELECT * FROM '.Database::table('shop_categories').($public?' WHERE active=1':'').' ORDER BY name,id')->fetchAll();
    }
    public static function saveCategory(int $id, array $input, int $actor): int
    {
        $name=Input::text($input,'name',120,true);$slug=ContentSchema::slug(Input::text($input,'slug',90),$name);
        $description=Input::text($input,'description',2000);$active=($input['active']??'')==='1';
        $db=Database::connect();$db->beginTransaction();
        try {
            if ($id) {
                $revision=Input::integer($input,'revision',1,100000000,1);
                $q=$db->prepare('UPDATE '.Database::table('shop_categories').' SET name=?,slug=?,description=?,active=?,revision=revision+1 WHERE id=? AND revision=?');
                $q->execute([$name,$slug,$description,(int)$active,$id,$revision]);
                if($q->rowCount()!==1){throw new RuntimeException('Category changed. Reload before saving.');}
            } else {
                $q=$db->prepare('INSERT INTO '.Database::table('shop_categories').' (name,slug,description,active) VALUES (?,?,?,?)');$q->execute([$name,$slug,$description,(int)$active]);$id=(int)$db->lastInsertId();
            }
            Database::audit($db,$actor,'catalog.category.saved');$db->commit();return $id;
        } catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function product(int $id, bool $public = false, ?PDO $db = null): ?array
    {
        $db??=Database::connect();$q=$db->prepare('SELECT * FROM '.Database::table('products').' WHERE id=?');$q->execute([$id]);$row=$q->fetch();
        if(!$row || ($public && (!$row['published_json'] || !self::options()['enabled']))){return null;}
        $row['data']=json_decode($public?$row['published_json']:$row['draft_json'],true,64,JSON_THROW_ON_ERROR);
        if($public && $row['data']['category_id']){
            $q=$db->prepare('SELECT active FROM '.Database::table('shop_categories').' WHERE id=?');$q->execute([$row['data']['category_id']]);
            if((int)$q->fetchColumn()!==1){return null;}
        }
        return $row;
    }
    public static function bySlug(string $slug): ?array
    {
        $q=Database::connect()->prepare('SELECT id FROM '.Database::table('products').' WHERE slug=?');$q->execute([$slug]);$id=$q->fetchColumn();return $id?self::product((int)$id,true):null;
    }
    public static function listing(bool $public=false, string $search='', int $category=0, int $page=1): array
    {
        $db=Database::connect();$where=[];$args=[];
        if($public){
            if(!self::options()['enabled']){return ['items'=>[],'total'=>0,'pages'=>1,'page'=>1];}
            $where[]='p.published_json IS NOT NULL';
            $where[]="(CAST(JSON_UNQUOTE(JSON_EXTRACT(p.published_json,'$.category_id')) AS UNSIGNED)=0 OR EXISTS (SELECT 1 FROM ".Database::table('shop_categories')." c WHERE c.id=CAST(JSON_UNQUOTE(JSON_EXTRACT(p.published_json,'$.category_id')) AS UNSIGNED) AND c.active=1))";
        }
        if($search!==''){$where[]=($public?"JSON_UNQUOTE(JSON_EXTRACT(p.published_json,'$.name'))":'p.name').' LIKE ?';$args[]='%'.substr($search,0,100).'%';}
        if($category>0){$where[]="CAST(JSON_UNQUOTE(JSON_EXTRACT(p.".($public?'published_json':'draft_json').",'$.category_id')) AS UNSIGNED)=?";$args[]=$category;}
        $clause=$where?' WHERE '.implode(' AND ',$where):'';
        $q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('products').' p'.$clause);$q->execute($args);$total=(int)$q->fetchColumn();$pages=max(1,(int)ceil($total/12));$page=max(1,min($page,$pages));
        $q=$db->prepare('SELECT p.* FROM '.Database::table('products').' p'.$clause.' ORDER BY p.id DESC LIMIT 12 OFFSET '.(($page-1)*12));$q->execute($args);$items=$q->fetchAll();
        foreach($items as &$item){$item['data']=json_decode($item[$public?'published_json':'draft_json'],true,64,JSON_THROW_ON_ERROR);$item['variants']=self::variants((int)$item['id'],$public);}
        return compact('items','total','pages','page');
    }
    public static function saveProduct(int $id, array $input, int $actor): int
    {
        $action=Input::choice($input,'action',['draft','publish','unpublish'],'draft');
        $name=Input::text($input,'name',160,true);$slug=ContentSchema::slug(Input::text($input,'slug',90),$name);
        $data=['name'=>$name,'description'=>Input::text($input,'description',10000),'size_guide'=>Input::text($input,'size_guide',4000),'category_id'=>Input::integer($input,'category_id',0,PHP_INT_MAX,0)];
        $images=$input['image_ids']??[];
        if(!is_array($images)||count($images)>8){throw new InvalidArgumentException('Choose up to eight product photographs.');}
        $data['image_ids']=[];
        foreach($images as $image){$media=Input::integer(['id'=>$image],'id',1,PHP_INT_MAX);if(!in_array($media,$data['image_ids'],true)){$data['image_ids'][]=$media;}}
        $db=Database::connect();$db->beginTransaction();
        try{
            if($data['category_id']){$q=$db->prepare('SELECT id FROM '.Database::table('shop_categories').' WHERE id=?');$q->execute([$data['category_id']]);if(!$q->fetchColumn()){throw new InvalidArgumentException('Choose an existing category.');}}
            foreach($data['image_ids'] as $image){$m=Media::get($image);if(!$m||$m['kind']!=='image'){throw new InvalidArgumentException('Select an uploaded product image.');}}
            $existing=null;
            if($id){$q=$db->prepare('SELECT * FROM '.Database::table('products').' WHERE id=? FOR UPDATE');$q->execute([$id]);$existing=$q->fetch();if(!$existing||(int)$existing['revision']!==Input::integer($input,'revision',1,100000000,1)){throw new RuntimeException('Product changed. Reload before saving.');}if($existing['slug']!==$slug){throw new InvalidArgumentException('The product URL is fixed after creation to preserve existing links.');}}
            if($action==='publish'){
                if(($input['approved']??'')!=='1'||$data['description']===''||!$data['image_ids']){throw new InvalidArgumentException('Publication needs a description, product photograph and approval confirmation.');}
                $q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('product_variants').' WHERE product_id=? AND active=1');$q->execute([$id]);if(!$id||!(int)$q->fetchColumn()){throw new InvalidArgumentException('Save a draft and add at least one active priced variant before publication.');}
            }
            $json=json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
            $published=match($action){'publish'=>$json,'unpublish'=>null,default=>$existing['published_json']??null};
            if($id){$q=$db->prepare('UPDATE '.Database::table('products').' SET name=?,draft_json=?,published_json=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$name,$json,$published,$id]);}
            else{$q=$db->prepare('INSERT INTO '.Database::table('products').' (slug,name,draft_json,published_json,created_at,updated_at) VALUES (?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())');$q->execute([$slug,$name,$json,$published]);$id=(int)$db->lastInsertId();}
            Database::audit($db,$actor,'catalog.product.'.$action);$db->commit();return $id;
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function variants(int $product, bool $active=false): array
    {
        $q=Database::connect()->prepare('SELECT * FROM '.Database::table('product_variants').' WHERE product_id=?'.($active?' AND active=1':'').' ORDER BY id');$q->execute([$product]);return $q->fetchAll();
    }
    public static function saveVariant(int $product, int $id, array $input, int $actor): int
    {
        $sku=strtoupper(Input::text($input,'sku',64,true));if(!preg_match('/^[A-Z0-9][A-Z0-9_-]{0,63}$/D',$sku)){throw new InvalidArgumentException('SKU must contain letters, digits, dashes or underscores.');}
        $size=Input::text($input,'size_label',40);$color=Input::text($input,'color_label',60);$price=Money::parse(Input::text($input,'price',12,true));
        if($price<1){throw new InvalidArgumentException('A merchandise variant must have a positive price.');}
        $active=($input['active']??'')==='1';$db=Database::connect();$db->beginTransaction();
        try{
            // Consistent lock order with publication: product, then variant.
            $q=$db->prepare('SELECT id FROM '.Database::table('products').' WHERE id=? FOR UPDATE');$q->execute([$product]);if(!$q->fetchColumn()){throw new RuntimeException('Product not found.');}
            if($id){$revision=Input::integer($input,'revision',1,100000000,1);$q=$db->prepare('UPDATE '.Database::table('product_variants').' SET sku=?,size_label=?,color_label=?,price_cents=?,active=?,revision=revision+1 WHERE id=? AND product_id=? AND revision=?');$q->execute([$sku,$size,$color,$price,(int)$active,$id,$product,$revision]);if($q->rowCount()!==1){throw new RuntimeException('Variant changed. Reload before saving.');}}
            else{$q=$db->prepare('INSERT INTO '.Database::table('product_variants').' (product_id,sku,size_label,color_label,price_cents,active) VALUES (?,?,?,?,?,?)');$q->execute([$product,$sku,$size,$color,$price,(int)$active]);$id=(int)$db->lastInsertId();}
            Database::audit($db,$actor,'catalog.variant.saved');$db->commit();return $id;
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function adjustStock(int $id, array $input, int $actor): void
    {
        $delta=Input::integer($input,'delta',-1000000,1000000);if(!$delta){throw new InvalidArgumentException('Enter a nonzero stock adjustment.');}
        $reason=Input::text($input,'reason',300,true);$nonce=Input::text($input,'nonce',64,true);if(!preg_match('/^[a-f0-9]{64}$/D',$nonce)){throw new InvalidArgumentException('Reload the inventory form.');}
        $operation=hash('sha256',$actor.':'.$id.':'.$nonce);$revision=Input::integer($input,'revision',1,100000000,1);$db=Database::connect();$db->beginTransaction();
        try{
            $q=$db->prepare('SELECT stock,revision FROM '.Database::table('product_variants').' WHERE id=? FOR UPDATE');$q->execute([$id]);$v=$q->fetch();if(!$v){throw new RuntimeException('Variant not found.');}
            $q=$db->prepare('SELECT quantity_delta,reason FROM '.Database::table('inventory_adjustments').' WHERE operation_hash=?');$q->execute([$operation]);$old=$q->fetch();
            if($old){if((int)$old['quantity_delta']!==$delta||$old['reason']!==$reason){throw new RuntimeException('That stock form was already used. Reload it.');}$db->commit();return;}
            if((int)$v['revision']!==$revision){throw new RuntimeException('Inventory changed. Reload before adjusting.');}
            $after=(int)$v['stock']+$delta;if($after<0||$after>1000000){throw new InvalidArgumentException('Adjustment would put stock outside the allowed range.');}
            $q=$db->prepare('UPDATE '.Database::table('product_variants').' SET stock=?,revision=revision+1 WHERE id=?');$q->execute([$after,$id]);
            $q=$db->prepare('INSERT INTO '.Database::table('inventory_adjustments').' (variant_id,operation_hash,quantity_delta,stock_after,reason,actor_id,created_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())');$q->execute([$id,$operation,$delta,$after,$reason,$actor]);
            Database::audit($db,$actor,'catalog.stock.adjusted');$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function publicImage(int $id): bool
    {
        if(!self::options()['enabled']){return false;}
        // Exact JSON array membership: do not mistake media 1 for media 11.
        $q=Database::connect()->prepare('SELECT id FROM '.Database::table('products')." WHERE published_json IS NOT NULL AND JSON_CONTAINS(JSON_EXTRACT(published_json,'$.image_ids'),?)");$q->execute([(string)$id]);
        foreach($q->fetchAll(PDO::FETCH_COLUMN) as $product){if(self::product((int)$product,true)){return true;}}
        return false;
    }
    public static function label(array $variant): string {return implode(' / ',array_filter([$variant['color_label'],$variant['size_label']])) ?: $variant['sku'];}
}
