<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$options=getopt('',['document-root:']);$root=$options['document-root']??getenv('SPIRIT_DOCUMENT_ROOT');
if(!is_string($root)||!is_dir($root)){fwrite(STDERR,"Supply --document-root=/absolute/public/document/root\n");exit(1);}
$_SERVER['DOCUMENT_ROOT']=$root;require dirname(__DIR__).'/includes/bootstrap.php';
try{if(!Spirit\Paths::installed()||!Spirit\Migrations::ready()){throw new RuntimeException('Installed application and schema required.');}$local=Spirit\OrderMaintenance::run();$notifications=Spirit\WebhookInbox::process(3);$heartbeat=true;try{Spirit\TaskHeartbeat::record('payments');}catch(Throwable $heartbeatError){$heartbeat=false;}echo json_encode(['local'=>$local,'notifications'=>$notifications,'heartbeat'=>$heartbeat],JSON_THROW_ON_ERROR)."\n";}catch(Throwable $e){fwrite(STDERR,'Payment maintenance failed ['.get_class($e)."]. Review owner order and notification screens. No payment retry is automatic.\n");exit(1);}
