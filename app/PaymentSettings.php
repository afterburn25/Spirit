<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

final class PaymentSettings
{
    public static function mode(string $value): string
    {
        if(!in_array($value,['sandbox','production'],true)){throw new InvalidArgumentException('Select sandbox or production.');}return $value;
    }
    public static function all(): array {return array_merge(['environment'=>'sandbox','enabled'=>false,'sandbox'=>[],'production'=>[]],ConfigStore::read('payments'));}
    public static function environment(string $mode): array
    {
        self::mode($mode);return array_merge(['login_id'=>'','transaction_key'=>'','signature_key'=>'','client_key'=>''],self::all()[$mode]??[]);
    }
    public static function available(): bool
    {
        $o=self::all();$e=self::environment($o['environment']);
        return $o['enabled']&&Catalog::options()['enabled']&&extension_loaded('curl')&&$e['login_id']!==''&&$e['transaction_key']!==''&&$e['signature_key']!==''&&$e['client_key']!==''&&DeliveryPolicy::options()['configured'];
    }
    public static function save(array $input,int $actor): void
    {
        $o=self::all();$mode=self::mode(Input::text($input,'environment',12,true));$prior=self::environment($mode);
        $login=Input::text($input,'login_id',64,true);$client=Input::text($input,'client_key',160,true);
        if(!preg_match('/^[A-Za-z0-9_-]{1,64}$/D',$login)||!preg_match('/^[A-Za-z0-9_-]{1,160}$/D',$client)){throw new InvalidArgumentException('Copy the API Login ID and Public Client Key without spaces.');}
        // Prevent orders ever silently changing their merchant account. Keys may
        // rotate; changing merchant identity requires a deliberate migration.
        if($prior['login_id']!==''&&!hash_equals($prior['login_id'],$login)){throw new RuntimeException('This environment is bound to its existing merchant Login ID. Account migration is not an ordinary key update.');}
        $e=['login_id'=>$login,'client_key'=>$client];
        foreach(['transaction_key','signature_key'] as $key){
            $value=Input::text($input,$key,160);
            if($value!==''&&($key==='signature_key'?!preg_match('/^[A-Fa-f0-9]{128}$/D',$value):!preg_match('/^[A-Za-z0-9_-]{8,160}$/D',$value))){throw new InvalidArgumentException('The supplied '.str_replace('_',' ',$key).' has an invalid format.');}
            $e[$key]=$value===''?$prior[$key]:PrivateValue::seal($value);
        }
        $enabled=($input['enabled']??'')==='1';
        if($enabled&&($e['transaction_key']===''||$e['signature_key']===''||!extension_loaded('curl'))){throw new RuntimeException('Enable PHP cURL and supply all four API values before enabling checkout.');}
        if($enabled&&!DeliveryPolicy::options()['configured']){throw new RuntimeException('Save the shipping, tax and store-policy configuration before enabling checkout.');}
        if($enabled&&$mode==='production'&&($input['live_confirmation']??'')!=='ENABLE LIVE PAYMENTS'){throw new RuntimeException('Production requires explicitly entering ENABLE LIVE PAYMENTS. Use sandbox until provider, security and merchant checks are accepted.');}
        $o[$mode]=$e;$o['environment']=$mode;$o['enabled']=$enabled;ConfigStore::write('payments',$o,$actor);
    }
    public static function credentials(string $mode,string $expectedLogin=''): array
    {
        $e=self::environment($mode);
        if($e['login_id']===''||$e['transaction_key']===''||($expectedLogin!==''&&!hash_equals($expectedLogin,$e['login_id']))){throw new RuntimeException('payment_configuration_unavailable');}
        return ['name'=>$e['login_id'],'transactionKey'=>PrivateValue::open($e['transaction_key'])];
    }
    public static function signature(string $mode): string
    {
        $key=self::environment($mode)['signature_key'];return $key===''?'':PrivateValue::open($key);
    }
    public static function script(string $mode): string {return self::mode($mode)==='sandbox'?'https://jstest.authorize.net/v1/Accept.js':'https://js.authorize.net/v1/Accept.js';}
}
