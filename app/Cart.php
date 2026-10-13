<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/** Browser-owned bag. Prices and availability are always read from the database. */
final class Cart
{
    public static function cookieName(): string {return 'sg_cart_'.substr(hash('sha256',Paths::root()),0,12);}
    private static function cookie(string $token,int $expires): void
    {
        setcookie(self::cookieName(),$token,['expires'=>$expires,'path'=>Paths::basePath().'/','secure'=>Security::isHttps(),'httponly'=>true,'samesite'=>'Lax']);
        $_COOKIE[self::cookieName()]=$token;
    }
    public static function forget(): void {self::cookie('',time()-3600);}
    private static function id(PDO $db,bool $create=false): int
    {
        $token=$_COOKIE[self::cookieName()]??'';
        if(is_string($token)&&preg_match('/^[a-f0-9]{64}$/D',$token)){
            $q=$db->prepare('SELECT id FROM '.Database::table('carts').' WHERE token_hash=? AND expires_at>UTC_TIMESTAMP()');$q->execute([hash_hmac('sha256',$token,Paths::config()['secret'])]);$id=$q->fetchColumn();if($id){return (int)$id;}
        }
        if(!$create){return 0;}
        $token=bin2hex(random_bytes(32));$q=$db->prepare('INSERT INTO '.Database::table('carts').' (token_hash,expires_at,updated_at) VALUES (?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 30 DAY),UTC_TIMESTAMP())');$q->execute([hash_hmac('sha256',$token,Paths::config()['secret'])]);
        $id=(int)$db->lastInsertId();self::cookie($token,time()+30*86400);return $id;
    }
    public static function mutate(array $input): void
    {
        $variant=Input::integer($input,'variant_id',1,PHP_INT_MAX);$quantity=Input::integer($input,'quantity',0,20);$mode=Input::choice($input,'action',['add','set'],'add');
        $nonce=Input::text($input,'nonce',64,true);if(!preg_match('/^[a-f0-9]{64}$/D',$nonce)){throw new InvalidArgumentException('Reload the bag form.');}
        RequestLimit::consume('cart-changes',120,900);
        $db=Database::connect();$id=self::id($db,true);$db->beginTransaction();
        try{
            $q=$db->prepare('SELECT id FROM '.Database::table('carts').' WHERE id=? FOR UPDATE');$q->execute([$id]);$q->fetch();
            $hash=hash('sha256',$nonce);$payload=hash('sha256',$variant.':'.$quantity.':'.$mode);
            $q=$db->prepare('SELECT payload_hash FROM '.Database::table('cart_operations').' WHERE cart_id=? AND nonce_hash=?');$q->execute([$id,$hash]);$old=$q->fetchColumn();
            if($old!==false){if(!hash_equals($old,$payload)){throw new RuntimeException('That bag form was already used. Reload before changing it.');}$db->commit();return;}
            $q=$db->prepare('SELECT quantity FROM '.Database::table('cart_items').' WHERE cart_id=? AND variant_id=?');$q->execute([$id,$variant]);$prior=$q->fetchColumn();$desired=$mode==='add'?((int)$prior+$quantity):$quantity;
            if($desired>0){
                if(!Catalog::options()['enabled']){throw new RuntimeException('The merchandise catalog is not open.');}
                $q=$db->prepare('SELECT * FROM '.Database::table('product_variants').' WHERE id=? FOR UPDATE');$q->execute([$variant]);$v=$q->fetch();
                if(!$v||!(int)$v['active']||!Catalog::product((int)$v['product_id'],true,$db)){throw new RuntimeException('That item is no longer available.');}
                if($desired>20||$desired>(int)$v['stock']){throw new RuntimeException('Requested quantity is not available. The bag was not changed.');}
                if($prior===false){$q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('cart_items').' WHERE cart_id=?');$q->execute([$id]);if((int)$q->fetchColumn()>=30){throw new RuntimeException('The bag can contain up to 30 different variants.');}}
                $q=$db->prepare('INSERT INTO '.Database::table('cart_items').' (cart_id,variant_id,quantity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE quantity=VALUES(quantity)');$q->execute([$id,$variant,$desired]);
            }else{$q=$db->prepare('DELETE FROM '.Database::table('cart_items').' WHERE cart_id=? AND variant_id=?');$q->execute([$id,$variant]);}
            $q=$db->prepare('INSERT INTO '.Database::table('cart_operations').' (cart_id,nonce_hash,payload_hash) VALUES (?,?,?)');$q->execute([$id,$hash,$payload]);
            $q=$db->prepare('UPDATE '.Database::table('carts').' SET updated_at=UTC_TIMESTAMP() WHERE id=?');$q->execute([$id]);$db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function summary(): array
    {
        $db=Database::connect();$id=self::id($db);$items=[];$subtotal=0;$count=0;$problems=false;
        if($id){
            $q=$db->prepare('SELECT i.variant_id,i.quantity,v.* FROM '.Database::table('cart_items').' i LEFT JOIN '.Database::table('product_variants').' v ON v.id=i.variant_id WHERE i.cart_id=? ORDER BY i.variant_id');$q->execute([$id]);
            foreach($q->fetchAll() as $row){
                $product=isset($row['product_id'])?Catalog::product((int)$row['product_id'],true):null;
                $available=$product&&(int)$row['active']===1;$issue=!$available?'No longer available':((int)$row['stock']<(int)$row['quantity']?'Quantity exceeds current stock':'');
                $quantity=(int)$row['quantity'];$count+=$quantity;$price=$available?(int)$row['price_cents']:0;$total=$price*$quantity;
                if($issue===''){$subtotal+=$total;}else{$problems=true;}
                $items[]=['variant_id'=>(int)$row['variant_id'],'quantity'=>$quantity,'product'=>$product,'label'=>$available?Catalog::label($row):'Unavailable item','price'=>$price,'total'=>$total,'issue'=>$issue];
            }
        }
        return compact('items','subtotal','count','problems');
    }
}
