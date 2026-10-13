<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;
use Throwable;

final class SmtpMailer
{
    /** Strict TLS-only authenticated SMTP; never log protocol or credentials. */
    public static function send(array $message,array $options): void
    {
        $to=Security::email($message['recipient']);$from=Security::email($options['from_email']);
        $host=$options['smtp_host'];$port=(int)$options['smtp_port'];
        if(!preg_match('/^[A-Za-z0-9][A-Za-z0-9.-]{0,252}$/D',$host)||$port<1||$port>65535||!in_array($options['smtp_security'],['tls','starttls'],true)){throw new RuntimeException('smtp_configuration_invalid');}
        $context=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'peer_name'=>$host,'allow_self_signed'=>false,'SNI_enabled'=>true,'crypto_method'=>STREAM_CRYPTO_METHOD_TLS_CLIENT]]);
        $transport=$options['smtp_security']==='tls'?'tls':'tcp';$socket=@stream_socket_client($transport.'://'.$host.':'.$port,$errno,$error,10,STREAM_CLIENT_CONNECT,$context);
        if(!$socket){throw new RuntimeException('smtp_connection_failed');}
        stream_set_timeout($socket,10);$committed=false;$acknowledged=false;
        try{
            self::reply($socket,[220]);self::command($socket,'EHLO spiritualgorillaz.local',[250]);
            if($options['smtp_security']==='starttls'){
                self::command($socket,'STARTTLS',[220]);
                if(@stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)!==true){throw new RuntimeException('smtp_tls_verification_failed');}
                self::command($socket,'EHLO spiritualgorillaz.local',[250]);
            }
            self::command($socket,'AUTH LOGIN',[334]);self::command($socket,base64_encode($options['smtp_user']),[334]);self::command($socket,base64_encode(PrivateValue::open($options['smtp_password'])),[235]);
            self::command($socket,'MAIL FROM:<'.$from.'>',[250]);self::command($socket,'RCPT TO:<'.$to.'>',[250,251]);self::command($socket,'DATA',[354]);
            $mime=self::mime($message,$options);$mime=str_replace("\r\n.","\r\n..",$mime);
            self::write($socket,$mime."\r\n");$committed=true;self::write($socket,".\r\n");self::reply($socket,[250]);$acknowledged=true;
            try{self::command($socket,'QUIT',[221]);}catch(Throwable $e){}
        }catch(Throwable $e){
            if($committed&&!$acknowledged){throw new DeliveryUncertain('smtp_delivery_uncertain');}throw $e;
        }finally{fclose($socket);}
    }
    public static function mime(array $message,array $options): string
    {
        $to=Security::email($message['recipient']);$from=Security::email($options['from_email']);
        $subject=Input::text($message,'subject',200,true);$fromName=Input::text($options,'from_name',120,true);
        $boundary='spirit-'.bin2hex(random_bytes(16));$html=(string)$message['html'];$text=html_entity_decode(strip_tags(str_replace(['<br>','<br/>','<br />','</p>','</div>'],"\n",$html)),ENT_QUOTES|ENT_HTML5,'UTF-8');
        $id=hash('sha256',(string)($message['id']??'').':'.(string)($message['dedupe_key']??$subject));
        $headers=['Date: '.gmdate('D, d M Y H:i:s').' +0000','Message-ID: <'.$id.'@spiritualgorillaz.local>','From: =?UTF-8?B?'.base64_encode($fromName).'?= <'.$from.'>','To: <'.$to.'>','Subject: =?UTF-8?B?'.base64_encode($subject).'?=','MIME-Version: 1.0','Content-Type: multipart/alternative; boundary="'.$boundary.'"'];
        $body=implode("\r\n",$headers)."\r\n\r\n";
        foreach(['plain'=>$text,'html'=>$html] as $type=>$part){$body.='--'.$boundary."\r\nContent-Type: text/".$type."; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($part),76,"\r\n");}
        return $body.'--'.$boundary."--\r\n";
    }
    private static function command($socket,string $command,array $codes): void {self::write($socket,$command."\r\n");self::reply($socket,$codes);}
    private static function write($socket,string $data): void
    {
        $length=strlen($data);$offset=0;while($offset<$length){$written=@fwrite($socket,substr($data,$offset));if($written===false||$written===0){throw new RuntimeException('smtp_connection_lost');}$offset+=$written;}
    }
    private static function reply($socket,array $codes): void
    {
        $length=0;$code=null;
        for($i=0;$i<50;$i++){
            $line=@fgets($socket,1024);$meta=stream_get_meta_data($socket);
            if($line===false||$meta['timed_out']){throw new RuntimeException('smtp_connection_lost');}
            $length+=strlen($line);if($length>32000||!preg_match('/^(\d{3})([ -])/',$line,$match)){throw new RuntimeException('smtp_protocol_invalid');}
            if($code!==null&&$code!==(int)$match[1]){throw new RuntimeException('smtp_protocol_invalid');}$code=(int)$match[1];
            if($match[2]===' '){if(!in_array($code,$codes,true)){throw new RuntimeException('smtp_rejected_'.$code);}return;}
        }
        throw new RuntimeException('smtp_protocol_limit');
    }
}
