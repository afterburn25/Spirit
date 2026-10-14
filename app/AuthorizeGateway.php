<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

final class AuthorizeGateway
{
    /** Tests may inject a transport in CLI only. Web handlers never accept one. */
    public static function request(string $mode,string $action,array $data,string $login='',?callable $transport=null): array
    {
        PaymentSettings::mode($mode);
        if(!in_array($action,['authenticateTestRequest','createTransactionRequest','getTransactionDetailsRequest'],true)){throw new RuntimeException('payment_action_invalid');}
        if($transport!==null&&PHP_SAPI!=='cli'){throw new RuntimeException('payment_custom_transport_refused');}
        $payload=[$action=>array_merge(['merchantAuthentication'=>PaymentSettings::credentials($mode,$login)],$data)];
        $json=json_encode($payload,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if($transport!==null){$response=$transport($payload);if(!is_array($response)){throw new PaymentUncertain('payment_response_invalid');}return $response;}
        if(getenv('SPIRIT_DISABLE_PAYMENT_NETWORK')==='1'){throw new RuntimeException('payment_network_disabled');}
        $endpoint=$mode==='sandbox'?'https://apitest.authorize.net/xml/v1/request.api':'https://api.authorize.net/xml/v1/request.api';
        return AuthorizeHttp::post($endpoint,$json);
    }
    public static function transactionId(mixed $id): ?string
    {
        return (is_string($id)||is_int($id))&&preg_match('/^[1-9][0-9]{0,31}$/D',(string)$id)?(string)$id:null;
    }
    public static function result(array $response): array
    {
        $r=$response['transactionResponse']??[];$code=(string)($r['responseCode']??'');$id=self::transactionId($r['transId']??null);
        $errors=$r['errors']??[];$errorCodes=array_column(is_array($errors)?$errors:[],'errorCode');
        $state='uncertain';
        if(in_array('11',array_map('strval',$errorCodes),true)){$state='uncertain';}
        elseif(($response['messages']['resultCode']??'')==='Ok'&&$code==='1'&&$id){$state='succeeded';}
        elseif($code==='4'&&$id){$state='held';}
        elseif($code==='2'){$state='declined';}
        elseif($code==='3'&&!$id){$state='declined';}
        // Test-mode transaction id zero, malformed replies and unknown API errors
        // are not success and are never blindly submitted again.
        $last=(string)($r['accountNumber']??'');$lastFour=preg_match('/^[Xx*]+([0-9]{4})$/D',$last,$m)?$m[1]:null;
        return ['state'=>$state,'transaction_id'=>$id,'last_four'=>$lastFour,'error'=>$state==='declined'?'payment_declined':($state==='uncertain'?'payment_acceptance_uncertain':null)];
    }
    public static function details(string $mode,string $id,string $login='',?callable $transport=null): array
    {
        if(!self::transactionId($id)){throw new RuntimeException('payment_transaction_invalid');}
        $r=self::request($mode,'getTransactionDetailsRequest',['transId'=>$id],$login,$transport);
        if(($r['messages']['resultCode']??'')!=='Ok'||!is_array($r['transaction']??null)){throw new RuntimeException('payment_reporting_unavailable');}return $r['transaction'];
    }
    /** The trusted reporting API may serialize decimal amounts as JSON numbers. */
    public static function reportedCents(mixed $amount): int
    {
        if(is_string($amount)){return Money::parse($amount);}
        if(!is_int($amount)&&!is_float($amount)){throw new RuntimeException('payment_amount_invalid');}
        if(!is_finite((float)$amount)||$amount<0||$amount>99999.99||abs($amount*100-round($amount*100))>0.000001){throw new RuntimeException('payment_amount_invalid');}
        return (int)round($amount*100);
    }
}
