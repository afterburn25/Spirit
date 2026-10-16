<?php
declare(strict_types=1);
// Optional hosting-panel scheduled task; not an HTTP endpoint.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$options=getopt('',['document-root:']);$root=$options['document-root']??getenv('SPIRIT_DOCUMENT_ROOT');
if(!is_string($root)||!is_dir($root)){fwrite(STDERR,"Supply --document-root=/absolute/public/document/root\n");exit(1);}
$_SERVER['DOCUMENT_ROOT']=$root;require dirname(__DIR__).'/includes/bootstrap.php';
try{if(!Spirit\Paths::installed()||!Spirit\Migrations::ready()){throw new RuntimeException('Installed application and schema required.');}$result=Spirit\MailQueue::process(20,90);$heartbeat=true;try{Spirit\TaskHeartbeat::record('mail');}catch(Throwable $heartbeatError){$heartbeat=false;}echo json_encode(['result'=>$result,'heartbeat'=>$heartbeat],JSON_THROW_ON_ERROR)."\n";}catch(Throwable $e){fwrite(STDERR,'Mail task failed ['.get_class($e)."]. Review owner delivery diagnostics.\n");exit(1);}
