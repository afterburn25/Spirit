<?php
declare(strict_types=1);
namespace Spirit;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class Media
{
    public static function all(?string $kind=null): array
    {
        $q=Database::connect()->prepare('SELECT * FROM '.Database::table('media').($kind!==null?' WHERE kind=?':'').' ORDER BY id DESC LIMIT 1000');$q->execute($kind!==null?[$kind]:[]);return $q->fetchAll();
    }
    public static function get(int $id): ?array {$q=Database::connect()->prepare('SELECT * FROM '.Database::table('media').' WHERE id=?');$q->execute([$id]);return $q->fetch()?:null;}
    private static array $dimensions=[];
    public static function dimensions(array $media): array
    {
        $key=(string)$media['id'];if(isset(self::$dimensions[$key])){return self::$dimensions[$key];}
        try{$info=$media['kind']==='image'?@getimagesize(self::path($media)):false;}catch(Throwable $e){$info=false;}
        $value=$info?['width'=>(int)$info[0],'height'=>(int)$info[1]]:[];
        if(count(self::$dimensions)<128){self::$dimensions[$key]=$value;}return $value;
    }
    public static function url(int $id): string {return app_url('media/file.php?id='.$id);}
    public static function path(array $media): string
    {
        if (!preg_match('/^[a-f0-9]{32}\.(?:jpg|png|webp|mp3|ogg|wav|pdf)$/D',$media['storage_name'])) {throw new RuntimeException('Invalid media storage record.');}
        $file=Paths::runtime().'/media/'.$media['storage_name'];
        if (is_link(dirname($file)) || realpath(dirname($file)) !== dirname($file) || is_link($file) || !is_file($file)) {throw new RuntimeException('Media file unavailable.');}return $file;
    }
    public static function upload(array $file,string $alt,int $actor): int
    {
        if (($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) {throw new InvalidArgumentException('Upload did not complete. Check the file and the hosting upload/post size limits.');}
        if (!isset($file['tmp_name'],$file['name']) || !is_string($file['tmp_name']) || !is_string($file['name']) || !is_uploaded_file($file['tmp_name'])) {throw new InvalidArgumentException('No valid uploaded file was received.');}
        $size=filesize($file['tmp_name']);if ($size===false || $size<1 || $size>50*1024*1024) {throw new InvalidArgumentException('Files must be between 1 byte and 50 MB; your hosting limit may be lower.');}
        if (!class_exists(\finfo::class)) {throw new RuntimeException('Enable the PHP fileinfo extension in the hosting panel before uploading.');}
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $types=['image/jpeg'=>['image','jpg'],'image/png'=>['image','png'],'image/webp'=>['image','webp'],'audio/mpeg'=>['audio','mp3'],'audio/ogg'=>['audio','ogg'],'application/ogg'=>['audio','ogg'],'audio/x-wav'=>['audio','wav'],'audio/wav'=>['audio','wav'],'application/pdf'=>['pdf','pdf']];
        if (!isset($types[$mime])) {throw new InvalidArgumentException('Supported uploads: JPEG, PNG, WebP, MP3, OGG, WAV and PDF. SVG, scripts and archives are not accepted.');}
        [$kind,$extension]=$types[$mime];
        if ($kind==='pdf' && $size>12*1024*1024) {throw new InvalidArgumentException('PDF documents must be 12 MB or smaller.');}
        $alt=trim($alt);if (strlen($alt)>300 || str_contains($alt,"\0")) {throw new InvalidArgumentException('Alternative text must be at most 300 bytes.');}
        $original=substr(preg_replace('/[\x00-\x1f\x7f]/','',basename(str_replace('\\','/',$file['name'])))??'upload',0,180);
        $directory=Paths::runtime().'/media';$name=bin2hex(random_bytes(16)).'.'.$extension;$target=$directory.'/'.$name;
        try {
            if ($kind==='image') {
                if (!extension_loaded('gd')) {throw new RuntimeException('Enable the PHP GD extension in the hosting panel to safely process images.');}
                $info=@getimagesize($file['tmp_name']);
                if (!$info || $info[0]<1 || $info[1]<1 || $info[0]*$info[1]>20000000 || $info[0]>10000 || $info[1]>10000) {throw new InvalidArgumentException('The image is invalid or exceeds the 20-megapixel limit.');}
                if (!ImageVariants::canDecode($info[0], $info[1])) {throw new InvalidArgumentException('This image needs more processing memory than the host allows. Upload a smaller-resolution copy.');}
                $image=match($extension){'jpg'=>@imagecreatefromjpeg($file['tmp_name']),'png'=>@imagecreatefrompng($file['tmp_name']),'webp'=>function_exists('imagecreatefromwebp')?@imagecreatefromwebp($file['tmp_name']):false};
                if (!$image) {throw new InvalidArgumentException('The image could not be decoded.');}
                // JPEG phone cameras commonly store portrait rotation in EXIF rather than pixels.
                if ($extension==='jpg' && function_exists('exif_read_data')) {
                    $exif=@exif_read_data($file['tmp_name']);
                    $orientation=is_array($exif)?(int)($exif['Orientation']??1):1;
                    if (in_array($orientation,[2,4,5,7],true)) {imageflip($image, in_array($orientation,[2,5,7],true)?IMG_FLIP_HORIZONTAL:IMG_FLIP_VERTICAL);}
                    if (in_array($orientation,[3,4],true)) {$turned=imagerotate($image,180,0);if($turned instanceof \\GdImage){imagedestroy($image);$image=$turned;}}
                    elseif (in_array($orientation,[5,6],true)) {$turned=imagerotate($image,270,0);if($turned instanceof \\GdImage){imagedestroy($image);$image=$turned;}}
                    elseif (in_array($orientation,[7,8],true)) {$turned=imagerotate($image,90,0);if($turned instanceof \\GdImage){imagedestroy($image);$image=$turned;}}
                }
                imagesavealpha($image,true);
                $ok=match($extension){'jpg'=>imagejpeg($image,$target,90),'png'=>imagepng($image,$target,6),'webp'=>imagewebp($image,$target,88)};
                imagedestroy($image);if (!$ok) {throw new RuntimeException('The processed image could not be stored.');}
            } else {
                if ($kind==='pdf') {$h=fopen($file['tmp_name'],'rb');$magic=$h?fread($h,5):'';if ($h)fclose($h);if ($magic!=='%PDF-') {throw new InvalidArgumentException('Invalid PDF header.');}}
                if (!move_uploaded_file($file['tmp_name'],$target)) {throw new RuntimeException('PHP could not store this upload in private media storage.');}
            }
            if (!chmod($target,0600) || (fileperms($target)&0077)!==0) {throw new RuntimeException('The host cannot protect the uploaded file.');}
            $db=Database::connect();$q=$db->prepare('INSERT INTO '.Database::table('media').' (storage_name,original_name,kind,mime,byte_size,checksum,alt_text,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP())');
            $canonicalMime=match($extension){'ogg'=>'audio/ogg','wav'=>'audio/wav',default=>$mime};
            $q->execute([$name,$original,$kind,$canonicalMime,filesize($target),hash_file('sha256',$target),$alt,$actor]);$id=(int)$db->lastInsertId();return $id;
        } catch (Throwable $e) {if (is_file($target)) {@unlink($target);}throw $e;}
    }
    public static function serve(int $id): never
    {
        $media=self::get($id);$public=$media!==null && (($media['kind']==='image' && Branding::publicImage($id)) || Content::publicMedia($id) || (Database::exists(Database::connect(),Paths::config()['db']['prefix'].'products') && Catalog::publicImage($id)));
        if (!$public) {$user=Auth::current();if (!$media || !$user || !Permissions::allows($user,'media.manage')) {header('Cache-Control: private, no-store');http_response_code(404);exit('Media unavailable.');}}
        try {$width=ImageVariants::requestWidth($_GET['width']??null);} catch (InvalidArgumentException $e) {http_response_code(400);exit('Unsupported image size.');}
        if($width>0&&$media['kind']!=='image'){http_response_code(400);exit('Image size is not applicable.');}
        $resolved=ImageVariants::resolve($media,isset($_GET['download'])?0:$width);
        $file=$resolved['file'];$size=(int)filesize($file);$etag=$resolved['etag'];
        header('Content-Type: '.$resolved['mime']);header('X-Content-Type-Options: nosniff');header('Accept-Ranges: bytes');header('ETag: '.$etag);
        header('Cache-Control: '.($public?'public, max-age=0, must-revalidate':'private, no-store'));
        if ($media['kind']==='pdf' || isset($_GET['download'])) {header('Content-Disposition: attachment; filename="spiritual-gorillaz-'.$id.'.'.pathinfo($media['storage_name'],PATHINFO_EXTENSION).'"');}
        if ($public && ($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag && !isset($_SERVER['HTTP_RANGE'])) {http_response_code(304);exit;}
        $start=0;$end=$size-1;$range=(string)($_SERVER['HTTP_RANGE']??'');
        if($range!==''&&isset($_SERVER['HTTP_IF_RANGE'])&&$_SERVER['HTTP_IF_RANGE']!==$etag){$range='';}
        if ($range!=='') {
            if (!preg_match('/^bytes=(\d*)-(\d*)$/D',$range,$m) || ($m[1]==='' && $m[2]==='')) {header('Content-Range: bytes */'.$size);http_response_code(416);exit;}
            if ($m[1]==='') {$suffix=(int)$m[2];if ($suffix<1) {header('Content-Range: bytes */'.$size);http_response_code(416);exit;}$start=max(0,$size-$suffix);} else {$start=(int)$m[1];$end=$m[2]===''?$end:min($end,(int)$m[2]);}
            if ($start>$end || $start>=$size) {header('Content-Range: bytes */'.$size);http_response_code(416);exit;}
            http_response_code(206);header('Content-Range: bytes '.$start.'-'.$end.'/'.$size);
        }
        header('Content-Length: '.($end-$start+1));session_write_close();
        if (($_SERVER['REQUEST_METHOD']??'GET')==='HEAD') {exit;}
        $handle=fopen($file,'rb');if (!$handle) {http_response_code(503);exit;}fseek($handle,$start);$left=$end-$start+1;
        while ($left>0 && !feof($handle) && !connection_aborted()) {$chunk=fread($handle,min(65536,$left));if ($chunk===false || $chunk==='')break;echo $chunk;$left-=strlen($chunk);}
        fclose($handle);exit;
    }
}
