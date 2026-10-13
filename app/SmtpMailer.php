<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;
use Throwable;

final class SmtpMailer
{
    /** TLS-only authenticated SMTP, bounded by one overall network deadline. */
    public static function send(array $message,array $options,float $seconds=12): void
    {
        if($seconds<=0||$seconds>120){throw new RuntimeException('smtp_timeout_invalid');}
        $to=Security::email($message['recipient']);$from=Security::email($options['from_email']);
        $host=$options['smtp_host'];$port=(int)$options['smtp_port'];
        if(!is_string($host)||!preg_match('/^[A-Za-z0-9][A-Za-z0-9.-]{0,252}$/D',$host)||$port<1||$port>65535||!in_array($options['smtp_security'],['tls','starttls'],true)){throw new RuntimeException('smtp_configuration_invalid');}
        // Validate and decrypt before connecting or entering the DATA phase.
        $mime=self::mime($message,$options);$password=PrivateValue::open($options['smtp_password']);
        $context=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'peer_name'=>$host,'allow_self_signed'=>false,'SNI_enabled'=>true,'crypto_method'=>STREAM_CRYPTO_METHOD_TLS_CLIENT]]);
        $deadline=microtime(true)+$seconds;$transport=$options['smtp_security']==='tls'?'tls':'tcp';
        $socket=@stream_socket_client($transport.'://'.$host.':'.$port,$errno,$error,min(10,$seconds),STREAM_CLIENT_CONNECT,$context);
        if(!$socket){throw new RuntimeException('smtp_connection_failed');}
        $committed=false;$acknowledged=false;$domain=substr($from,strrpos($from,'@')+1);
        try{
            self::reply($socket,[220],$deadline);self::command($socket,'EHLO '.$domain,[250],$deadline);
            if($options['smtp_security']==='starttls'){
                self::command($socket,'STARTTLS',[220],$deadline);self::timeout($socket,$deadline);
                if(@stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)!==true){throw new RuntimeException('smtp_tls_verification_failed');}
                self::command($socket,'EHLO '.$domain,[250],$deadline);
            }
            self::command($socket,'AUTH LOGIN',[334],$deadline);self::command($socket,base64_encode($options['smtp_user']),[334],$deadline);self::command($socket,base64_encode($password),[235],$deadline);
            self::command($socket,'MAIL FROM:<'.$from.'>',[250],$deadline);self::command($socket,'RCPT TO:<'.$to.'>',[250,251],$deadline);self::command($socket,'DATA',[354],$deadline);
            self::write($socket,str_replace("\r\n.","\r\n..",$mime)."\r\n",$deadline);
            $committed=true;self::write($socket,".\r\n",$deadline);self::reply($socket,[250],$deadline);$acknowledged=true;
            try{self::command($socket,'QUIT',[221],$deadline);}catch(Throwable $e){}
        }catch(Throwable $e){
            // A definite SMTP rejection is retryable. Losing the final reply is
            // different: the provider may have accepted the message already.
            if($committed&&!$acknowledged&&!preg_match('/^smtp_rejected_[45][0-9]{2}$/D',$e->getMessage())){throw new DeliveryUncertain('smtp_delivery_uncertain');}
            throw $e;
        }finally{fclose($socket);}
    }

    private static function encodedHeader(string $value): string
    {
        if(!preg_match('//u',$value)||preg_match('/[\x00-\x1f\x7f]/',$value)){throw new RuntimeException('smtp_header_invalid');}
        // RFC 2047 encoded words stay below 75 characters without splitting a
        // UTF-8 code point. Fold between words, never within a base64 word.
        $words=[];$chunk='';
        foreach(preg_split('//u',$value,-1,PREG_SPLIT_NO_EMPTY) as $character){
            if(strlen($chunk.$character)>42){$words[]='=?UTF-8?B?'.base64_encode($chunk).'?=';$chunk='';}$chunk.=$character;
        }
        if($chunk!==''){$words[]='=?UTF-8?B?'.base64_encode($chunk).'?=';}
        return implode("\r\n ",$words);
    }

    public static function mime(array $message,array $options): string
    {
        $to=Security::email($message['recipient']);$from=Security::email($options['from_email']);
        $subject=Input::text($message,'subject',200,true);$fromName=Input::text($options,'from_name',120,true);
        $boundary='spirit-'.bin2hex(random_bytes(16));$html=(string)$message['html'];
        $text=html_entity_decode(strip_tags(str_replace(['<br>','<br/>','<br />','</p>','</div>'],"\n",$html)),ENT_QUOTES|ENT_HTML5,'UTF-8');
        $id=hash('sha256',$from.':'.(string)($message['id']??'').':'.(string)($message['dedupe_key']??$subject));$domain=substr($from,strrpos($from,'@')+1);
        $headers=['Date: '.gmdate('D, d M Y H:i:s').' +0000','Message-ID: <'.$id.'@'.$domain.'>','From: '.self::encodedHeader($fromName)."\r\n <".$from.'>','To: <'.$to.'>','Subject: '.self::encodedHeader($subject),'MIME-Version: 1.0','Content-Type: multipart/alternative; boundary="'.$boundary.'"'];
        $body=implode("\r\n",$headers)."\r\n\r\n";
        foreach(['plain'=>$text,'html'=>$html] as $type=>$part){$body.='--'.$boundary."\r\nContent-Type: text/".$type."; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($part),76,"\r\n");}
        return $body.'--'.$boundary."--\r\n";
    }

    private static function timeout($socket,float $deadline): void
    {
        $remaining=$deadline-microtime(true);
        if($remaining<=0){throw new RuntimeException('smtp_deadline_exceeded');}
        $remaining=min(10,$remaining);$whole=(int)$remaining;
        stream_set_timeout($socket,$whole,max(1,(int)(($remaining-$whole)*1000000)));
    }
    private static function command($socket,string $command,array $codes,float $deadline): void
    {
        self::write($socket,$command."\r\n",$deadline);self::reply($socket,$codes,$deadline);
    }
    private static function write($socket,string $data,float $deadline): void
    {
        $length=strlen($data);$offset=0;
        while($offset<$length){self::timeout($socket,$deadline);$written=@fwrite($socket,substr($data,$offset));if($written===false||$written===0){throw new RuntimeException('smtp_connection_lost');}$offset+=$written;}
    }
    private static function reply($socket,array $codes,float $deadline): void
    {
        $length=0;$code=null;
        for($i=0;$i<50;$i++){
            self::timeout($socket,$deadline);$line=@fgets($socket,1024);$meta=stream_get_meta_data($socket);
            if($line===false||$meta['timed_out']){throw new RuntimeException('smtp_connection_lost');}
            $length+=strlen($line);
            if($length>32000||!str_ends_with($line,"\r\n")||!preg_match('/^([2-5][0-9]{2})([ -])/',$line,$match)){throw new RuntimeException('smtp_protocol_invalid');}
            if($code!==null&&$code!==(int)$match[1]){throw new RuntimeException('smtp_protocol_invalid');}$code=(int)$match[1];
            if($match[2]===' '){if(!in_array($code,$codes,true)){throw new RuntimeException('smtp_rejected_'.$code);}return;}
        }
        throw new RuntimeException('smtp_protocol_limit');
    }
}
