<?php
declare(strict_types=1);
namespace Spirit;

use PDO;
use RuntimeException;
use Throwable;

/** One-time, owner-confirmed transition of the previously displayed factual lineup. */
final class MemberOnboarding
{
    public static function confirmed(): array
    {
        return [
            ['Chad Lindsey','Guitar, keyboards and vocals','chad-lindsey'],
            ['Ron Green','Harmonica','ron-green'],
            ['Charlie Savell','Drums','charlie-savell'],
        ];
    }

    /** Include deleted and draft records: a removed member must never be recreated. */
    public static function canImport(): bool
    {
        $q=Database::connect()->prepare('SELECT COUNT(*) FROM '.Database::table('content').' WHERE kind=?');
        $q->execute(['member']);
        return (int)$q->fetchColumn()===0;
    }

    /** Keep the old displayed lineup while only private draft records exist. */
    public static function showLegacyFallback(): bool
    {
        $db=Database::connect();
        $q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('content').
            ' WHERE kind=? AND (deleted_at IS NOT NULL OR published_json IS NOT NULL)');
        $q->execute(['member']);
        if((int)$q->fetchColumn()>0){return false;}
        $q=$db->prepare('SELECT COUNT(*) FROM '.Database::table('content_revisions').' r JOIN '.
            Database::table('content').' c ON c.id=r.content_id'.
            ' WHERE c.kind=? AND r.action IN (\'publish\',\'unpublish\')');
        $q->execute(['member']);
        return (int)$q->fetchColumn()===0;
    }

    public static function import(int $actor): int
    {
        $db=Database::connect();
        $db->beginTransaction();
        try {
            $user=$db->prepare('SELECT id,role,active,session_version FROM '.Database::table('users').' WHERE id=? FOR UPDATE');
            $user->execute([$actor]);
            $staff=$user->fetch();
            if(!Permissions::allows($staff?:null,'content.manage') ||
               (PHP_SAPI!=='cli' && ((int)($_SESSION['user_id']??0)!==$actor ||
               (int)($_SESSION['session_version']??0)!==(int)($staff['session_version']??-1)))){
                throw new RuntimeException('Your permission to manage members has changed. Sign in again.');
            }
            $check=$db->prepare('SELECT id FROM '.Database::table('content').' WHERE kind=? LIMIT 1 FOR UPDATE');
            $check->execute(['member']);
            if($check->fetchColumn()!==false){
                throw new RuntimeException('Band member records already exist. Edit those profiles instead of importing defaults.');
            }
            $insert=$db->prepare('INSERT INTO '.Database::table('content').
                ' (kind,title,draft_slug,draft_json,published_slug,published_json,revision,sort_order,published_at,created_at,updated_at,updated_by)'.
                ' VALUES (?,?,?,?,?,?,1,?,UTC_TIMESTAMP(),UTC_TIMESTAMP(),UTC_TIMESTAMP(),?)');
            $revision=$db->prepare('INSERT INTO '.Database::table('content_revisions').
                ' (content_id,revision,draft_json,action,actor_id,created_at) VALUES (?,1,?,\'publish\',?,UTC_TIMESTAMP())');
            $count=0;
            foreach(self::confirmed() as [$name,$role,$slug]){
                $data=ContentSchema::validate('member',['role'=>$role,'bio'=>'','image_id'=>''],true);
                $sort=10*($count+1);
                $document=json_encode(['title'=>$name,'slug'=>$slug,'data'=>$data,'sort_order'=>$sort],
                    JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                $insert->execute(['member',$name,$slug,$document,$slug,$document,$sort,$actor]);
                $revision->execute([(int)$db->lastInsertId(),$document,$actor]);
                $count++;
            }
            Database::audit($db,$actor,'content.member.import-confirmed');
            $db->commit();
            return $count;
        }catch(Throwable $error){
            if($db->inTransaction()){$db->rollBack();}
            throw $error;
        }
    }
}
