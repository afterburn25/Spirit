<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;
use Throwable;

/** Additional passive checks consolidated from the separately delivered dev10 preview. */
final class LaunchExtras
{
    public const IDS=['private_config','upload_headroom','public_contact','accounts','sandbox_keys','payment_mode','catalog_count','mail_pending','payment_pending','mail_worker','payment_worker','external_host','external_mailbox','external_merchant','external_recovery','external_artwork','external_quality'];

    public static function headroom(array $limits): array
    {
        $upload=$limits['upload_max_bytes']??null;$post=$limits['post_max_bytes']??null;
        if(!is_int($upload)||$upload<0||!is_int($post)||$post<0){return ['unavailable','Upload/request limits could not be interpreted. Do not assume unlimited uploads.'];}
        if($upload===0||($post>0&&$post<=$upload)){return ['attention','The file limit is zero, or the whole-request limit is not larger than the file limit. Leave room for the entire form, not just the file.'];}
        return ['review',$post===0?'PHP post_max_size is zero: this directive does not limit multipart POST size. Web-server, application, memory and file-upload limits still apply.':'The configured whole-request limit exceeds the file limit. Upload framing, other fields and server limits still need real-host review.'];
    }
    public static function snapshot(bool $schema): array
    {
        $rows=[];
        $add=static function(string $id,string $group,string $title,string $path,callable $read,bool $needsSchema=true)use(&$rows,$schema):void{
            if($needsSchema&&!$schema){$state='unavailable';$detail='Not checked until the current schema is confirmed.';}
            else{try{[$state,$detail]=$read();}catch(Throwable $error){$state='unavailable';$detail='This observation could not be read. No success or empty backlog has been assumed.';}}
            $rows[]=compact('id','group','state','title','detail','path');
        };
        $add('private_config','installation','Private configuration file','admin/installation.php',static function():array{
            $file=Paths::runtime().'/config.php';clearstatcache(true,$file);
            $mode=@fileperms($file);$ok=!is_link($file)&&is_file($file)&&is_readable($file)&&$mode!==false&&($mode&0077)===0;
            return [$ok?'observed':'attention',$ok?'Configuration is readable with restrictive permission flags. No secret bytes or filesystem paths are included in this report.':'Configuration file flags need review. Preserve the original file and encryption secret; never reinstall to fix permissions.'];
        },false);
        $add('upload_headroom','hosting','Whole-request upload headroom','admin/media/',static fn()=>self::headroom(HostingLimits::current()),false);
        $add('public_contact','services','Public contact address','admin/communication/',static function():array{
            $value=Communications::options()['public_email'];if(!is_string($value)){throw new RuntimeException('Invalid contact setting.');}
            return [$value!==''?'review':'attention',$value!==''?'A public contact address is configured. Its value is omitted and its delivery is not verified.':'No public contact address is configured. The saved booking form is a separate contact option.'];
        });
        $add('accounts','services','Customer registration','admin/shop/settings.php',static function():array{
            $enabled=Catalog::options()['accounts_enabled'];if(!is_bool($enabled)){throw new RuntimeException('Invalid account setting.');}
            return [$enabled?'review':'observed',$enabled?'Customer registration is enabled. Store availability and a working receiving-mailbox journey must also be checked.':'Customer registration is disabled. This is a safe dormant state and this screen does not enable it.'];
        });
        $add('sandbox_keys','services','Sandbox credential fields','admin/orders/payments.php',static function():array{
            $store=Catalog::options()['enabled'];$payments=PaymentSettings::all()['enabled'];$o=PaymentSettings::environment('sandbox');$complete=true;
            foreach(['login_id','transaction_key','signature_key','client_key'] as $key){if(!is_string($o[$key])){throw new RuntimeException('Invalid payment setting.');}if($o[$key]===''){$complete=false;}}
            if(!$store&&!$payments&&!$complete){return ['observed','Commerce is dormant. Sandbox credentials are not required until the storefront/payment feature is activated.'];}
            return [$complete?'review':'attention',$complete?'All four sandbox fields are populated. Secrets were not decrypted; actual tokenization, transactions and notifications remain unverified.':'One or more sandbox credential fields are missing. Do not enable production to bypass genuine sandbox acceptance.'];
        });
        $add('payment_mode','services','Production payment warning','admin/orders/payments.php',static function():array{
            $store=Catalog::options()['enabled'];$o=PaymentSettings::all();$mode=PaymentSettings::mode($o['environment']);if(!is_bool($o['enabled'])){throw new RuntimeException('Invalid payment enablement.');}
            if(!$store&&!$o['enabled']){return ['observed','Commerce is dormant and payment enablement is off. No provider operation was attempted.'];}
            return [$mode==='production'&&$o['enabled']?'attention':'review',$mode==='production'&&$o['enabled']?'Production is selected and enabled: live-charge risk. This is not owner launch approval and this report changes no settings.':ucfirst($mode).' is selected and payment enablement is '.($o['enabled']?'on':'off').'. No provider operation was attempted.'];
        });
        $add('catalog_count','content','Published merchandise','admin/shop/',static function():array{
            $sql='SELECT COUNT(*) FROM '.Database::table('products').' p WHERE p.published_json IS NOT NULL AND '
                ."(CAST(JSON_UNQUOTE(JSON_EXTRACT(p.published_json,'$.category_id')) AS UNSIGNED)=0 OR EXISTS (SELECT 1 FROM "
                .Database::table('shop_categories')." c WHERE c.id=CAST(JSON_UNQUOTE(JSON_EXTRACT(p.published_json,'$.category_id')) AS UNSIGNED) AND c.active=1))";
            $count=(int)Database::connect()->query($sql)->fetchColumn();$enabled=Catalog::options()['enabled'];
            if(!$enabled&&$count===0){return ['observed','The storefront is closed with no published merchandise. This is a safe dormant launch state.'];}
            return ['review',$count.' published product(s) with a visible category. Drafts are excluded; store enablement, variants, stock, rights and fulfillment require separate review.'];
        });
        foreach(['mail_pending'=>['Unfinished email jobs','mail_queue',"status IN ('queued','retry','failed','uncertain','sending')",'admin/mail/','An empty queue does not prove inbox delivery.'], 'payment_pending'=>['Unresolved payment operations','payment_operations',"state IN ('processing','uncertain')",'admin/orders/','Do not blindly repeat a charge or release its stock.']] as $id=>[$title,$table,$condition,$path,$note]){
            $add($id,'operations',$title,$path,static function()use($table,$condition,$note):array{
                $count=(int)Database::connect()->query('SELECT COUNT(*) FROM '.Database::table($table).' WHERE '.$condition)->fetchColumn();
                return [$count>0?'attention':'observed',$count.' unresolved record(s). '.$note.' This report never processes the queue.'];
            });
        }
        $add('mail_worker','operations','Mail worker heartbeat','admin/mail/',static function():array{
            $heartbeat=TaskHeartbeat::read('mail');
            if(!$heartbeat){return ['review','No successful mail-worker heartbeat has been recorded yet. A configured cron entry alone is not execution evidence.'];}
            $recent=$heartbeat['age_seconds']<=86400;
            return [$recent?'observed':'attention',$recent?'The mail worker completed successfully within the last 24 hours. Inbox delivery remains a separate acceptance check.':'The last successful mail-worker heartbeat is older than 24 hours. Review the scheduled task and mail queue.'];
        });
        $add('payment_worker','operations','Payment maintenance heartbeat','admin/orders/',static function():array{
            $store=Catalog::options()['enabled'];$payments=PaymentSettings::all()['enabled'];
            if(!$store&&!$payments){return ['observed','Commerce is dormant, so a payment-maintenance heartbeat is not required for launch.'];}
            $heartbeat=TaskHeartbeat::read('payments');
            if(!$heartbeat){return ['review','Commerce is active but no successful payment-maintenance heartbeat has been recorded yet. Verify the scheduled task before relying on unattended reconciliation.'];}
            $recent=$heartbeat['age_seconds']<=86400;
            return [$recent?'observed':'attention',$recent?'Payment maintenance completed successfully within the last 24 hours. This does not prove a provider transaction or notification.':'The last successful payment-maintenance heartbeat is older than 24 hours. Review scheduled payment maintenance and unresolved operations.'];
        });
        foreach([
            'host'=>['A2 staging acceptance','Review domain aliases, PHP settings, private storage, HTTPS, installer cleanup, uploads and actual scheduled tasks.','admin/installation.php'],
            'mailbox'=>['Receiving-mailbox acceptance','Receive real booking, customer, staff and newsletter messages. Exercise confirmation, expiry and unsubscribe. SMTP acknowledgement is not inbox evidence.','admin/communication/'],
            'merchant'=>['Genuine merchant sandbox acceptance','Verify real tokenization, approvals/declines, signed notifications, reporting and full reversal workflows. Never infer this from simulated tests.','admin/orders/payments.php'],
            'recovery'=>['Backup and recovery drill','Keep separate offline keys and independent backups. Exercise a supported same-instance restore and same-schema upgrade recovery on staging.','admin/backups/'],
            'artwork'=>['Approved production artwork and content','Approve master artwork, genuine band information, media usage rights, shows and real merchandise. Reference crops are not final masters.','admin/content/?kind=page'],
            'quality'=>['Populated-site review and owner approval','Review real pages on phones, keyboard/zoom/accessibility, security, performance, policies and cutover. No final release acceptance is inferred.','admin/roadmap.php'],
        ] as $id=>[$title,$detail,$path]){$add('external_'.$id,'acceptance',$title,$path,static fn()=>['review',$detail],false);}
        return $rows;
    }
}
