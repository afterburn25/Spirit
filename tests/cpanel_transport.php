<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){exit(1);}
require dirname(__DIR__).'/includes/bootstrap.php';
$input=json_decode(stream_get_contents(STDIN),true,16,JSON_THROW_ON_ERROR);
$endpoint=['ip'=>'127.0.0.1','port'=>(int)$input['port']];
if(!empty($input['ca'])){$endpoint['ca']=$input['ca'];}
$credentials=['host'=>($input['mismatch']??false)?'wrong.example.com':'cpanel.example.com','user'=>'cpuser','token'=>str_repeat('T',32)];
try{
    $client=new Spirit\CpanelClient($credentials,null,$endpoint);
    $data=$client->call('create_user',['name'=>'cpuser_fixture','password'=>'Synthetic+Password&975!']);
    echo json_encode(['ok'=>true,'data'=>$data]);
}catch(Throwable $e){echo json_encode(['ok'=>false,'class'=>get_class($e),'message'=>$e->getMessage()]);}
