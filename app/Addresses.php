<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class Addresses
{
    public static function fields(): array {return ['label'=>60,'recipient'=>120,'line_one'=>180,'line_two'=>180,'city'=>100,'region'=>100,'postal_code'=>20];}
    public static function validate(array $input): array
    {
        $data=[];foreach(self::fields() as $name=>$length){$data[$name]=Input::text($input,$name,$length,$name!=='line_two');if(preg_match('/[\x00-\x1f\x7f]/',$data[$name])){throw new InvalidArgumentException('Address fields cannot contain control characters.');}}
        // Shipping-country support is deliberately bounded for the first release.
        $data['country_code']=Input::choice($input,'country_code',['US'],'US');
        if(!preg_match('/^[0-9]{5}(?:-[0-9]{4})?$/D',$data['postal_code'])){throw new InvalidArgumentException('Enter a five-digit ZIP or ZIP+4.');}return $data;
    }
    public static function all(int $customer): array {$q=Database::connect()->prepare('SELECT * FROM '.Database::table('customer_addresses').' WHERE customer_id=? ORDER BY id');$q->execute([$customer]);return $q->fetchAll();}
    public static function save(int $customer,int $id,array $input): void
    {
        $data=self::validate($input);$db=Database::connect();$db->beginTransaction();
        try{
            $q=$db->prepare('SELECT id FROM '.Database::table('customers')." WHERE id=? AND status='active' FOR UPDATE");$q->execute([$customer]);if(!$q->fetchColumn()){throw new RuntimeException('Customer account unavailable.');}
            if($id){$revision=Input::integer($input,'revision',1,100000000,1);$q=$db->prepare('UPDATE '.Database::table('customer_addresses').' SET label=?,recipient=?,line_one=?,line_two=?,city=?,region=?,postal_code=?,country_code=?,revision=revision+1 WHERE id=? AND customer_id=? AND revision=?');$q->execute([...array_values($data),$id,$customer,$revision]);if($q->rowCount()!==1){throw new RuntimeException('Address not found or changed. Reload before saving.');}}
            else{$q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('customer_addresses').' WHERE customer_id=?');$q->execute([$customer]);if((int)$q->fetchColumn()>=10){throw new RuntimeException('Save up to ten addresses.');}$q=$db->prepare('INSERT INTO '.Database::table('customer_addresses').' (label,recipient,line_one,line_two,city,region,postal_code,country_code,customer_id) VALUES (?,?,?,?,?,?,?,?,?)');$q->execute([...array_values($data),$customer]);}
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function remove(int $customer,int $id,int $revision): void
    {
        $q=Database::connect()->prepare('DELETE FROM '.Database::table('customer_addresses').' WHERE id=? AND customer_id=? AND revision=?');$q->execute([$id,$customer,$revision]);if($q->rowCount()!==1){throw new RuntimeException('Address not found or changed.');}
    }
}
