<?php
declare(strict_types=1);
// Disposable CLI fixture; excluded from deployment packages.
if(PHP_SAPI!=='cli'||getenv('SPIRIT_TESTING')!=='1'){http_response_code(404);exit(1);}
$root=(string)getenv('SPIRIT_FIXTURE_ROOT');$_SERVER['DOCUMENT_ROOT']=(string)getenv('SPIRIT_FIXTURE_DOCUMENT_ROOT');
if(!is_file($root.'/includes/bootstrap.php')){throw new RuntimeException('Missing test installation.');}
require $root.'/includes/bootstrap.php';
use Spirit\{Paths,Database,Content,ContentSchema,ContentHistory,Media,Security};
$c=Paths::config();if(!$c||!preg_match('/^spirit_ci(?:_[a-z0-9]+)?$/D',$c['db']['name'])||!preg_match('/^sgci_[a-f0-9]{8}_$/D',$c['db']['prefix'])||$c['db']['prefix']!==getenv('SPIRIT_DB_PREFIX')){throw new RuntimeException('Non-test database refused.');}
$in=json_decode(stream_get_contents(STDIN)?:'{}',true,64,JSON_THROW_ON_ERROR);$id=(int)($in['id']??0);$db=Database::connect();$out=[];
try{switch($argv[1]??''){
case 'snapshot':
 $q=$db->prepare('SELECT * FROM '.Database::table('content').' WHERE id=?');$q->execute([$id]);$out['row']=$q->fetch();$q=$db->prepare('SELECT revision,action,draft_json FROM '.Database::table('content_revisions').' WHERE content_id=? ORDER BY revision');$q->execute([$id]);$out['history']=$q->fetchAll();$q=$db->prepare('SELECT * FROM '.Database::table('content_media').' WHERE content_id=? ORDER BY visibility,media_id');$q->execute([$id]);$out['links']=$q->fetchAll();break;
case 'seed':
 $kind=$in['kind']??'page';$count=min(1200,(int)($in['count']??0));$db->beginTransaction();
 if($kind==='media'){$q=$db->prepare('INSERT INTO '.Database::table('media')." (storage_name,original_name,kind,mime,byte_size,checksum,alt_text,created_by,created_at) VALUES (?,?,'image','image/png',1,?,'Synthetic metadata only',1,UTC_TIMESTAMP())");for($i=0;$i<$count;$i++){$q->execute([bin2hex(random_bytes(16)).'.png','Late synthetic image '.$i,str_repeat('a',64)]);}}
 else{ContentSchema::fields($kind);$q=$db->prepare('INSERT INTO '.Database::table('content').' (kind,title,draft_slug,draft_json,revision,sort_order,updated_by,created_at,updated_at) VALUES (?,?,?,?,1,0,1,UTC_TIMESTAMP(),UTC_TIMESTAMP())');for($i=0;$i<$count;$i++){$title='Large library '.str_pad((string)$i,4,'0',STR_PAD_LEFT);$slug='large-'.$kind.'-'.$i;$doc=['title'=>$title,'slug'=>$slug,'data'=>ContentSchema::validate($kind,[],false),'sort_order'=>0];$q->execute([$kind,$title,$slug,json_encode($doc,JSON_THROW_ON_ERROR)]);}}
 $db->commit();$out=['count'=>$count];break;
case 'roles':
 foreach(['content_editor','store_manager'] as $role){$q=$db->prepare('INSERT INTO '.Database::table('users').' (name,email,password_hash,role,created_at) VALUES (?,?,?,?,UTC_TIMESTAMP())');$q->execute(['Synthetic '.$role,$role.'@example.test',Security::password('History-fixture-Password-42!'),$role]);$out[$role]=(int)$db->lastInsertId();}break;
case 'direct-denied':
 Content::save('page',['title'=>'Denied direct save','slug'=>'denied-direct'],(int)$in['actor'],'save');$out=['ok'=>true];break;
case 'media-break':$m=Media::get((int)$in['media_id']);$path=Media::path($m);file_put_contents($path,'Synthetic damaged bytes');break;
case 'legacy':
 $q=$db->prepare('SELECT draft_json FROM '.Database::table('content_revisions').' WHERE content_id=? AND revision=?');$q->execute([$id,(int)$in['revision']]);$doc=ContentHistory::document($q->fetchColumn());unset($doc['sort_order']);$q=$db->prepare('UPDATE '.Database::table('content_revisions').' SET draft_json=? WHERE content_id=? AND revision=?');$q->execute([json_encode($doc,JSON_THROW_ON_ERROR),$id,(int)$in['revision']]);break;
case 'duplicate-history':
 $q=$db->prepare('SELECT draft_json FROM '.Database::table('content').' WHERE id=?');$q->execute([$id]);$doc=$q->fetchColumn();$q=$db->prepare('INSERT INTO '.Database::table('content_revisions').' (content_id,revision,draft_json,action,actor_id,created_at) VALUES (?,?,?,\'save\',1,UTC_TIMESTAMP())');for($i=10;$i<44;$i++){$q->execute([$id,$i,$doc]);}break;
default:throw new RuntimeException('Unknown history fixture.');
}}catch(Throwable $e){if($db->inTransaction()){$db->rollBack();}$out=['ok'=>false,'error'=>$e->getMessage()];}
echo json_encode($out,JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
