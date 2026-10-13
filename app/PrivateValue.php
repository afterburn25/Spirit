<?php
declare(strict_types=1);
namespace Spirit;
use RuntimeException;

/** Authenticated encryption for service secrets and queued message bodies. */
final class PrivateValue
{
    private static function key(?string $key): string
    {
        if($key!==null){if(strlen($key)!==32){throw new RuntimeException('Invalid encryption key length.');}return $key;}
        $secret=Paths::config()['secret']??'';
        if(!preg_match('/^[a-f0-9]{64}$/D',$secret)){throw new RuntimeException('Private installation key unavailable.');}
        return hash_hkdf('sha256',hex2bin($secret),32,'Spiritual Gorillaz service encryption v1');
    }
    public static function seal(string $plain,?string $key=null): string
    {
        $nonce=random_bytes(12);$tag='';$encrypted=openssl_encrypt($plain,'aes-256-gcm',self::key($key),OPENSSL_RAW_DATA,$nonce,$tag,'spirit-v1',16);
        if($encrypted===false){throw new RuntimeException('Private encryption failed.');}
        return 'v1:'.base64_encode($nonce.$tag.$encrypted);
    }
    public static function open(string $sealed,?string $key=null): string
    {
        if(!str_starts_with($sealed,'v1:')){throw new RuntimeException('Invalid encrypted value format.');}
        $data=base64_decode(substr($sealed,3),true);
        if($data===false||strlen($data)<28){throw new RuntimeException('Invalid encrypted value.');}
        $plain=openssl_decrypt(substr($data,28),'aes-256-gcm',self::key($key),OPENSSL_RAW_DATA,substr($data,0,12),substr($data,12,16),'spirit-v1');
        if($plain===false){throw new RuntimeException('Private value authentication failed.');}return $plain;
    }
}
