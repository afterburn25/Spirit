<?php
declare(strict_types=1);
// Isolated CLI diagnostics; never packaged or reachable through the application.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing disposable application.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Media,ImageVariants,TransientCleanup};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Refusing non-test configuration.');}
$input=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);
$media=Media::get((int)($input['id']??0));$result=[];
switch($argv[1]??''){
case 'inspect':
    if(!$media){throw new RuntimeException('Fixture image missing.');}
    $source=Media::path($media);$result=['source'=>['name'=>$media['storage_name'],'bytes'=>filesize($source),'checksum'=>hash_file('sha256',$source)],'variants'=>[]];
    foreach(glob(Paths::runtime().'/cache/image-v1-'.$media['checksum'].'-*')?:[] as $path){
        if(is_link($path)){continue;}$info=@getimagesize($path);if(!$info){continue;}
        $raw=file_get_contents($path);$im=imagecreatefromstring($raw);$alpha=($im?imagecolorat($im,0,0):0)>>24;if($im){imagedestroy($im);}
        $result['variants'][]=['name'=>basename($path),'width'=>$info[0],'height'=>$info[1],'mime'=>$info['mime'],'bytes'=>filesize($path),'mode'=>fileperms($path)&0777,'alpha'=>$alpha,'hash'=>hash('sha256',$raw)];
    }break;
case 'low_memory':$result=ImageVariants::resolve($media,640);unset($result['file']);break;
case 'prune':$result=TransientCleanup::pruneTemporary(Paths::runtime(),time()-7*86400);break;
default:throw new RuntimeException('Unknown media-quality fixture operation.');
}
echo json_encode($result,JSON_THROW_ON_ERROR);
