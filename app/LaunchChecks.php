<?php
declare(strict_types=1);
namespace Spirit;
use Throwable;

/** Owner-facing observations, not automated production acceptance. No network calls. */
final class LaunchChecks
{
    public const STATES = ['observed'=>'Observed','attention'=>'Needs attention','review'=>'Review required','unavailable'=>'Not checked'];
    public const GROUPS = ['hosting'=>'PHP and hosting','installation'=>'Installation safeguards','services'=>'Service configuration','content'=>'Published content','operations'=>'Operational follow-up','acceptance'=>'External acceptance'];
    private array $rows = [];

    private function add(string $id, string $group, string $state, string $title, string $detail, string $path): void
    {
        $this->rows[] = compact('id','group','state','title','detail','path');
    }

    /** Catch per-check failures without exposing driver/configuration exceptions. */
    private function observe(string $id, string $group, string $title, string $path, callable $read): void
    {
        try { [$state,$detail] = $read(); }
        catch (Throwable $error) { $state = 'unavailable'; $detail = 'This check could not be read. Review the linked administration screen. No settings were changed.'; }
        $this->add($id,$group,$state,$title,$detail,$path);
    }

    public static function snapshot(): array
    {
        $self = new self();
        $extensions = [];
        foreach (['pdo_mysql','openssl','fileinfo','gd','curl','zip'] as $extension) { $extensions[$extension] = extension_loaded($extension); }
        $runtime = ['php_version'=>PHP_VERSION,'https'=>Security::isHttps(),'extensions'=>$extensions,'limits'=>HostingLimits::current()];
        $self->add('php','hosting',PHP_VERSION_ID >= 80200 ? 'observed':'attention','PHP version',PHP_VERSION . ' is serving this page. The site requires PHP 8.2 or newer.','admin/installation.php');
        foreach (['pdo_mysql'=>'Database driver','openssl'=>'Encrypted connections','fileinfo'=>'File validation','gd'=>'Image processing','curl'=>'Payment connections','zip'=>'Browser ZIP upgrades'] as $key=>$purpose) {
            $self->add('extension_'.$key,'hosting',$extensions[$key] ? 'observed':'attention','PHP '.$key,
                $extensions[$key] ? $purpose . ': extension loaded. This is not a functional provider test.' : $purpose . ': extension missing. Enable it for this domain’s PHP version through cPanel or A2 support. cURL/ZIP do not block manual installation.', 'admin/installation.php');
        }
        $self->add('https','hosting',$runtime['https'] ? 'observed':'attention','HTTPS for this request',
            $runtime['https'] ? 'Native HTTPS is active. Domain aliases, proxy configuration and public certificates still need staging review.' : 'This request is not native HTTPS. Local development is not production TLS acceptance.','admin/installation.php');
        $limits = $runtime['limits'];
        $uploadState = !$limits['file_uploads'] || $limits['upload_max_bytes'] === 0 ? 'attention' : 'review';
        $self->add('uploads','hosting',$uploadState,'Upload limits',HostingLimits::uploadHint(),'admin/media/');
        $self->add('memory','hosting','review','PHP memory ceiling',HostingLimits::label($limits['memory_limit_bytes']) . '. Available processing memory and decoded image size still determine which images can be handled safely.','admin/media/');
        $self->observe('install_lock','installation','Installation lock','admin/installation.php',static fn()=>[
            Paths::installed() ? 'observed':'attention', 'Installed configuration or the independent completion marker prevents fresh setup. Keep both protected; do not reinstall to apply updates.'
        ]);
        $self->observe('installer_removed','installation','Installer-folder removal','admin/installation.php',static function(): array {
            $path=Paths::root().'/install';$removed=!file_exists($path)&&!is_link($path);
            return [$removed?'observed':'attention',$removed?'The installer directory is physically absent.':'The installer directory still exists. A logical lock is not physical removal; use Installation health to inspect or retry safe cleanup.'];
        });
        $self->observe('private_storage','installation','Private runtime permissions','admin/installation.php',static function(): array {
            $root=Paths::runtime();$ok=true;
            foreach (['','/sessions','/logs','/media','/backups','/cache'] as $suffix) {
                $path=$root.$suffix;clearstatcache(true,$path);
                if(is_link($path)||!is_dir($path)||realpath($path)!==$path||!is_writable($path)||((int)@fileperms($path)&0077)!==0){$ok=false;}
            }
            return [$ok?'review':'attention',$ok?'Private directory permission flags look compatible. This read-only check does not perform writes; use Installation health for an actual I/O probe and review every A2 public mapping.':'A required private directory has unsafe or unwritable flags. Review Installation health and hosting permissions; never make private storage public.'];
        });
        $schema=false;
        try { $schema=Migrations::ready();$schemaState=$schema?'observed':'attention'; }
        catch (Throwable $error) { $schemaState='unavailable'; }
        $self->add('schema','installation',$schemaState,'Database migration ledger',
            $schema?'Committed migration checksums match the installed ledger. This does not verify every stored record or replace a backup.':'The current schema could not be confirmed. Review Database updates; service/content checks below have not been run.','admin/updates.php');
        $services=[
            ['smtp','Transactional email','admin/communication/',static fn()=>[Communications::ready()?'review':'attention',Communications::ready()?'SMTP is configured and enabled. Receiving-mailbox delivery has not been verified by these checks.':'SMTP is disabled or required settings are missing. Verify a real owner test before relying on booking notices, account emails or receipts.']],
            ['newsletter','Mailing-list signup','admin/communication/',static fn()=>['review',Communications::newsletterReady()?'Signup is enabled. Complete a real confirmation and unsubscribe through the receiving mailbox.':'Signup is not currently available. This can be intentional; enable it only after email delivery works.']],
            ['store','Merchandise storefront','admin/shop/settings.php',static fn()=>['review',Catalog::options()['enabled']?'The storefront is enabled. Verify actual approved products, stock and fulfillment before launch.':'The storefront is closed. Keep it closed until real products and checkout acceptance are ready.']],
            ['payments','Checkout configuration','admin/orders/payments.php',static function(): array {
                $o=PaymentSettings::all();$mode=PaymentSettings::mode($o['environment']);$available=PaymentSettings::available();
                return [$o['enabled']&&!$available?'attention':'review',ucfirst($mode).' is selected. '.($available?'Local checkout prerequisites are present. No tokenization, transaction, refund or provider notification was tested by this screen.':'Checkout is unavailable or disabled. Keep payments off until all prerequisites and genuine sandbox checks are complete.')];
            }],
            ['policy','Delivery and store policy','admin/orders/policy.php',static fn()=>[DeliveryPolicy::options()['configured']?'review':'attention',DeliveryPolicy::options()['configured']?'Shipping/tax/policy settings are present. The owner must review destinations, actual rates, terms and operational scope.':'Delivery and store policy are not configured. Do not enable checkout before reviewing them.']],
            ['indexing','Search-engine indexing','admin/discovery.php',static fn()=>['review',Metadata::options()['indexing']?'Indexing is allowed. For staging, review whether indexing should be disabled; noindex is not access control.':'Indexing is disabled. Review this setting deliberately before public launch; it does not protect private staging.']],
        ];
        foreach($services as [$id,$title,$path,$read]) {
            if($schema){$self->observe($id,'services',$title,$path,$read);}
            else{$self->add($id,'services','unavailable',$title,'Not checked until the current schema is confirmed.',$path);}
        }
        foreach(['page'=>'Pages','release'=>'Music releases','track'=>'Audio tracks','event'=>'Shows','member'=>'Band members','gallery'=>'Gallery entries','video'=>'Videos','press'=>'Press downloads'] as $kind=>$label) {
            $read=static function()use($kind):array {
                $q=Database::connect()->prepare('SELECT COUNT(*) FROM '.Database::table('content').' WHERE kind=? AND published_json IS NOT NULL AND deleted_at IS NULL');$q->execute([$kind]);$count=(int)$q->fetchColumn();
                return ['review',$count.' published record(s). '.($count===0?'An empty section may be intentional; approve genuine content rather than adding invented examples.':'Publication alone does not establish correct content, usage permission or final artwork quality.')];
            };
            if($schema){$self->observe('content_'.$kind,'content',$label,'admin/content/?kind='.$kind,$read);}
            else{$self->add('content_'.$kind,'content','unavailable',$label,'Not checked until the current schema is confirmed.','admin/updates.php');}
        }
        $self->rows=array_merge($self->rows,LaunchExtras::snapshot($schema));
        $counts=array_fill_keys(array_keys(self::STATES),0);foreach($self->rows as $row){$counts[$row['state']]++;}
        return ['version'=>Paths::version(),'checked_at'=>gmdate('c'),'runtime'=>$runtime,'counts'=>$counts,'checks'=>$self->rows];
    }

