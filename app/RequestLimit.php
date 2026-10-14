<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

final class RequestLimit
{
    public static function consume(string $scope,int $limit,int $seconds=900,?string $subject=null): void
    {
        $subject??=(string)($_SERVER['REMOTE_ADDR']??'unknown');
        $key=hash_hmac('sha256',$scope.':'.$subject,Paths::config()['secret']);$now=time();$db=Database::connect();
        $q=$db->prepare('INSERT INTO '.Database::table('request_limits').' (bucket,attempts,expires_at) VALUES (?,1,?) ON DUPLICATE KEY UPDATE attempts=IF(expires_at < ?,1,attempts+1),expires_at=IF(expires_at < ?,?,expires_at)');$q->execute([$key,$now+$seconds,$now,$now,$now+$seconds]);
        $q=$db->prepare('SELECT attempts FROM '.Database::table('request_limits').' WHERE bucket=?');$q->execute([$key]);$attempts=(int)$q->fetchColumn();
        // Expired counters reset above when reused. Bulk expiry belongs to the
        // owner-reviewed bounded cleanup, never a side effect of authentication.
        if($attempts>$limit){throw new RuntimeException('Too many requests. Please wait before trying again.');}
    }
}
