<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use Throwable;

final class Communications
{
    private static bool $scheduled=false;
    public static function options(): array
    {
        return array_merge(['enabled'=>false,'newsletter_enabled'=>false,'smtp_host'=>'','smtp_port'=>587,'smtp_security'=>'starttls','smtp_user'=>'','smtp_password'=>'','from_email'=>'','from_name'=>'Spiritual Gorillaz','booking_email'=>'','public_email'=>''],ConfigStore::read('communication'));
    }
    public static function ready(): bool
    {
        $o=self::options();return $o['enabled']&&$o['smtp_host']!==''&&$o['smtp_user']!==''&&$o['smtp_password']!==''&&$o['from_email']!=='';
    }
    public static function newsletterReady(): bool {return self::ready()&&self::options()['newsletter_enabled'];}
    public static function save(array $input,int $actor): void
    {
        $old=self::options();$o=['enabled'=>($input['enabled']??'')==='1','newsletter_enabled'=>($input['newsletter_enabled']??'')==='1'];
        $o['smtp_host']=Input::text($input,'smtp_host',253);
        if($o['smtp_host']!==''&&!preg_match('/^(?=.{1,253}$)[A-Za-z0-9](?:[A-Za-z0-9.-]*[A-Za-z0-9])?$/D',$o['smtp_host'])){throw new InvalidArgumentException('Enter the SMTP hostname without a scheme, port or path.');}
        $o['smtp_port']=Input::integer($input,'smtp_port',1,65535,587);
        $o['smtp_security']=Input::choice($input,'smtp_security',['starttls','tls'],'starttls');
        $o['smtp_user']=Input::text($input,'smtp_user',254);
        $o['from_name']=Input::text($input,'from_name',120,true);
        foreach(['from_email','booking_email','public_email'] as $key){$value=Input::text($input,$key,190);$o[$key]=$value!==''?Security::email($value):'';}
        $password=$input['smtp_password']??'';
        if(!is_string($password)||strlen($password)>1000||str_contains($password,"\0")){throw new InvalidArgumentException('Invalid SMTP password.');}
        $o['smtp_password']=($input['clear_password']??'')==='1'?'':($password!==''?PrivateValue::seal($password):$old['smtp_password']);
        if($o['enabled']&&($o['smtp_host']===''||$o['smtp_user']===''||$o['smtp_password']===''||$o['from_email']==='')){throw new InvalidArgumentException('SMTP host, username, password and From email are required before enabling delivery.');}
        ConfigStore::write('communication',$o,$actor);
    }
    public static function bookingAddress(): string
    {
        $o=self::options();if($o['booking_email']!==''){return $o['booking_email'];}
        $value=Database::connect()->query('SELECT email FROM '.Database::table('users')." WHERE role='owner' AND active=1 ORDER BY id LIMIT 1")->fetchColumn();return is_string($value)?$value:'';
    }
    public static function absolute(string $path): string {return rtrim(Paths::config()['site_url'],'/').'/'.ltrim($path,'/');}
    public static function scheduleDelivery(): void
    {
        if(self::$scheduled||!self::ready()){return;}self::$scheduled=true;
        register_shutdown_function(static function():void {
            if(session_status()===PHP_SESSION_ACTIVE){session_write_close();}
            if(function_exists('fastcgi_finish_request')){fastcgi_finish_request();}
            try{MailQueue::process(2,12);}catch(Throwable $e){error_log('Spirit queued delivery deferred ['.get_class($e).']');}
        });
    }
    public static function message(string $heading,string $text,?string $buttonLabel=null,?string $buttonUrl=null): string
    {
        $button=$buttonLabel&&$buttonUrl?'<p style="margin-top:28px"><a href="'.e($buttonUrl).'" style="display:inline-block;background:#dfb65e;color:#102030;padding:14px 22px;text-decoration:none;font-weight:bold">'.e($buttonLabel).'</a></p><p style="font-size:12px;overflow-wrap:anywhere">'.e($buttonUrl).'</p>':'';
        return '<!doctype html><html lang="en"><meta charset="utf-8"><body style="margin:0;background:#06101b;color:#edf0ed;font-family:Arial,sans-serif"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:32px 16px"><table role="presentation" width="600" style="max-width:100%;background:#0c1c2c;border:1px solid #35506a"><tr><td style="padding:32px"><p style="color:#dfb65e;font-weight:bold;letter-spacing:2px">SPIRITUAL GORILLAZ</p><h1 style="font-size:28px;line-height:1.2">'.e($heading).'</h1><div style="line-height:1.7">'.nl2br(e($text)).'</div>'.$button.'<p style="margin-top:32px;color:#9eb0c0;font-size:12px">Music. People. Purpose.</p></td></tr></table></td></tr></table></body></html>';
    }
}
