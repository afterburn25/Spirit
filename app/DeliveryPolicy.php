<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

/** Explicit merchant configuration, not an automatic tax-compliance service. */
final class DeliveryPolicy
{
    public const STATES=['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'];
    public static function options(): array {return array_merge(['configured'=>false,'shipping_cents'=>0,'rates'=>[],'zip_rates'=>[],'shipping_taxable'=>false,'policy'=>''],ConfigStore::read('store')['delivery']??[]);}
    public static function rate(string $percentage): int
    {
        if(!preg_match('/^(0|[1-9][0-9]?)(?:\.([0-9]{1,4}))?$/D',$percentage,$m)){throw new InvalidArgumentException('Use a tax percentage from 0 to 99.9999 with up to four decimal places.');}
        return (int)$m[1]*10000+(int)str_pad($m[2]??'',4,'0');
    }
    public static function save(array $input,int $actor): void
    {
        $shipping=Money::parse(Input::text($input,'shipping',12,true));$rates=[];$zip=[];
        foreach(explode("\n",Input::text($input,'rates',5000,true)) as $line){
            if(trim($line)===''){continue;}$parts=explode(':',trim($line));
            if(count($parts)!==2||!in_array(strtoupper(trim($parts[0])),self::STATES,true)){throw new InvalidArgumentException('Each supported destination is a STATE:percentage line, for example LA:0. Set actual rates appropriate to your obligations.');}
            $state=strtoupper(trim($parts[0]));if(isset($rates[$state])){throw new InvalidArgumentException('Duplicate state configuration.');}$rates[$state]=self::rate(trim($parts[1]));
        }
        foreach(explode("\n",Input::text($input,'zip_rates',12000)) as $line){
            if(trim($line)===''){continue;}$parts=explode(':',trim($line));
            if(count($parts)!==3||!isset($rates[strtoupper(trim($parts[0]))])||!preg_match('/^[0-9]{5}$/D',trim($parts[1]))){throw new InvalidArgumentException('ZIP override format is STATE:ZIP:percentage. Its state must be supported.');}
            $key=strtoupper(trim($parts[0])).':'.trim($parts[1]);if(isset($zip[$key])){throw new InvalidArgumentException('Duplicate ZIP override.');}$zip[$key]=self::rate(trim($parts[2]));
        }
        $policy=Input::text($input,'policy',6000,true);
        if(!$rates||($input['reviewed']??'')!=='1'){throw new InvalidArgumentException('Review shipping, returns and tax obligations and explicitly confirm the configuration.');}
        $d=['configured'=>true,'shipping_cents'=>$shipping,'rates'=>$rates,'zip_rates'=>$zip,'shipping_taxable'=>($input['shipping_taxable']??'')==='1','policy'=>$policy];
        $store=ConfigStore::read('store');$store['delivery']=$d;ConfigStore::write('store',$store,$actor);
    }
    public static function validate(array $input): array
    {
        $fields=['recipient'=>100,'line_one'=>60,'line_two'=>60,'city'=>40,'postal_code'=>10,'billing_line'=>60,'billing_city'=>40,'billing_zip'=>10];$a=[];
        $same=($input['billing_same']??'')==='1';
        foreach($fields as $key=>$max){$a[$key]=Input::text($input,$key,$max,$key!=='line_two'&&(!str_starts_with($key,'billing_')||!$same));if(preg_match('/[\x00-\x1f\x7f]/',$a[$key])){throw new InvalidArgumentException('Address fields cannot contain control characters.');}}
        $a['region']=Input::choice($input,'region',self::STATES,'');$a['country_code']='US';
        $a['billing_region']=$same?$a['region']:Input::choice($input,'billing_region',self::STATES,'');
        if(strlen(trim($a['line_one'].' '.$a['line_two']))>60){throw new InvalidArgumentException('Street and apartment information combined must fit in 60 characters for the payment provider.');}
        if(!preg_match('/^[0-9]{5}(?:-[0-9]{4})?$/D',$a['postal_code'])){throw new InvalidArgumentException('Use a US ZIP or ZIP+4.');}
        if($same){$a['billing_line']=trim($a['line_one'].' '.$a['line_two']);$a['billing_city']=$a['city'];$a['billing_zip']=$a['postal_code'];}
        if(!preg_match('/^[0-9]{5}(?:-[0-9]{4})?$/D',$a['billing_zip'])){throw new InvalidArgumentException('Use a valid billing ZIP.');}
        return $a;
    }
    public static function quote(int $subtotal,array $address,?array $options=null): array
    {
        $o=$options??self::options();$state=$address['region'];
        if(!$o['configured']||!array_key_exists($state,$o['rates'])){throw new RuntimeException('Shipping to this state has not been configured. No payment was requested.');}
        $rate=$o['zip_rates'][$state.':'.substr($address['postal_code'],0,5)]??$o['rates'][$state];$shipping=(int)$o['shipping_cents'];
        $basis=$subtotal+($o['shipping_taxable']?$shipping:0);$tax=intdiv($basis*$rate+500000,1000000);$total=$subtotal+$shipping+$tax;
        if($subtotal<1||$total>Money::MAX_CENTS){throw new RuntimeException('Order amount is outside the supported range.');}
        return ['subtotal_cents'=>$subtotal,'shipping_cents'=>$shipping,'tax_cents'=>$tax,'total_cents'=>$total,'rate_ppm'=>$rate,'policy'=>$o['policy']];
    }
}
