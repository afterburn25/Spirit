<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$options=getopt('',['document-root:']);$root=$options['document-root']??getenv('SPIRIT_DOCUMENT_ROOT');
if(!is_string($root)||!is_dir($root)){fwrite(STDERR,"Supply --document-root=/absolute/public/document/root\n");exit(1);}
$_SERVER['DOCUMENT_ROOT']=$root;require dirname(__DIR__, 2).'/includes/bootstrap.php';
try{
    if(!Spirit\Paths::installed()||!Spirit\Migrations::ready()){throw new RuntimeException('Installed application and current schema required.');}
    if((Spirit\ConfigStore::read('maintenance')['scheduled_cleanup']??false)!==true){echo "Scheduled cleanup is disabled. No data changed.\n";exit;}
    echo json_encode(Spirit\TransientCleanup::run(null),JSON_THROW_ON_ERROR)."\n";
}catch(Throwable $e){fwrite(STDERR,'Cleanup deferred ['.get_class($e)."]. Review owner maintenance controls.\n");exit(1);}
finally{Spirit\MaintenanceGate::release();}
