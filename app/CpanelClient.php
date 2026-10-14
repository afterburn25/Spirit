<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;

/** Installer-only cPanel UAPI client. Never persists panel credentials. */
final class CpanelClient
{
    private const FUNCTIONS=['get_restrictions','list_databases','list_users','create_database','create_user','set_privileges_on_database'];
    private array $credentials;
    private $transport;
    private ?array $endpoint;
    private float $deadline;

    public function __construct(#[\SensitiveParameter] array $credentials, ?callable $testTransport=null, ?array $testEndpoint=null)
    {
        if(($testTransport!==null||$testEndpoint!==null)&&(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1')){throw new RuntimeException('Test cPanel transport is restricted to the isolated CLI tests.');}
        $this->credentials=self::validate($credentials);$this->transport=$testTransport;$this->endpoint=$testEndpoint;$this->deadline=microtime(true)+22;
        if($testEndpoint!==null&&(!in_array($testEndpoint['ip']??null,['127.0.0.1'],true)||!is_int($testEndpoint['port']??null)||$testEndpoint['port']<1||$testEndpoint['port']>65535)){throw new RuntimeException('Test cPanel endpoint must be loopback.');}
    }
    public static function validate(#[\SensitiveParameter] array $input): array
    {
        foreach(['host','user','token'] as $key){if(!isset($input[$key])||!is_string($input[$key])){throw new InvalidArgumentException('Enter the cPanel server hostname, username and API token.');}}
        $host=strtolower(trim($input['host']));$user=trim($input['user']);$token=$input['token'];
        if(strlen($host)>253||filter_var($host,FILTER_VALIDATE_IP)||!preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/D',$host)||preg_match('/\.(?:localhost|local|internal|invalid|test|example)$/D',$host)){throw new InvalidArgumentException('Enter the public cPanel server hostname only, without https, port, login path or IP address.');}
        if(!preg_match('/^[a-z][a-z0-9]{0,31}$/D',$user)){throw new InvalidArgumentException('Enter the cPanel account username, not the website owner email.');}
        if(!preg_match('/^[A-Za-z0-9_-]{20,512}$/D',$token)){throw new InvalidArgumentException('Enter the cPanel API token without spaces. Do not enter your cPanel password.');}
        return ['host'=>$host,'user'=>$user,'token'=>$token];
    }
    public function identity(): array {return ['host'=>$this->credentials['host'],'user'=>$this->credentials['user']];}
    public static function publicAddress(string $ip): bool
    {
        return filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4|FILTER_FLAG_GLOBAL_RANGE)!==false && (int)explode('.',$ip,2)[0]<224;
    }
    /** Accept the documented envelope and the direct /execute result shape. */
    public static function decode(string $body,string $function): mixed
    {
        if(strlen($body)>1048576){throw new RuntimeException('cPanel response exceeded the safe size limit.');}
        try{$value=json_decode($body,true,32,JSON_THROW_ON_ERROR);}catch(\JsonException $e){throw new RuntimeException('cPanel returned an unreadable response. No response body has been logged.');}
        if(!is_array($value)){throw new RuntimeException('cPanel returned an unexpected response.');}
        if(array_key_exists('result',$value)){
            if(($value['module']??null)!=='Mysql'||($value['func']??null)!==$function||($value['apiversion']??null)!==3||!is_array($value['result'])){throw new RuntimeException('cPanel returned a mismatched API response.');}
            $value=$value['result'];
        }
        if(($value['status']??null)===0){throw new CpanelRejected('cPanel rejected '.$function.'. Check API access, database quota and account restrictions in cPanel. Provider response details are not displayed or logged.');}
        if(($value['status']??null)!==1||!array_key_exists('data',$value)||!empty($value['errors'])){throw new RuntimeException('cPanel did not confirm the requested operation.');}
        return $value['data'];
    }
    public function call(string $function,#[\SensitiveParameter] array $parameters=[]): mixed
    {
        if(!in_array($function,self::FUNCTIONS,true)){throw new InvalidArgumentException('This installer cannot run that cPanel operation.');}
        if($this->transport!==null){return self::decode(($this->transport)($function,$parameters),$function);}
        if($this->endpoint===null&&getenv('SPIRIT_DISABLE_CPANEL_NETWORK')==='1'){throw new RuntimeException('External cPanel networking is disabled in this environment.');}
        if(!extension_loaded('curl')){throw new RuntimeException('Enable the PHP cURL extension in cPanel for automatic database setup, or use an existing database.');}
        $host=$this->credentials['host'];$port=2083;
        if($this->endpoint!==null){$ip=$this->endpoint['ip'];$port=$this->endpoint['port'];}
        else{
            $addresses=@gethostbynamel($host);
            if(!$addresses||count($addresses)>32){throw new RuntimeException('The cPanel hostname did not resolve to a supported public IPv4 address.');}
            foreach($addresses as $address){if(!self::publicAddress($address)){throw new RuntimeException('Automatic cPanel setup refuses local, private or reserved network destinations.');}}
            $ip=$addresses[0];
        }
        $remaining=$this->deadline-microtime(true);
        if($remaining<=0){throw new RuntimeException('The cPanel operation time budget expired. Retry from this page after reviewing the saved state.');}
        $body='';$overflow=false;$curl=curl_init('https://'.$host.':'.$port.'/execute/Mysql/'.$function);
        if($curl===false){throw new RuntimeException('Unable to initialize the secure cPanel connection.');}
        try{
            $settings=[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($parameters,'','&',PHP_QUERY_RFC3986),CURLOPT_HTTPHEADER=>['Authorization: cpanel '.$this->credentials['user'].':'.$this->credentials['token'],'Content-Type: application/x-www-form-urlencoded','Accept: application/json','Expect:'],CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_REDIR_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_MAXREDIRS=>0,CURLOPT_PROXY=>'',CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_CONNECTTIMEOUT_MS=>min(5000,(int)ceil($remaining*1000)),CURLOPT_TIMEOUT_MS=>min(8000,(int)ceil($remaining*1000)),CURLOPT_RESOLVE=>[$host.':'.$port.':'.$ip],CURLOPT_WRITEFUNCTION=>static function($handle,string $chunk)use(&$body,&$overflow):int{if(strlen($body)+strlen($chunk)>1048576){$overflow=true;return 0;}$body.=$chunk;return strlen($chunk);}];
            if($this->endpoint!==null&&isset($this->endpoint['ca'])){$settings[CURLOPT_CAINFO]=$this->endpoint['ca'];}
            curl_setopt_array($curl,$settings);$ok=curl_exec($curl);$status=(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
            if($ok===false||$overflow){throw new RuntimeException('The secure cPanel request did not complete. Verify the server hostname, TLS certificate and outbound port 2083 access. No credential or response body has been logged.');}
            if(in_array($status,[401,403],true)){throw new CpanelRejected('cPanel authorization was refused. Check the account username, token expiry and API permissions.');}
            if($status!==200){throw new RuntimeException('cPanel returned an unexpected HTTP status. Redirects are never followed with your token.');}
            return self::decode($body,$function);
        }finally{curl_close($curl);}
    }
}
