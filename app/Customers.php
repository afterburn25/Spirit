<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

/** Customer identities never share the staff users table or role fields. */
final class Customers
{
    private const DUMMY_HASH='$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    public static function current(): ?array
    {
        if(!isset($_SESSION['customer_id'],$_SESSION['customer_version'])){return null;}
        $q=Database::connect()->prepare('SELECT id,name,email,status,session_version FROM '.Database::table('customers').' WHERE id=?');$q->execute([(int)$_SESSION['customer_id']]);$c=$q->fetch();
        if(!$c||$c['status']!=='active'||(int)$c['session_version']!==(int)$_SESSION['customer_version']){unset($_SESSION['customer_id'],$_SESSION['customer_version']);return null;}return $c;
    }
    public static function requireCustomer(): array {$c=self::current();if(!$c){go('account/login.php');}return $c;}
    public static function login(string $email,string $password): bool
    {
        if(strlen($email)>190||strlen($password)>72||str_contains($password,"\0")){return false;}
        $email=strtolower(trim($email));
        try{RequestLimit::consume('customer-login-ip',40);RequestLimit::consume('customer-login-email',8,900,$email);}catch(RuntimeException $e){return false;}
        $db=Database::connect();$q=$db->prepare('SELECT * FROM '.Database::table('customers').' WHERE email=?');$q->execute([$email]);$c=$q->fetch();
        $valid=password_verify($password,$c['password_hash']??self::DUMMY_HASH);
        if(!$valid||!$c||$c['status']!=='active'){return false;}
        session_regenerate_id(true);$_SESSION=['customer_id'=>(int)$c['id'],'customer_version'=>(int)$c['session_version'],'last_activity'=>time(),'csrf'=>bin2hex(random_bytes(32))];
        if(password_needs_rehash($c['password_hash'],PASSWORD_DEFAULT)){$q=$db->prepare('UPDATE '.Database::table('customers').' SET password_hash=? WHERE id=? AND password_hash=?');$q->execute([password_hash($password,PASSWORD_DEFAULT),$c['id'],$c['password_hash']]);}
        return true;
    }
    public static function logout(): void {Cart::forget();Auth::logout();}
    public static function requestToken(string $purpose,array $input): void
    {
        if(!in_array($purpose,['activate','reset'],true)){throw new InvalidArgumentException('Invalid account action.');}
        if(!Communications::ready()||!Catalog::options()['accounts_enabled']){throw new RuntimeException('Email-based account services are not available yet.');}
        $email=Security::email(Input::text($input,'email',190,true));
        $name=$purpose==='activate'?Input::text($input,'name',120,true):'';
        if($purpose==='activate'&&($input['account_consent']??'')!=='1'){throw new InvalidArgumentException('Confirm permission to create your customer account.');}
        RequestLimit::consume('customer-'.$purpose.'-ip',12);RequestLimit::consume('customer-'.$purpose.'-email',4,3600,$email);
        // No fast unknown-account response. Timing is padded, not claimed constant-time.
        $start=microtime(true);$db=Database::connect();$db->beginTransaction();
        try{
            if($purpose==='activate'){
                $q=$db->prepare('INSERT INTO '.Database::table('customers')." (name,email,consent_version,created_at) VALUES (?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");$q->execute([$name,$email,'account-contact-2026-10-v1']);
            }
            $q=$db->prepare('SELECT id,status FROM '.Database::table('customers').' WHERE email=? FOR UPDATE');$q->execute([$email]);$c=$q->fetch();
            if(!$c||$c['status']!==($purpose==='activate'?'pending':'active')){$db->commit();return;}
            $q=$db->prepare('SELECT requested_at FROM '.Database::table('customer_tokens').' WHERE customer_id=? AND purpose=?');$q->execute([$c['id'],$purpose]);$last=$q->fetchColumn();
            if($last&&strtotime($last.' UTC')>time()-300){$db->commit();return;}
            $token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);$expires=gmdate('Y-m-d H:i:s',time()+($purpose==='activate'?172800:1800));
            $q=$db->prepare('INSERT INTO '.Database::table('customer_tokens').' (customer_id,purpose,token_hash,expires_at,requested_at) VALUES (?,?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE token_hash=VALUES(token_hash),expires_at=VALUES(expires_at),requested_at=UTC_TIMESTAMP(),used_at=NULL');$q->execute([$c['id'],$purpose,$hash,$expires]);
            $label=$purpose==='activate'?'Complete your account':'Reset your password';$text=$purpose==='activate'?'Verify your email and choose your own password to finish creating your customer account. This does not subscribe you to marketing.':'A password reset was requested. Ignore this email if it was not you. Your existing password is unchanged until you complete the reset.';
            MailQueue::enqueue($email,'Spiritual Gorillaz — '.$label,Communications::message($label,$text,$label,Communications::absolute('account/complete.php?purpose='.$purpose.'&token='.$token)),'customer:'.$purpose.':'.$hash,$db);
            $db->commit();
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
        finally{$delay=250000-(int)((microtime(true)-$start)*1000000);if($delay>0){usleep($delay);}}
        // Scheduled processing is separate; no per-account SMTP timing side-channel.
    }
    public static function token(string $purpose,string $token,?PDO $db=null,bool $lock=false): ?array
    {
        if(!in_array($purpose,['activate','reset'],true)||!preg_match('/^[a-f0-9]{64}$/D',$token)){return null;}
        $db??=Database::connect();$q=$db->prepare('SELECT t.*,c.name,c.email,c.status FROM '.Database::table('customer_tokens').' t JOIN '.Database::table('customers').' c ON c.id=t.customer_id WHERE t.token_hash=? AND t.purpose=? AND t.used_at IS NULL AND t.expires_at>UTC_TIMESTAMP()'.($lock?' FOR UPDATE':''));$q->execute([hash('sha256',$token),$purpose]);$row=$q->fetch();
        return $row&&$row['status']===($purpose==='activate'?'pending':'active')?$row:null;
    }
    public static function finish(string $purpose,string $token,array $input): void
    {
        $password=$input['password']??null;$confirm=$input['password_confirm']??null;
        if(!is_string($password)||!is_string($confirm)||$password!==$confirm){throw new InvalidArgumentException('Passwords must match.');}$hash=Security::password($password);
        $db=Database::connect();$db->beginTransaction();
        try{
            $row=self::token($purpose,$token,$db,true);if(!$row){throw new RuntimeException('This account link is expired or already used. Request a new email.');}
            $q=$db->prepare('UPDATE '.Database::table('customers')." SET password_hash=?,status='active',session_version=session_version+1,verified_at=COALESCE(verified_at,UTC_TIMESTAMP()) WHERE id=?");$q->execute([$hash,$row['customer_id']]);
            $q=$db->prepare('UPDATE '.Database::table('customer_tokens').' SET used_at=UTC_TIMESTAMP() WHERE customer_id=? AND used_at IS NULL');$q->execute([$row['customer_id']]);
            $db->commit();unset($_SESSION['customer_id'],$_SESSION['customer_version']);
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function changePassword(int $id,array $input): void
    {
        $old=$input['current_password']??null;$new=$input['password']??null;
        if(!is_string($old)||strlen($old)>72||str_contains($old,"\0")||!is_string($new)||$new!==($input['password_confirm']??null)){throw new InvalidArgumentException('Check the password fields.');}
        RequestLimit::consume('customer-password-change',6,900,(string)$id);$hash=Security::password($new);$db=Database::connect();$db->beginTransaction();
        try{
            $q=$db->prepare('SELECT password_hash,session_version FROM '.Database::table('customers')." WHERE id=? AND status='active' FOR UPDATE");$q->execute([$id]);$row=$q->fetch();
            if(!$row||!password_verify($old,$row['password_hash'])){throw new InvalidArgumentException('The current password was not accepted.');}
            $q=$db->prepare('UPDATE '.Database::table('customers').' SET password_hash=?,session_version=session_version+1 WHERE id=?');$q->execute([$hash,$id]);
            $q=$db->prepare('UPDATE '.Database::table('customer_tokens').' SET used_at=UTC_TIMESTAMP() WHERE customer_id=? AND used_at IS NULL');$q->execute([$id]);$db->commit();
            session_regenerate_id(true);$_SESSION['customer_version']=(int)$row['session_version']+1;$_SESSION['csrf']=bin2hex(random_bytes(32));
        }catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}throw $e;}
    }
    public static function mailApplicable(PDO $db,array $message): bool
    {
        if(!preg_match('/^customer:(activate|reset):([a-f0-9]{64})$/D',$message['dedupe_key'],$m)){return false;}
        $q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('customer_tokens').' t JOIN '.Database::table('customers').' c ON c.id=t.customer_id WHERE t.token_hash=? AND t.purpose=? AND t.used_at IS NULL AND t.expires_at>UTC_TIMESTAMP() AND c.email=? AND c.status=?');$q->execute([$m[2],$m[1],$message['recipient'],$m[1]==='activate'?'pending':'active']);return (int)$q->fetchColumn()===1;
    }
}
