<?php
declare(strict_types=1);
namespace Spirit;
use Generator;
use RuntimeException;

/** Bounded authenticated record stream; no PHP/SQL or serialized objects execute. */
final class BackupStream
{
    private const MAGIC="SPIRIT-BACKUP-1\n";
    public const MAX_RECORD=2097152;
    public const MAX_BYTES=536870912;
    public const MAX_RECORDS=100000;
    private mixed $file;
    private string $key;
    private string $header;
    private string $tag='';
    private int $sequence=0;
    private bool $finished=false;
    public function __construct(string $path,string $key)
    {
        if(strlen($key)!==32||is_link($path)){throw new RuntimeException('Invalid backup destination or key.');}
        $this->file=@fopen($path,'x+b');if(!$this->file){throw new RuntimeException('Backup destination already exists or is not writable.');}
        if(!@chmod($path,0600)){fclose($this->file);throw new RuntimeException('Backup file cannot be protected.');}
        $this->key=$key;$this->header=self::MAGIC.bin2hex(random_bytes(16))."\n";self::write($this->file,$this->header);
    }
    public function record(array $value): void
    {
        if($this->finished||$this->sequence>=self::MAX_RECORDS){throw new RuntimeException('Backup record limit exceeded.');}
        $plain=json_encode($value,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
        if(strlen($plain)>self::MAX_RECORD){throw new RuntimeException('A backup record exceeds the supported size.');}
        $nonce=random_bytes(12);$tag='';
        $cipher=openssl_encrypt($plain,'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,$nonce,$tag,$this->header.':'.$this->sequence.':'.$this->tag,16);
        if($cipher===false){throw new RuntimeException('Backup encryption failed.');}
        self::write($this->file,pack('N',strlen($cipher)).$nonce.$tag.$cipher);
        if(ftell($this->file)>self::MAX_BYTES){throw new RuntimeException('Backup exceeds the supported 512 MB archive limit.');}
        $this->tag=$tag;$this->sequence++;
    }
    public function finish(): void
    {
        $this->record(['kind'=>'end','records'=>$this->sequence]);$this->finished=true;
        if(!fflush($this->file)||(function_exists('fsync')&&!fsync($this->file))){throw new RuntimeException('Backup flush failed.');}
        fclose($this->file);$this->file=null;
    }
    public function __destruct(){if(is_resource($this->file)){fclose($this->file);}}
    public static function read(string $path,string $key): Generator
    {
        if(strlen($key)!==32||is_link($path)||!is_file($path)||filesize($path)>self::MAX_BYTES){throw new RuntimeException('Invalid or oversized backup archive.');}
        $file=@fopen($path,'rb');if(!$file){throw new RuntimeException('Backup file unavailable.');}
        try{
            $header=self::exact($file,strlen(self::MAGIC)+33);
            if(!str_starts_with($header,self::MAGIC)||!preg_match('/^[a-f0-9]{32}\n$/D',substr($header,strlen(self::MAGIC)))){throw new RuntimeException('Unknown backup format.');}
            $sequence=0;$tag='';
            while($sequence<self::MAX_RECORDS){
                $length=unpack('Nlength',self::exact($file,4))['length'];
                if($length<2||$length>self::MAX_RECORD){throw new RuntimeException('Invalid backup record length.');}
                $nonce=self::exact($file,12);$next=self::exact($file,16);$cipher=self::exact($file,$length);
                $plain=openssl_decrypt($cipher,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$nonce,$next,$header.':'.$sequence.':'.$tag);
                if($plain===false){throw new RuntimeException('Backup authentication failed. The key or archive is invalid.');}
                $value=json_decode($plain,true,64,JSON_THROW_ON_ERROR);
                if(!is_array($value)||!is_string($value['kind']??null)){throw new RuntimeException('Invalid backup record.');}
                if($value['kind']==='end'){
                    if(($value['records']??null)!==$sequence||fread($file,1)!==''){throw new RuntimeException('Backup footer or trailing content is invalid.');}
                    return;
                }
                yield $value;$sequence++;$tag=$next;
            }
            throw new RuntimeException('Backup record limit exceeded.');
        }finally{fclose($file);}
    }
    private static function exact(mixed $file,int $length): string
    {
        $body='';while(strlen($body)<$length){$part=fread($file,$length-strlen($body));if($part===false||$part===''){throw new RuntimeException('The backup is truncated or unreadable.');}$body.=$part;}return $body;
    }
    private static function write(mixed $file,string $body): void
    {
        for($offset=0,$length=strlen($body);$offset<$length;){$n=fwrite($file,substr($body,$offset));if($n===false||$n===0){throw new RuntimeException('Incomplete backup write.');}$offset+=$n;}
    }
}
