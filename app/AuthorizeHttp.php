<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

/** A bounded HTTPS JSON client. Public calls use only fixed Authorize.net URLs. */
final class AuthorizeHttp
{
    public static function post(string $endpoint,string $json,float $seconds=25,?string $caFile=null): array
    {
        $known=in_array($endpoint,['https://apitest.authorize.net/xml/v1/request.api','https://api.authorize.net/xml/v1/request.api'],true);
        $url=parse_url($endpoint);$loopback=is_array($url)&&($url['scheme']??'')==='https'&&in_array($url['host']??'',['localhost','127.0.0.1'],true)&&!isset($url['user'])&&!isset($url['pass'])&&!isset($url['fragment']);
        $test=PHP_SAPI==='cli'&&getenv('SPIRIT_TESTING')==='1'&&$loopback;
        if(!$known&&!$test){throw new RuntimeException('payment_endpoint_refused');}
        if($caFile!==null&&!$test){throw new RuntimeException('payment_trust_override_refused');}
        if($seconds<=0||$seconds>25||strlen($json)>131072){throw new RuntimeException('payment_request_bounds');}
        if(!extension_loaded('curl')){throw new RuntimeException('payment_curl_unavailable');}
        $ch=curl_init($endpoint);$body='';
        $options=[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$json,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'],CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT_MS=>(int)(min(8,$seconds)*1000),CURLOPT_TIMEOUT_MS=>max(1,(int)($seconds*1000)),CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_REDIR_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_WRITEFUNCTION=>static function($handle,string $chunk)use(&$body):int{if(strlen($body)+strlen($chunk)>1048576){return 0;}$body.=$chunk;return strlen($chunk);}];
        if($caFile!==null){$options[CURLOPT_CAINFO]=$caFile;}curl_setopt_array($ch,$options);
        try{$ok=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);if($ok===false||$status!==200){throw new PaymentUncertain('payment_transport_uncertain');}}
        finally{curl_close($ch);}
        try{$decoded=json_decode(preg_replace('/^\xEF\xBB\xBF/','',$body),true,64,JSON_THROW_ON_ERROR|JSON_BIGINT_AS_STRING);}catch(\Throwable $e){throw new PaymentUncertain('payment_response_invalid');}
        if(!is_array($decoded)){throw new PaymentUncertain('payment_response_invalid');}return $decoded;
    }
}