    /** Positive allowlist: never serialize snapshots, exceptions or configuration wholesale. */
    public static function supportReport(array $snapshot): array
    {
        $extensions=[];foreach(['pdo_mysql','openssl','fileinfo','gd','curl','zip'] as $name){$extensions[$name]=($snapshot['runtime']['extensions'][$name]??false)===true;}
        $limits=['file_uploads'=>($snapshot['runtime']['limits']['file_uploads']??false)===true];
        foreach(['upload_max_bytes','post_max_bytes','memory_limit_bytes'] as $key){$v=$snapshot['runtime']['limits'][$key]??null;$limits[$key]=is_int($v)&&$v>=-1?$v:null;}
        $allowed=['php','extension_pdo_mysql','extension_openssl','extension_fileinfo','extension_gd','extension_curl','extension_zip','https','uploads','memory','install_lock','installer_removed','private_storage','schema','smtp','newsletter','store','payments','policy','indexing','content_page','content_release','content_track','content_event','content_member','content_gallery','content_video','content_press'];
        $allowed=array_merge($allowed,LaunchExtras::IDS);
        $statuses=[];foreach($snapshot['checks']??[] as $row){
            if(is_array($row)&&in_array($row['id']??null,$allowed,true)&&is_string($row['state']??null)&&isset(self::STATES[$row['state']])){$statuses[$row['id']]=$row['state'];}
        }
        return ['format'=>'spirit-support-v1','generated_at'=>gmdate('c'),'application_version'=>Paths::version(),
            'php_version'=>PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION,
            'native_https'=>($snapshot['runtime']['https']??false)===true,'extensions'=>$extensions,'limits'=>$limits,'check_states'=>$statuses,
            'scope'=>'Local configuration observations only. No provider, mailbox, hosting-account or launch acceptance is certified.',
            'privacy'=>'No hostnames, filesystem paths, credentials, logs, database names, personal details or content text are included. Review before sharing.'];
    }
}
